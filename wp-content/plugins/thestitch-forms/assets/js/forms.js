/**
 * The Stitch Custom Forms - JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';

    var bridalPhoneInstance = null;
    var currentStep = 1;
    var totalSteps = $('.step-node').length || 5;
    var selectedFilesMap = {};
    var maxUploadBytes = 5 * 1024 * 1024;
    var nonceRefreshPromise = null;

    initBridalPhoneInput();
    initBridalForm();
    initRecreateForm();
    initPreviewMode();
    initSharedValidation();
    setDateMinimums();

    function initPreviewMode() {
        $('#ts-preview-overlay form').attr('novalidate', 'novalidate');
    }

    function initBridalPhoneInput() {
        var input = document.querySelector('#bridal_mobile_number');
        if (!input || typeof window.intlTelInput !== 'function') {
            return;
        }

        bridalPhoneInstance = window.intlTelInput(input, {
            initialCountry: 'auto',
            geoIpLookup: function(callback) {
                fetch('https://ipapi.co/json/')
                    .then(function(response) { return response.json(); })
                    .then(function(data) {
                        callback(data && data.country_code ? String(data.country_code).toLowerCase() : 'us');
                    })
                    .catch(function() {
                        callback('us');
                    });
            },
            preferredCountries: ['ae', 'tr', 'gb', 'us', 'al'],
            separateDialCode: true,
            nationalMode: true,
            strictMode: true,
            autoPlaceholder: 'polite',
            loadUtils: function() {
                return import(thestitch_ajax.phone_utils_url);
            }
        });

        input.addEventListener('countrychange', syncBridalPhoneData);
        input.addEventListener('input', syncBridalPhoneData);
        input.addEventListener('blur', function() {
            syncBridalPhoneData();
            validateBridalPhone($(input));
        });

        syncBridalPhoneData();
    }

    function initBridalForm() {
        $('#bridal-form').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);
            var $submitBtn = $form.find('.btn-submit');
            var $response = $('#bridal-form-response');
            var defaultButtonLabel = $submitBtn.data('default-label') || $submitBtn.text();

            if (isPreviewContext($form)) {
                $response.removeClass('error loading').addClass('success').html('Preview mode only — submission is disabled here.');
                return;
            }

            syncBridalPhoneData();

            if (!validateBridalForm($form)) {
                return;
            }

            $submitBtn.prop('disabled', true).text('Sending...');
            $response.removeClass('error success').addClass('loading').html('<span class="spinner"></span>Processing your request...');

            refreshFormsNonce().done(function(newNonce) {
                var formData = $form.serializeArray();
                formData.push({ name: 'action', value: 'submit_bridal_form' });
                formData.push({ name: 'nonce', value: newNonce || thestitch_ajax.nonce });

                submitBridalRequest(formData, $form, $submitBtn, $response, defaultButtonLabel, false);
            }).fail(function() {
                $response.removeClass('loading success').addClass('error').html('Unable to refresh your session. Please try again.');
                $submitBtn.prop('disabled', false).text(defaultButtonLabel);
            });
        });
    }

    function initRecreateForm() {
        initDropZones();
        initStepNavigation();
        initSizingToggle();

        $('#dream-outfit-form').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);
            var $submitBtn = $form.find('.btn-submit');
            var $response = $('#dream-form-response');
            var defaultLabel = $submitBtn.find('span:last').text();

            if (isPreviewContext($form)) {
                $response.removeClass('error loading').addClass('success').html('Preview mode only — submission is disabled here.');
                return;
            }

            if (!validateStep5($form.closest('.recreate-form-wrap'))) {
                return;
            }

            $submitBtn.prop('disabled', true).find('span:last').text('Sending...');
            $response.removeClass('error success').addClass('loading').html('<span class="spinner"></span>Uploading your request...');

            var formElement = this;
            refreshFormsNonce().done(function(newNonce) {
                var formData = new FormData(formElement);
                formData.append('action', 'submit_recreate_form');
                formData.append('nonce', newNonce || thestitch_ajax.nonce);

                submitRecreateRequest(formData, $form, $submitBtn, $response, defaultLabel, false);
            }).fail(function() {
                $response.removeClass('loading success').addClass('error').html('Unable to refresh your session. Please try again.');
                $submitBtn.prop('disabled', false).find('span:last').text(defaultLabel);
            });
        });
    }

    function initDropZones() {
        $('.drop-zone').each(function() {
            var $zone = $(this);
            var $input = $zone.find('input[type="file"]');
            var fieldName = ($input.attr('name') || '').replace('[]', '');

            ensureFieldBucket(fieldName);

            $zone.on('dragenter dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $zone.addClass('drag-over');
            });

            $zone.on('dragleave drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $zone.removeClass('drag-over');
            });

            $zone.on('drop', function(e) {
                var files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    var validated = collectValidatedFiles(files, $zone);
                    if (!validated.length) {
                        return;
                    }

                    selectedFilesMap[fieldName] = selectedFilesMap[fieldName].concat(validated);
                    syncInputFiles($input, fieldName);
                    renderFilePreviews($input, fieldName);
                }
            });
        });

        $('input[type="file"]').on('change', function() {
            var $input = $(this);
            var fieldName = $input.attr('name').replace('[]', '');
            var validated = collectValidatedFiles(this.files, $input.closest('.drop-zone'));
            if (!validated.length) {
                syncInputFiles($input, fieldName);
                return;
            }

            selectedFilesMap[fieldName] = selectedFilesMap[fieldName].concat(validated);
            syncInputFiles($input, fieldName);
            renderFilePreviews($input, fieldName);
        });
    }

    function initStepNavigation() {
        $('.btn-next').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var $container = $btn.closest('.recreate-form-wrap');

            if (!isPreviewContext($btn) && !validateCurrentStep(currentStep, $container)) {
                return;
            }
            goToStep(currentStep + 1, $container);
        });

        $('.btn-prev').on('click', function(e) {
            e.preventDefault();
            goToStep(currentStep - 1, $(this).closest('.recreate-form-wrap'));
        });
    }

    function initSizingToggle() {
        $('.custom-fit-section input, .custom-fit-section select').prop('disabled', true);

        $('input[name="sizing_type"]').on('change', function() {
            var val = $(this).val();
            if (val === 'custom') {
                $('#standard-sizing').hide();
                $('#custom-sizing').fadeIn(300);
                $('input[name="standard_size"]').prop('checked', false);
            } else {
                $('#custom-sizing').hide();
                $('#standard-sizing').fadeIn(300);
                $('input[name="custom_fit_type"]').prop('checked', false);
                $('.custom-fit-section').hide();
                $('.custom-fit-section input, .custom-fit-section select').prop('disabled', true);
            }

            ensureStepFiveSubmitVisibility($(this).closest('.recreate-form-wrap'));
        });

        $('input[name="custom_fit_type"]').on('change', function() {
            var fitType = $(this).val();
            $('.custom-fit-section').hide();
            $('.custom-fit-section input, .custom-fit-section select').prop('disabled', true);
            $('.custom-fit-section[data-fit-type="' + fitType + '"]').fadeIn(250);
            $('.custom-fit-section[data-fit-type="' + fitType + '"] input, .custom-fit-section[data-fit-type="' + fitType + '"] select').prop('disabled', false);

            ensureStepFiveSubmitVisibility($(this).closest('.recreate-form-wrap'));
        });

        $('input[name="measurement_unit"]').on('change', function() {
            var unitLabel = $(this).val() === 'cm' ? 'cm' : 'inches';
            $('.ts-unit-label').text(unitLabel);
        });

        $('.size-chart-toggle').on('click', function(e) {
            e.preventDefault();
            var target = $(this).data('target');
            var $panel = $(target);
            if (!$panel.length) {
                return;
            }
            $panel.slideToggle(220);
            $(this).toggleClass('active');
            $(this).text($(this).hasClass('active') ? 'Hide Size Chart' : 'View Size Chart');
        });
    }

    function initSharedValidation() {
        $('input[type="email"]').on('blur', function() {
            var $el = $(this);
            if ($el.val() && !isValidEmail($el.val())) {
                showFieldError($el, 'Please enter a valid email address');
            } else {
                clearFieldError($el);
            }
        });
    }

    function setDateMinimums() {
        var today = new Date().toISOString().split('T')[0];
        $('input[name="preferred_date"], input[name="wedding_date"]').attr('min', today);
    }

    function syncBridalPhoneData() {
        var $input = $('#bridal_mobile_number');
        if (!$input.length) {
            return;
        }

        var $form = $input.closest('form');
        var localNumber = ($input.val() || '').trim();
        var fullNumber = localNumber;
        var countryCode = '';
        var iso2 = '';

        if (bridalPhoneInstance) {
            var selectedCountry = bridalPhoneInstance.getSelectedCountryData() || {};
            countryCode = selectedCountry.dialCode ? '+' + selectedCountry.dialCode : '';
            iso2 = selectedCountry.iso2 ? selectedCountry.iso2.toUpperCase() : '';
            try {
                fullNumber = localNumber ? (bridalPhoneInstance.getNumber() || localNumber) : '';
            } catch (error) {
                fullNumber = localNumber;
            }
        }

        $form.find('input[name="country_code"]').val(countryCode);
        $form.find('input[name="phone_full"]').val(fullNumber);
        $form.find('input[name="phone_country_iso"]').val(iso2);
    }

    function validateBridalForm($form) {
        var isValid = true;
        var fields = [
            { sel: 'input[name="full_name"]', check: function(v) { return (v || '').trim().length >= 2; }, msg: 'Please enter a valid name' },
            { sel: 'input[name="email"]', check: function(v) { return isValidEmail(v); }, msg: 'Please enter a valid email' },
            { sel: 'input[name="preferred_date"]', check: function(v) { return !!v; }, msg: 'Please select a preferred date' },
            { sel: 'input[name="preferred_time"]', check: function(v) { return !!v; }, msg: 'Please select a preferred time' }
        ];

        fields.forEach(function(field) {
            var $field = $form.find(field.sel);
            if (!field.check($field.val())) {
                showFieldError($field, field.msg);
                isValid = false;
            } else {
                clearFieldError($field);
            }
        });

        if (!validateBridalPhone($form.find('input[name="mobile_number"]'))) {
            isValid = false;
        }

        return isValid;
    }

    function validateBridalPhone($input) {
        var $anchor = $input.closest('.phone-input-wrap');
        var rawValue = ($input.val() || '').trim();

        clearFieldError($anchor);

        if (!rawValue) {
            showFieldError($anchor, 'Please enter a valid phone number');
            return false;
        }

        if (bridalPhoneInstance && typeof bridalPhoneInstance.isValidNumber === 'function' && !bridalPhoneInstance.isValidNumber()) {
            showFieldError($anchor, 'Please enter a valid phone number');
            return false;
        }

        return true;
    }

    function ensureFieldBucket(fieldName) {
        if (!selectedFilesMap[fieldName]) {
            selectedFilesMap[fieldName] = [];
        }
    }

    function syncInputFiles($input, fieldName) {
        if (typeof DataTransfer === 'undefined') {
            return;
        }

        var dt = new DataTransfer();
        ensureFieldBucket(fieldName);
        selectedFilesMap[fieldName].forEach(function(file) {
            dt.items.add(file);
        });
        $input[0].files = dt.files;
    }

    function ensureStepFiveSubmitVisibility($context) {
        if (!window.matchMedia('(max-width: 768px)').matches) {
            return;
        }

        var $formContainer = ($context && $context.length) ? $context : $('.recreate-form-wrap').first();
        if (!$formContainer.length || currentStep !== 5) {
            return;
        }

        var $submit = $formContainer.find('#step-5 .btn-submit').first();
        if (!$submit.length || !$submit[0].scrollIntoView) {
            return;
        }

        window.setTimeout(function() {
            $submit[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 140);
    }

    function renderFilePreviews($input, fieldName) {
        var $preview = $('#preview-' + fieldName);
        $preview.empty();

        ensureFieldBucket(fieldName);

        selectedFilesMap[fieldName].forEach(function(file, index) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var $item = $('<div class="file-preview-item"></div>');
                var $img = $('<img>').attr('src', e.target.result);
                var $removeBtn = $('<button type="button" class="remove-btn" aria-label="Remove file">&times;</button>');

                $removeBtn.on('click', function(evt) {
                    evt.preventDefault();
                    selectedFilesMap[fieldName].splice(index, 1);
                    syncInputFiles($input, fieldName);
                    renderFilePreviews($input, fieldName);
                });

                $item.append($img).append($removeBtn);
                $preview.append($item);
            };
            reader.readAsDataURL(file);
        });
    }

    function collectValidatedFiles(fileList, $errorAnchor) {
        var validFiles = [];

        Array.from(fileList).forEach(function(file) {
            if (!file.type || !file.type.startsWith('image/')) {
                showFieldError($errorAnchor, 'Only image files are allowed.');
                return;
            }
            if (file.size > maxUploadBytes) {
                showFieldError($errorAnchor, 'Each image must be 5MB or smaller.');
                return;
            }
            validFiles.push(file);
        });

        if (validFiles.length) {
            clearFieldError($errorAnchor);
        }

        return validFiles;
    }

    function goToStep(step, $context) {
        if (step < 1 || step > totalSteps) {
            return;
        }

        var $formContainer = ($context && $context.length) ? $context : $('.recreate-form-wrap').first();
        var $steps = $formContainer.find('.form-step');

        $steps.hide();
        $formContainer.find('#step-' + step).fadeIn(250);
        currentStep = step;
        updateStepTrack(step, $formContainer);

        if ($formContainer.length && !isPreviewContext($formContainer)) {
            var offset = $formContainer.offset().top - 80;
            $('html, body').animate({ scrollTop: offset }, 350);
        }
    }

    function updateStepTrack(step, $context) {
        var $formContainer = ($context && $context.length) ? $context : $('.recreate-form-wrap').first();

        $formContainer.find('.step-node').each(function(index) {
            var nodeStep = index + 1;
            $(this).removeClass('active done');
            if (nodeStep < step) {
                $(this).addClass('done');
            } else if (nodeStep === step) {
                $(this).addClass('active');
            }
        });

        $formContainer.find('.step-line').each(function(index) {
            if (index < step - 1) {
                $(this).addClass('filled');
            } else {
                $(this).removeClass('filled');
            }
        });
    }

    function isPreviewContext($element) {
        return !!($element && $element.length && $element.closest('#ts-preview-overlay').length);
    }

    function validateStep1($context) {
        var $scope = ($context && $context.length) ? $context : $('.recreate-form-wrap').first();
        var $input = $scope.find('input[name="dream_images[]"]').first();
        if ($input.length && $input[0].files.length === 0) {
            showFieldError($input.closest('.drop-zone'), 'Please add at least one outfit image');
            shakeElement($input.closest('.drop-zone'));
            return false;
        }
        clearFieldError($input.closest('.drop-zone'));
        return true;
    }

    function validateStep2($context) {
        var $scope = ($context && $context.length) ? $context : $('.recreate-form-wrap').first();
        clearFieldError($scope.find('input[name="ref_images[]"]').first().closest('.drop-zone'));
        return true;
    }

    function validateStep3($context) {
        var $scope = ($context && $context.length) ? $context : $('.recreate-form-wrap').first();
        clearFieldError($scope.find('textarea[name="notes"]').first());
        return true;
    }

    function validateStep5($context) {
        var $scope = ($context && $context.length) ? $context : $('.recreate-form-wrap').first();
        var $sizing = $scope.find('input[name="sizing_type"]:checked');
        if ($sizing.length === 0) {
            showFieldError($scope.find('.sizing-toggle').first(), 'Please select a sizing type');
            return false;
        }
        clearFieldError($scope.find('.sizing-toggle').first());

        if ($sizing.val() === 'custom') {
            var $fitType = $scope.find('input[name="custom_fit_type"]:checked');
            if ($fitType.length === 0) {
                showFieldError($scope.find('.custom-fit-toggle').first(), 'Please choose quick fit or full fit');
                return false;
            }
            clearFieldError($scope.find('.custom-fit-toggle').first());

            if ($fitType.val() === 'full-fit') {
                var $fullSection = $scope.find('.custom-fit-section[data-fit-type="full-fit"]').first();
                var chestVal = parseFloat($fullSection.find('input[name="chest"]').val());
                var waistFullVal = parseFloat($fullSection.find('input[name="waist"]').val());
                var hipCircVal = parseFloat($fullSection.find('input[name="hip_circumference"]').val());
                if (isNaN(chestVal) || chestVal <= 0 || isNaN(waistFullVal) || waistFullVal <= 0 || isNaN(hipCircVal) || hipCircVal <= 0) {
                    showFieldError($fullSection.find('.measurement-grid'), 'Please fill Chest, Waist, and Hip Circumference');
                    return false;
                }
                clearFieldError($fullSection.find('.measurement-grid'));
            } else {
                var $quickSection = $scope.find('.custom-fit-section[data-fit-type="quick-fit"]').first();
                var bustVal = parseFloat($quickSection.find('input[name="bust"]').val());
                var waistVal = parseFloat($quickSection.find('input[name="waist"]').val());
                var hipsVal = parseFloat($quickSection.find('input[name="hips"]').val());
                if (isNaN(bustVal) || bustVal <= 0 || isNaN(waistVal) || waistVal <= 0 || isNaN(hipsVal) || hipsVal <= 0) {
                    showFieldError($quickSection.find('.measurement-grid'), 'Please fill Bust, Waist, and Hips');
                    return false;
                }
                clearFieldError($quickSection.find('.measurement-grid'));
            }
        } else {
            var size = $scope.find('input[name="standard_size"]:checked');
            if (size.length === 0) {
                showFieldError($scope.find('.size-chips').first(), 'Please pick a size');
                return false;
            }
            clearFieldError($scope.find('.size-chips').first());
        }

        var $email = $scope.find('#step-5 input[name="email"]').first();
        if (!isValidEmail($email.val())) {
            showFieldError($email, 'Please enter a valid email');
            return false;
        }
        clearFieldError($email);

        return true;
    }

    function validateCurrentStep(step, $context) {
        if (step === 1) return validateStep1($context);
        if (step === 2) return validateStep2($context);
        if (step === 3) return validateStep3($context);
        if (step === 4) return true;
        if (step === 5) return validateStep5($context);
        return true;
    }

    function shakeElement($el) {
        $el.css('animation', 'none');
        setTimeout(function() {
            $el.css('animation', 'shake 0.4s ease');
        }, 10);
    }

    if (!document.getElementById('shake-style')) {
        var style = document.createElement('style');
        style.id = 'shake-style';
        style.textContent = '@keyframes shake{0%,100%{transform:translateX(0)}20%{transform:translateX(-8px)}40%{transform:translateX(8px)}60%{transform:translateX(-4px)}80%{transform:translateX(4px)}}';
        document.head.appendChild(style);
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function submitBridalRequest(formData, $form, $submitBtn, $response, defaultButtonLabel, hasRetriedNonce) {
        $.ajax({
            url: thestitch_ajax.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: $.param(formData),
            success: function(response) {
                if (response.success) {
                    $response.removeClass('loading error').addClass('success').html(response.data);
                    $form[0].reset();
                    if (bridalPhoneInstance) {
                        bridalPhoneInstance.setNumber('');
                    }
                    syncBridalPhoneData();
                    $submitBtn.prop('disabled', false).text(defaultButtonLabel);
                    setTimeout(function() {
                        $response.fadeOut(function() {
                            $(this).removeAttr('style');
                        });
                    }, 5000);
                    return;
                }

                $response.removeClass('loading success').addClass('error').html((response && response.data) ? response.data : 'There was an issue submitting your request. Please try again.');
                $submitBtn.prop('disabled', false).text(defaultButtonLabel);
            },
            error: function(xhr, status) {
                if (!hasRetriedNonce && isNonceFailure(xhr)) {
                    refreshFormsNonce().done(function(newNonce) {
                        replaceFieldValue(formData, 'nonce', newNonce);
                        submitBridalRequest(formData, $form, $submitBtn, $response, defaultButtonLabel, true);
                    }).fail(function() {
                        $response.removeClass('loading success').addClass('error').html('Your session expired. Please try again.');
                        $submitBtn.prop('disabled', false).text(defaultButtonLabel);
                    });
                    return;
                }

                $response.removeClass('loading success').addClass('error').html(normalizeAjaxError(xhr, status));
                $submitBtn.prop('disabled', false).text(defaultButtonLabel);
            }
        });
    }

    function submitRecreateRequest(formData, $form, $submitBtn, $response, defaultLabel, hasRetriedNonce) {
        $.ajax({
            url: thestitch_ajax.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $response.removeClass('loading error').addClass('success').html(response.data);
                    $form[0].reset();
                    $submitBtn.prop('disabled', false).find('span:last').text(defaultLabel);
                    $('.file-preview').empty();
                    selectedFilesMap = {};
                    $('#standard-sizing, #custom-sizing').hide();
                    setTimeout(function() {
                        goToStep(1);
                    }, 1500);
                } else {
                    var errorMessage = (response && response.data) ? response.data : 'There was an issue submitting your request. Please try again.';
                    $response.removeClass('loading success').addClass('error').html(errorMessage);
                    $submitBtn.prop('disabled', false).find('span:last').text(defaultLabel);
                }
            },
            error: function(xhr, status) {
                if (!hasRetriedNonce && isNonceFailure(xhr)) {
                    refreshFormsNonce().done(function(newNonce) {
                        formData.set('nonce', newNonce);
                        submitRecreateRequest(formData, $form, $submitBtn, $response, defaultLabel, true);
                    }).fail(function() {
                        $response.removeClass('loading success').addClass('error').html('Your session expired. Please try again.');
                        $submitBtn.prop('disabled', false).find('span:last').text(defaultLabel);
                    });
                    return;
                }

                $response.removeClass('loading success').addClass('error').html(normalizeAjaxError(xhr, status));
                $submitBtn.prop('disabled', false).find('span:last').text(defaultLabel);
            }
        });
    }

    function refreshFormsNonce() {
        if (nonceRefreshPromise) {
            return nonceRefreshPromise;
        }

        nonceRefreshPromise = $.ajax({
            url: thestitch_ajax.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'thestitch_refresh_nonce'
            }
        }).then(function(response) {
            if (!response || !response.success || !response.data || !response.data.nonce) {
                return $.Deferred().reject(response).promise();
            }

            thestitch_ajax.nonce = response.data.nonce;
            return response.data.nonce;
        }).always(function() {
            nonceRefreshPromise = null;
        });

        return nonceRefreshPromise;
    }

    function isNonceFailure(xhr) {
        if (!xhr) {
            return false;
        }

        var responseText = typeof xhr.responseText === 'string' ? xhr.responseText.trim() : '';
        return xhr.status === 403 || responseText === '-1';
    }

    function normalizeAjaxError(xhr, status) {
        var errorMessage = 'Network error. Please try again.';

        if (xhr && xhr.responseJSON && xhr.responseJSON.data) {
            return xhr.responseJSON.data;
        }

        if (xhr && typeof xhr.responseText === 'string' && xhr.responseText.trim()) {
            if (xhr.responseText.trim() === '-1') {
                return 'Your session expired. Please try again.';
            }

            try {
                var parsed = JSON.parse(xhr.responseText);
                if (parsed && parsed.data) {
                    return parsed.data;
                }
            } catch (err) {
                if (status === 'timeout') {
                    return 'Upload timed out. Please retry on a stable connection.';
                }
            }
        }

        return errorMessage;
    }

    function replaceFieldValue(formData, fieldName, fieldValue) {
        var replaced = false;
        for (var i = 0; i < formData.length; i += 1) {
            if (formData[i].name === fieldName) {
                formData[i].value = fieldValue;
                replaced = true;
            }
        }

        if (!replaced) {
            formData.push({ name: fieldName, value: fieldValue });
        }
    }

    function showFieldError($field, message) {
        clearFieldError($field);
        $('<div class="form-error-message show"></div>').text(message).insertAfter($field);
    }

    function clearFieldError($field) {
        $field.nextAll('.form-error-message').remove();
    }

});

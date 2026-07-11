jQuery(function ($) {
    'use strict';

    /* ── Color Pickers ─────────────────────────────────────── */
    $('.ts-color-input').wpColorPicker();

    /* ── Tab Navigation ────────────────────────────────────── */
    $(document).on('click', '.ts-tab', function (e) {
        e.preventDefault();
        var target = $(this).data('target');
        $('.ts-tab').removeClass('active');
        $('.ts-pane').removeClass('active');
        $(this).addClass('active');
        $('#' + target).addClass('active');
    });

    /* ── Submission Bulk Selection ────────────────────────── */
    $(document).on('change', '.ts-select-all-submissions', function () {
        var checked = $(this).is(':checked');
        $('.ts-submission-checkbox').prop('checked', checked);
    });

    /* ── Quick Presets ─────────────────────────────────────── */
    $('#ts-preset-dark').on('click', function () {
        applyPreset({
            colors: {
                button_primary: '#8b7355', button_hover: '#6d5a47',
                input_border: '#3a3a3f', input_focus: '#c9a96e',
                success_color: '#4caf50', error_color: '#f44336',
                background: '#101014', text_color: '#f3efe8'
            },
            branding: { border_radius: '18', button_radius: '999', width: '680', padding: '30' }
        });
    });

    $('#ts-preset-light').on('click', function () {
        applyPreset({
            colors: {
                button_primary: '#8b7355', button_hover: '#6d5a47',
                input_border: '#e0e0e0', input_focus: '#8b7355',
                success_color: '#4caf50', error_color: '#f44336',
                background: '#ffffff', text_color: '#333333'
            },
            branding: { border_radius: '8', button_radius: '8', width: '620', padding: '30' }
        });
    });

    $('#ts-preset-reset').on('click', function () {
        applyPreset({
            colors: {
                button_primary: '#8b7355', button_hover: '#6d5a47',
                input_border: '#e0e0e0', input_focus: '#8b7355',
                success_color: '#4caf50', error_color: '#f44336',
                background: '#ffffff', text_color: '#333333'
            },
            branding: { border_radius: '8', button_radius: '8', width: '600', padding: '30' }
        });
    });

    function applyPreset(data) {
        if (data.colors) {
            Object.keys(data.colors).forEach(function (key) {
                var $f = $('[name="thestitch_forms_colors[' + key + ']"]');
                if ($f.hasClass('ts-color-input')) {
                    try { $f.wpColorPicker('color', data.colors[key]); }
                    catch (e) { $f.val(data.colors[key]); }
                } else {
                    $f.val(data.colors[key]);
                }
            });
        }
        if (data.branding) {
            Object.keys(data.branding).forEach(function (key) {
                $('[name="thestitch_forms_branding[' + key + ']"]').val(data.branding[key]);
            });
        }
    }

    /* ── Save Button Feedback ──────────────────────────────── */
    $(document).on('submit', '#ts-main-form', function () {
        $('#ts-save-btn, #ts-save-btn-top').each(function () {
            var $btn = $(this);
            if (!$btn.data('orig-label')) {
                $btn.data('orig-label', $btn.val());
            }
            $btn.val('Saving…');
        });
    });

    /* ── Custom Template Toggle ────────────────────────────── */
    function syncTemplateRow() {
        if ($('#ts-use-custom-template').val() === 'yes') {
            $('#ts-custom-template-row').slideDown(160);
        } else {
            $('#ts-custom-template-row').slideUp(160);
        }
    }
    $('#ts-use-custom-template').on('change', syncTemplateRow);
    syncTemplateRow();

    /* ── Email Template Presets ────────────────────────────── */
    $(document).on('click', '[data-template-preset]', function (e) {
        e.preventDefault();
        var key = $(this).data('template-preset');
        var presets = (window.thestitch_admin && thestitch_admin.email_template_presets)
            ? thestitch_admin.email_template_presets : null;
        if (!presets || !presets[key]) return;
        if (!confirm('Replace current template with the ' + key + ' preset?')) return;
        setEditorContent(presets[key]);
    });

    function setEditorContent(content) {
        var str = String(content || '');
        if (typeof tinymce !== 'undefined') {
            var ed = tinymce.get('thestitch_forms_email_customer_email_template_html');
            if (ed) { ed.setContent(str); }
        }
        $('[name="thestitch_forms_email[customer_email_template_html]"]').val(str);
    }

    function getEditorContent() {
        if (typeof tinymce !== 'undefined') {
            var ed = tinymce.get('thestitch_forms_email_customer_email_template_html');
            if (ed && !ed.isHidden()) return ed.getContent();
        }
        return $('[name="thestitch_forms_email[customer_email_template_html]"]').val() || '';
    }

    /* ── Drag & Drop Email Builder ─────────────────────────── */
    var blockTemplates = {
        hero:    '<div style="padding:26px 28px;background:linear-gradient(120deg,#8b7355,#c9a96e);color:#fff;"><div style="font-size:13px;letter-spacing:.08em;text-transform:uppercase;opacity:.92;">{{site_name}}</div><h1 style="margin:8px 0 0;font-size:28px;line-height:1.2;">{{heading}}</h1><p style="margin:10px 0 0;font-size:15px;line-height:1.5;opacity:.96;">{{subheading}}</p></div>',
        message: '<div style="padding:24px 28px;"><div style="font-size:15px;line-height:1.7;color:#3b3429;">{{message}}</div></div>',
        summary: '<div style="padding:0 28px 24px;"><div style="margin-top:8px;padding:16px 18px;background:#fcf8f1;border:1px solid #efe4d6;border-radius:12px;"><div style="font-size:13px;font-weight:700;color:#6a563d;letter-spacing:.04em;text-transform:uppercase;">Submission Summary</div><div style="margin-top:8px;font-size:14px;color:#2f2a22;"><div><strong>Type:</strong> {{submission_type}}</div><div><strong>Received:</strong> {{submitted_at}}</div></div>{{details_table}}</div></div>',
        cta:     '<div style="padding:0 28px 16px;">{{cta_button}}</div>',
        footer:  '<div style="padding:16px 28px;border-top:1px solid #f2e8da;background:#fffdf9;color:#8a7558;font-size:12px;line-height:1.7;">{{signature}}<br>\u00a9 {{year}} {{site_name}}</div>'
    };

    var blockLabels = {
        hero: 'Hero', message: 'Message', summary: 'Summary',
        cta: 'CTA Button', footer: 'Footer'
    };

    var $canvas = $('#ts-builder-canvas');

    if ($canvas.length && typeof $canvas.sortable === 'function') {
        $canvas.sortable({
            placeholder: 'ts-builder-placeholder',
            handle:      '.ts-drag-handle',
            tolerance:   'pointer',
            axis:        'y',
            cursor:      'grabbing'
        });
    }

    function addBlock(type) {
        if (!blockTemplates[type]) return;
        var $item = $('<li class="ts-block-item"></li>');
        $item.append(
            '<div class="ts-block-head">' +
            '<span class="ts-drag-handle" title="Drag to reorder">&#8942;</span>' +
            '<span class="ts-block-name">' + esc(blockLabels[type]) + '</span>' +
            '<button type="button" class="ts-block-remove">\u2715 Remove</button>' +
            '</div>' +
            '<textarea class="ts-block-code" rows="4"></textarea>'
        );
        $item.find('.ts-block-code').val(blockTemplates[type]);
        $canvas.append($item);
    }

    /* Seed default blocks on load */
    if ($canvas.length && !$canvas.children().length) {
        ['hero', 'message', 'summary', 'cta', 'footer'].forEach(addBlock);
    }

    $(document).on('click', '[data-add-block]', function (e) {
        e.preventDefault();
        addBlock($(this).data('add-block'));
    });

    $(document).on('click', '.ts-block-remove', function (e) {
        e.preventDefault();
        $(this).closest('.ts-block-item').remove();
    });

    $('#ts-builder-clear').on('click', function (e) {
        e.preventDefault();
        $canvas.empty();
    });

    $('#ts-builder-apply').on('click', function (e) {
        e.preventDefault();
        var blocks = [];
        $canvas.find('.ts-block-code').each(function () {
            var v = String($(this).val() || '').trim();
            if (v) blocks.push(v);
        });
        if (!blocks.length) return;

        var html =
            '<div style="margin:0;padding:24px;background:#f7f2ea;font-family:Inter,-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#2f2a22;">\n' +
            '<div style="max-width:640px;margin:0 auto;background:#fff;border:1px solid #eadfce;border-radius:18px;overflow:hidden;box-shadow:0 14px 36px rgba(32,22,8,.08);">\n' +
            blocks.join('\n') +
            '\n</div>\n</div>';

        setEditorContent(html);
        $('#ts-use-custom-template').val('yes').trigger('change');
    });

    /* ── Media Picker (Size Chart) ─────────────────────────── */
    var mediaFrame = null;
    $('#ts-pick-size-chart').on('click', function (e) {
        e.preventDefault();
        if (typeof wp === 'undefined' || !wp.media) return;
        if (mediaFrame) { mediaFrame.open(); return; }
        mediaFrame = wp.media({
            title: 'Select Size Chart Image',
            button: { text: 'Use this image' },
            library: { type: 'image' },
            multiple: false
        });
        mediaFrame.on('select', function () {
            var att = mediaFrame.state().get('selection').first().toJSON();
            var url = (att && att.url) ? att.url : '';
            $('#ts-size-chart-url').val(url);
            renderSizeChartPreview(url);
        });
        mediaFrame.open();
    });

    $('#ts-clear-size-chart').on('click', function (e) {
        e.preventDefault();
        $('#ts-size-chart-url').val('');
        renderSizeChartPreview('');
    });

    $('#ts-size-chart-url').on('change keyup', function () {
        renderSizeChartPreview($(this).val());
    });

    function renderSizeChartPreview(url) {
        var $p = $('#ts-size-chart-preview');
        if (!url) { $p.hide().html(''); return; }
        $p.html('<img src="' + esc(url) + '" alt="Size Chart Preview">').show();
    }

    /* Restore preview on load */
    (function () {
        var $u = $('#ts-size-chart-url');
        if ($u.length && $u.val()) renderSizeChartPreview($u.val());
    }());

    /* ── Send Test Email ───────────────────────────────────── */
    $('#ts-send-test-email').on('click', function (e) {
        e.preventDefault();
        if (!window.thestitch_admin) return;
        var $btn = $(this);
        var $status = $('#ts-test-email-status');
        var payload = {
            action: 'thestitch_send_test_customer_email',
            nonce: thestitch_admin.nonce,
            to_email: ($('#ts-test-email-recipient').val() || '').trim(),
            subject: 'The Stitch \u00b7 Test Customer Confirmation Email',
            customer_message:            $('[name="thestitch_forms_email[customer_message]"]').val() || '',
            customer_email_heading:      $('[name="thestitch_forms_email[customer_email_heading]"]').val() || '',
            customer_email_subheading:   $('[name="thestitch_forms_email[customer_email_subheading]"]').val() || '',
            customer_email_signature:    $('[name="thestitch_forms_email[customer_email_signature]"]').val() || '',
            customer_email_theme:        $('[name="thestitch_forms_email[customer_email_theme]"]').val() || 'luxury',
            customer_email_button_text:  $('[name="thestitch_forms_email[customer_email_button_text]"]').val() || '',
            customer_email_button_url:   $('[name="thestitch_forms_email[customer_email_button_url]"]').val() || '',
            customer_email_use_custom_template: $('[name="thestitch_forms_email[customer_email_use_custom_template]"]').val() || 'no',
            customer_email_template_html: getEditorContent()
        };

        $btn.prop('disabled', true).text('Sending\u2026');
        $status.text('Sending\u2026').css('color', '#6b7280');

        $.post(thestitch_admin.ajax_url, payload)
            .done(function (r) {
                if (r && r.success) {
                    $status.text(r.data || 'Sent!').css('color', '#15803d');
                } else {
                    $status.text((r && r.data) ? r.data : 'Failed.').css('color', '#b91c1c');
                }
            })
            .fail(function () {
                $status.text('Network error. Please try again.').css('color', '#b91c1c');
            })
            .always(function () {
                $btn.prop('disabled', false).text('Send Test Email');
            });
    });
    /* ── Fullscreen Preview ─────────────────────────────── */
    $('#ts-open-preview').on('click', function () {
        $('#ts-preview-overlay, #ts-preview-backdrop').addClass('ts-preview-open');
        $('body').css('overflow', 'hidden');
    });

    function closePreview() {
        $('#ts-preview-overlay, #ts-preview-backdrop').removeClass('ts-preview-open');
        $('body').css('overflow', '');
    }

    $('#ts-close-preview').on('click', closePreview);
    $('#ts-preview-backdrop').on('click', closePreview);

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') closePreview();
    });

    $(document).on('click', '.ts-preview-tab', function () {
        var form = $(this).data('form');
        $('.ts-preview-tab').removeClass('active');
        $(this).addClass('active');
        $('.ts-preview-pane').hide();
        $('#ts-preview-' + form).show();
        if (form === 'email') loadEmailPreview();
    });

    function loadEmailPreview() {
        var iframe = document.getElementById('ts-email-preview-iframe');
        if (!iframe) return;
        // Build email HTML from current field values
        var heading    = $('input[name="thestitch_forms_email[customer_email_heading]"]').val()    || 'Thank you ✨';
        var subheading = $('input[name="thestitch_forms_email[customer_email_subheading]"]').val() || 'We received your request and our team is already reviewing it.';
        var message    = $('textarea[name="thestitch_forms_email[customer_message]"]').val()       || '';
        var btnText    = $('input[name="thestitch_forms_email[customer_email_button_text]"]').val()|| '';
        var btnUrl     = $('input[name="thestitch_forms_email[customer_email_button_url]"]').val() || '#';
        var signature  = $('textarea[name="thestitch_forms_email[customer_email_signature]"]').val()|| '';
        var theme      = $('select[name="thestitch_forms_email[customer_email_theme]"]').val()     || 'luxury';
        var siteName   = typeof thestitch_admin !== 'undefined' && thestitch_admin.site_name ? thestitch_admin.site_name : document.title.split('–')[1] ? document.title.split('–')[1].trim() : 'The Stitch';

        // Theme colours
        var themes = {
            luxury:  { grad: 'linear-gradient(120deg,#8b7355,#c9a96e)', bg: '#f7f2ea', card: '#fff', accent: '#8b7355' },
            classic: { grad: 'linear-gradient(120deg,#5a6776,#8a9dad)', bg: '#f2f4f6', card: '#fff', accent: '#5a6776' },
            minimal: { grad: '#ffffff', bg: '#ffffff', card: '#f8f8f8', accent: '#333' }
        };
        var t = themes[theme] || themes.luxury;

        var btnHtml = btnText ? '<div style="text-align:center;margin:24px 0;"><a href="' + esc(btnUrl) + '" style="display:inline-block;background:' + t.accent + ';color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:700;font-size:14px;">' + esc(btnText) + '</a></div>' : '';
        var msgHtml = message ? '<p style="color:#555;line-height:1.7;font-size:14px;">' + esc(message).replace(/\n/g, '<br>') + '</p>' : '';
        var sigHtml = signature ? '<p style="color:#888;font-size:12px;border-top:1px solid #eee;margin-top:24px;padding-top:16px;">' + esc(signature).replace(/\n/g, '<br>') + '</p>' : '';

        var html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>*{box-sizing:border-box;margin:0;padding:0;}body{background:' + t.bg + ';font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,sans-serif;}</style></head>'
            + '<body><div style="max-width:600px;margin:0 auto;padding:24px;">'
            + '<div style="background:' + t.card + ';border-radius:16px;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,0.08);">'
            + '<div style="background:' + t.grad + ';padding:36px 32px;text-align:center;">'
            + '<div style="font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,0.75);margin-bottom:12px;">' + esc(siteName) + '</div>'
            + '<h1 style="font-size:26px;font-weight:700;color:#fff;margin:0;line-height:1.3;">' + esc(heading) + '</h1>'
            + '</div>'
            + '<div style="padding:32px;">'
            + '<p style="font-size:15px;color:#444;line-height:1.7;margin-bottom:18px;">' + esc(subheading) + '</p>'
            + msgHtml
            + btnHtml
            + sigHtml
            + '<div style="text-align:center;font-size:11px;color:#bbb;margin-top:28px;">' + esc(siteName) + ' &mdash; ' + new Date().getFullYear() + '</div>'
            + '</div></div></div></body></html>';

        // Write into iframe
        iframe.srcdoc = html;
        // auto-expand height
        $(iframe).off('load.ep').on('load.ep', function () {
            try {
                var h = iframe.contentDocument.body.scrollHeight;
                iframe.style.minHeight = (h + 40) + 'px';
            } catch(e){}
        });
    }
    /* ── Helpers ───────────────────────────────────────────── */
    function esc(v) {
        return String(v || '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    /* ── TinyMCE Sync Before Submit ────────────────────────── */
    // Ensure the wp_editor textarea is populated before the form posts.
    var form = document.getElementById('ts-main-form');
    if (form) {
        form.addEventListener('submit', function () {
            if (typeof tinymce !== 'undefined') {
                tinymce.triggerSave();
            }
        });
    }
});

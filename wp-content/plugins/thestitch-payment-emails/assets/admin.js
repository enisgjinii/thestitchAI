(function ($) {
  function getPanel($button) {
    return $button.closest('.ts-payment-panel');
  }

  function collectPayload($panel) {
    return {
      action: '',
      nonce: thestitchPaymentEmail.nonce,
      source_type: $panel.data('source-type'),
      source_id: $panel.data('source-id'),
      final_price: $('#ts_payment_final_price', $panel).val(),
      currency: $('#ts_payment_currency', $panel).val(),
      payment_url: $('#ts_payment_url', $panel).val(),
      admin_message: $('#ts_payment_message', $panel).val(),
      test_mode: $('#ts_payment_test_mode', $panel).is(':checked') ? 1 : 0,
    };
  }

  function showFeedback($panel, message, type) {
    var $feedback = $('.ts-payment-feedback', $panel);
    $feedback.removeClass('is-error is-success').addClass(type === 'error' ? 'is-error' : 'is-success').text(message);
  }

  function updateStatus($panel, data) {
    var html = '<p><strong>Sent:</strong> ' + data.sent_at + '</p>' +
      '<p><strong>Sent by:</strong> ' + data.sent_by + '</p>' +
      '<p><strong>Send count:</strong> ' + data.send_count + '</p>';
    $('.ts-payment-status', $panel).html(html);
    $('.ts-payment-send', $panel).text('Resend Payment Email');
  }

  $(document).on('click', '.ts-payment-preview', function () {
    var $panel = getPanel($(this));
    var payload = collectPayload($panel);
    payload.action = 'thestitch_payment_email_preview';

    showFeedback($panel, 'Generating preview…', 'success');

    $.post(thestitchPaymentEmail.ajaxUrl, payload)
      .done(function (response) {
        if (!response.success) {
          showFeedback($panel, response.data && response.data.message ? response.data.message : 'Preview failed.', 'error');
          return;
        }

        var $wrap = $('.ts-payment-preview-wrap', $panel);
        var $frame = $('.ts-payment-preview-frame', $panel);
        $wrap.show();
        $frame.attr('srcdoc', response.data.html);
        showFeedback($panel, 'Preview ready below.', 'success');
      })
      .fail(function () {
        showFeedback($panel, 'Preview request failed.', 'error');
      });
  });

  $(document).on('click', '.ts-payment-send', function () {
    var $panel = getPanel($(this));
    var payload = collectPayload($panel);
    payload.action = 'thestitch_payment_email_send';

    if (payload.test_mode && !payload.payment_url) {
      if (!window.confirm('Test mode is on. The email will use a placeholder payment link that does not accept real payments. Continue?')) {
        return;
      }
    }

    var sendCountText = $('.ts-payment-status strong:contains("Send count")', $panel).length;
    if (sendCountText && !payload.confirm_resend) {
      var existingCount = parseInt($('.ts-payment-status', $panel).text().match(/Send count:\s*(\d+)/)?.[1] || '0', 10);
      if (existingCount > 0 && !window.confirm('This payment email was already sent. Resend it to the customer?')) {
        return;
      }
      payload.confirm_resend = 1;
    }

    showFeedback($panel, 'Sending…', 'success');

    $.post(thestitchPaymentEmail.ajaxUrl, payload)
      .done(function (response) {
        if (!response.success) {
          if (response.data && response.data.requires_confirmation) {
            if (window.confirm('This payment email was already sent. Resend it to the customer?')) {
              payload.confirm_resend = 1;
              $.post(thestitchPaymentEmail.ajaxUrl, payload).done(function (retryResponse) {
                if (!retryResponse.success) {
                  showFeedback($panel, retryResponse.data && retryResponse.data.message ? retryResponse.data.message : 'Send failed.', 'error');
                  return;
                }
                updateStatus($panel, retryResponse.data);
                showFeedback($panel, retryResponse.data.message, 'success');
              });
            }
            return;
          }

          showFeedback($panel, response.data && response.data.message ? response.data.message : 'Send failed.', 'error');
          return;
        }

        updateStatus($panel, response.data);
        var successMessage = response.data.message;
        if (response.data.test_mode) {
          successMessage += ' (Test mode — placeholder payment link used.)';
        }
        showFeedback($panel, successMessage, 'success');
      })
      .fail(function () {
        showFeedback($panel, 'Send request failed.', 'error');
      });
  });
})(jQuery);

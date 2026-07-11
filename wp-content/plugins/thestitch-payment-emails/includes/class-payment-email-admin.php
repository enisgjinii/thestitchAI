<?php

if (!defined('ABSPATH')) {
    exit;
}

class TheStitch_Payment_Email_Admin {
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'register_meta_boxes']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_thestitch_payment_email_preview', [$this, 'ajax_preview']);
        add_action('wp_ajax_thestitch_payment_email_send', [$this, 'ajax_send']);
    }

    public function register_meta_boxes() {
        add_meta_box(
            'thestitch_payment_request',
            'Payment Request',
            [$this, 'render_panel'],
            'form_submission',
            'side',
            'high'
        );

        if (function_exists('wc_get_page_screen_id')) {
            add_meta_box(
                'thestitch_payment_request_wc',
                'Payment Request',
                [$this, 'render_panel'],
                wc_get_page_screen_id('shop-order'),
                'side',
                'high'
            );
        }
    }

    public function enqueue_assets($hook) {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen) {
            return;
        }

        $allowed = ['form_submission'];
        if (function_exists('wc_get_page_screen_id')) {
            $allowed[] = wc_get_page_screen_id('shop-order');
        }
        if (!in_array($screen->id, $allowed, true)) {
            return;
        }

        wp_enqueue_style(
            'thestitch-payment-emails-admin',
            THESTITCH_PAYMENT_EMAILS_URL . 'assets/admin.css',
            [],
            THESTITCH_PAYMENT_EMAILS_VERSION
        );
        wp_enqueue_script(
            'thestitch-payment-emails-admin',
            THESTITCH_PAYMENT_EMAILS_URL . 'assets/admin.js',
            ['jquery'],
            THESTITCH_PAYMENT_EMAILS_VERSION,
            true
        );

        wp_localize_script('thestitch-payment-emails-admin', 'thestitchPaymentEmail', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('thestitch_payment_email'),
        ]);
    }

    private function detect_context($post) {
        if ($post instanceof WC_Order) {
            return [
                'source_type' => 'woocommerce_order',
                'source_id' => $post->get_id(),
            ];
        }

        if ($post instanceof WP_Post && $post->post_type === 'form_submission') {
            return [
                'source_type' => 'recreate_submission',
                'source_id' => $post->ID,
            ];
        }

        if (is_numeric($post)) {
            $wp_post = get_post(absint($post));
            if ($wp_post instanceof WP_Post && $wp_post->post_type === 'form_submission') {
                return [
                    'source_type' => 'recreate_submission',
                    'source_id' => $wp_post->ID,
                ];
            }
        }

        return null;
    }

    public function render_panel($post) {
        $context = $this->detect_context($post);
        if (!$context) {
            echo '<p>Unsupported record type.</p>';
            return;
        }

        $adapter = TheStitch_Payment_Emails::get_adapter($context['source_type'], $context['source_id']);
        if (!$adapter) {
            echo '<p>Unable to load payment source.</p>';
            return;
        }

        $final_price = $adapter->get_payment_meta('_thestitch_final_price');
        $currency = $adapter->get_payment_meta('_thestitch_currency', 'AED');
        $payment_url = $adapter->get_payment_meta('_thestitch_nomod_payment_url');
        $admin_message = $adapter->get_payment_meta('_thestitch_payment_email_message');
        $sent_at = $adapter->get_payment_meta('_thestitch_payment_email_sent_at');
        $sent_by = $adapter->get_payment_meta('_thestitch_payment_email_sent_by');
        $send_count = (int) $adapter->get_payment_meta('_thestitch_payment_email_send_count', 0);

        wp_nonce_field('thestitch_payment_email_panel', 'thestitch_payment_email_panel_nonce');
        ?>
        <div class="ts-payment-panel" data-source-type="<?php echo esc_attr($context['source_type']); ?>" data-source-id="<?php echo esc_attr((string) $context['source_id']); ?>">
            <p><strong>Customer email:</strong><br><?php echo esc_html($adapter->get_customer_email() ?: 'Not available'); ?></p>

            <p>
                <label for="ts_payment_final_price"><strong>Final price</strong></label>
                <input type="number" step="0.01" min="0.01" id="ts_payment_final_price" class="widefat" value="<?php echo esc_attr($final_price); ?>">
            </p>

            <p>
                <label for="ts_payment_currency"><strong>Currency</strong></label>
                <select id="ts_payment_currency" class="widefat">
                    <?php foreach (TheStitch_Payment_Emails::allowed_currencies() as $allowed_currency) : ?>
                        <option value="<?php echo esc_attr($allowed_currency); ?>" <?php selected($currency, $allowed_currency); ?>><?php echo esc_html($allowed_currency); ?></option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p>
                <label for="ts_payment_url"><strong>NOMOD payment URL</strong></label>
                <input type="url" id="ts_payment_url" class="widefat" value="<?php echo esc_attr($payment_url); ?>" placeholder="https://...">
            </p>

            <p>
                <label for="ts_payment_message"><strong>Optional message</strong></label>
                <textarea id="ts_payment_message" class="widefat" rows="4"><?php echo esc_textarea($admin_message); ?></textarea>
            </p>

            <div class="ts-payment-actions">
                <button type="button" class="button button-secondary ts-payment-preview">Preview Email</button>
                <button type="button" class="button button-primary ts-payment-send"><?php echo $send_count > 0 ? 'Resend Payment Email' : 'Send Payment Email'; ?></button>
            </div>

            <div class="ts-payment-status">
                <?php if ($send_count > 0) : ?>
                    <p><strong>Sent:</strong> <?php echo esc_html($sent_at ?: 'Unknown'); ?></p>
                    <p><strong>Sent by:</strong> <?php echo esc_html($sent_by ?: 'Unknown'); ?></p>
                    <p><strong>Send count:</strong> <?php echo esc_html((string) $send_count); ?></p>
                <?php else : ?>
                    <p class="description">No payment email sent yet.</p>
                <?php endif; ?>
            </div>

            <div class="ts-payment-feedback" aria-live="polite"></div>
            <div class="ts-payment-preview-wrap" style="display:none;">
                <iframe class="ts-payment-preview-frame" title="Payment email preview" style="width:100%;min-height:480px;border:1px solid #ddd;border-radius:8px;background:#fff;"></iframe>
            </div>
        </div>
        <?php
    }

    private function current_user_label() {
        $user = wp_get_current_user();
        if (!$user || !$user->exists()) {
            return 'Administrator';
        }

        return $user->display_name ?: $user->user_login;
    }

    private function validate_request($source_type, $source_id, $payload) {
        if (!current_user_can('manage_woocommerce') && !current_user_can('manage_options')) {
            return new WP_Error('forbidden', 'You do not have permission to send payment emails.');
        }

        $adapter = TheStitch_Payment_Emails::get_adapter($source_type, $source_id);
        if (!$adapter) {
            return new WP_Error('invalid_source', 'The selected record could not be found.');
        }

        $final_price = isset($payload['final_price']) ? (float) $payload['final_price'] : 0;
        if ($final_price <= 0) {
            return new WP_Error('invalid_price', 'Final price must be greater than zero.');
        }

        $currency = isset($payload['currency']) ? strtoupper(sanitize_text_field($payload['currency'])) : 'AED';
        if (!in_array($currency, TheStitch_Payment_Emails::allowed_currencies(), true)) {
            return new WP_Error('invalid_currency', 'Selected currency is not allowed.');
        }

        $payment_url = TheStitch_Payment_Emails::validate_payment_url($payload['payment_url'] ?? '');
        if (is_wp_error($payment_url)) {
            return $payment_url;
        }

        $admin_message = isset($payload['admin_message']) ? sanitize_textarea_field($payload['admin_message']) : '';

        return [
            'adapter' => $adapter,
            'final_price' => $final_price,
            'currency' => $currency,
            'payment_url' => $payment_url,
            'admin_message' => $admin_message,
        ];
    }

    public function ajax_preview() {
        check_ajax_referer('thestitch_payment_email', 'nonce');

        $source_type = sanitize_text_field(wp_unslash($_POST['source_type'] ?? ''));
        $source_id = absint($_POST['source_id'] ?? 0);
        $validated = $this->validate_request($source_type, $source_id, wp_unslash($_POST));
        if (is_wp_error($validated)) {
            wp_send_json_error(['message' => $validated->get_error_message()]);
        }

        $html = TheStitch_Payment_Email_Renderer::render($validated['adapter'], [
            'final_price' => $validated['final_price'],
            'currency' => $validated['currency'],
            'payment_url' => $validated['payment_url'],
            'admin_message' => $validated['admin_message'],
        ]);

        wp_send_json_success(['html' => $html]);
    }

    public function ajax_send() {
        check_ajax_referer('thestitch_payment_email', 'nonce');

        $source_type = sanitize_text_field(wp_unslash($_POST['source_type'] ?? ''));
        $source_id = absint($_POST['source_id'] ?? 0);
        $confirm_resend = !empty($_POST['confirm_resend']);

        $validated = $this->validate_request($source_type, $source_id, wp_unslash($_POST));
        if (is_wp_error($validated)) {
            wp_send_json_error(['message' => $validated->get_error_message()]);
        }

        $adapter = $validated['adapter'];
        $existing_count = (int) $adapter->get_payment_meta('_thestitch_payment_email_send_count', 0);
        if ($existing_count > 0 && !$confirm_resend) {
            wp_send_json_error([
                'message' => 'This payment email was already sent. Confirm resend to continue.',
                'requires_confirmation' => true,
            ]);
        }

        $adapter->save_payment_fields([
            '_thestitch_final_price' => (string) $validated['final_price'],
            '_thestitch_currency' => $validated['currency'],
            '_thestitch_nomod_payment_url' => $validated['payment_url'],
            '_thestitch_payment_email_message' => $validated['admin_message'],
        ]);

        $result = TheStitch_Payment_Email_Renderer::send($adapter, [
            'final_price' => $validated['final_price'],
            'currency' => $validated['currency'],
            'payment_url' => $validated['payment_url'],
            'admin_message' => $validated['admin_message'],
        ]);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        $new_count = $existing_count + 1;
        $sent_at = current_time('mysql');
        $sent_by = $this->current_user_label();

        $adapter->save_payment_fields([
            '_thestitch_payment_email_sent_at' => $sent_at,
            '_thestitch_payment_email_sent_by' => $sent_by,
            '_thestitch_payment_email_send_count' => (string) $new_count,
        ]);

        wp_send_json_success([
            'message' => $existing_count > 0 ? 'Payment email resent successfully.' : 'Payment email sent successfully.',
            'sent_at' => $sent_at,
            'sent_by' => $sent_by,
            'send_count' => $new_count,
        ]);
    }
}

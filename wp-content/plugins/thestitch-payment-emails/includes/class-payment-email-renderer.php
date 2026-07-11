<?php

if (!defined('ABSPATH')) {
    exit;
}

class TheStitch_Payment_Email_Renderer {
    public static function render($adapter, $args) {
        $customer_name = esc_html($adapter->get_customer_name());
        $reference = esc_html($adapter->get_reference());
        $order_type = esc_html($adapter->get_order_type_label());
        $referral_code = trim((string) $adapter->get_referral_code());
        $final_price = number_format((float) $args['final_price'], 2, '.', ',');
        $currency = esc_html($args['currency']);
        $payment_url = esc_url($args['payment_url']);
        $admin_message = isset($args['admin_message']) ? wp_kses_post(wpautop($args['admin_message'])) : '';
        $preview_3d_url = esc_url($adapter->get_preview_3d_url());
        $fabric_url = esc_url($adapter->get_fabric_pattern_url());
        $design_preview_url = esc_url($adapter->get_design_preview_url());

        $garment_config = $adapter->get_garment_configuration();
        $sizing_details = $adapter->get_sizing_details();
        $upload_groups = $adapter->get_uploaded_images();

        $site_name = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
        $logo_url = esc_url(get_theme_mod('custom_logo') ? wp_get_attachment_image_url(get_theme_mod('custom_logo'), 'medium') : '');
        $support_email = sanitize_email(get_option('admin_email'));

        ob_start();
        include THESTITCH_PAYMENT_EMAILS_PATH . 'templates/payment-request-email.php';
        return (string) ob_get_clean();
    }

    public static function build_subject($adapter, $currency, $final_price) {
        return sprintf(
            'Your finalized quote from %s — %s %s',
            wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES),
            $currency,
            number_format((float) $final_price, 2, '.', ',')
        );
    }

    public static function send($adapter, $args) {
        $recipient = sanitize_email($adapter->get_customer_email());
        if (!$recipient || !is_email($recipient)) {
            return new WP_Error('invalid_email', 'A valid customer email is required.');
        }

        $html = self::render($adapter, $args);
        $subject = self::build_subject($adapter, $args['currency'], $args['final_price']);
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        $sent = wp_mail($recipient, $subject, $html, $headers);
        if (!$sent) {
            return new WP_Error('send_failed', 'The payment email could not be sent. Please check SMTP settings.');
        }

        return true;
    }
}

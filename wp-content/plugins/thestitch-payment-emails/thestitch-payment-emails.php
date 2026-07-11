<?php
/**
 * Plugin Name: The Stitch Payment Emails
 * Description: Admin-controlled finalized payment request emails for Create orders and Recreate submissions.
 * Version: 1.0.0
 * Author: The Stitch
 */

if (!defined('ABSPATH')) {
    exit;
}

define('THESTITCH_PAYMENT_EMAILS_VERSION', '1.0.0');
define('THESTITCH_PAYMENT_EMAILS_PATH', plugin_dir_path(__FILE__));
define('THESTITCH_PAYMENT_EMAILS_URL', plugin_dir_url(__FILE__));

require_once THESTITCH_PAYMENT_EMAILS_PATH . 'includes/class-source-adapter.php';
require_once THESTITCH_PAYMENT_EMAILS_PATH . 'includes/class-woocommerce-order-adapter.php';
require_once THESTITCH_PAYMENT_EMAILS_PATH . 'includes/class-recreate-submission-adapter.php';
require_once THESTITCH_PAYMENT_EMAILS_PATH . 'includes/class-payment-email-renderer.php';
require_once THESTITCH_PAYMENT_EMAILS_PATH . 'includes/class-payment-email-admin.php';

final class TheStitch_Payment_Emails {
    /** @var TheStitch_Payment_Email_Admin|null */
    private static $admin = null;

    public static function init() {
        self::$admin = new TheStitch_Payment_Email_Admin();
    }

    public static function allowed_currencies() {
        return ['AED', 'USD', 'EUR', 'GBP', 'SAR', 'QAR', 'KWD', 'BHD', 'OMR'];
    }

    public static function format_measurement_unit($unit) {
        if ($unit === 'cm') {
            return 'cm';
        }

        if ($unit === 'inches' || $unit === 'in') {
            return 'in';
        }

        return 'in';
    }

    public static function validate_payment_url($url) {
        $url = trim((string) $url);
        if ($url === '') {
            return new WP_Error('missing_url', 'Payment URL is required.');
        }

        $parsed = wp_parse_url($url);
        if (empty($parsed['scheme']) || strtolower($parsed['scheme']) !== 'https') {
            return new WP_Error('invalid_url', 'Payment URL must use HTTPS.');
        }

        $blocked_schemes = ['javascript', 'data', 'file'];
        if (in_array(strtolower($parsed['scheme']), $blocked_schemes, true)) {
            return new WP_Error('invalid_url', 'Payment URL scheme is not allowed.');
        }

        $host = strtolower((string) ($parsed['host'] ?? ''));
        if ($host === 'localhost' || str_starts_with($host, '127.') || str_starts_with($host, '0.0.0.0')) {
            return new WP_Error('invalid_url', 'Localhost payment URLs are not allowed.');
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return new WP_Error('invalid_url', 'Payment URL is malformed.');
        }

        return esc_url_raw($url);
    }

    public static function get_adapter($source_type, $source_id) {
        $source_id = absint($source_id);
        if (!$source_id) {
            return null;
        }

        if ($source_type === 'woocommerce_order') {
            if (!function_exists('wc_get_order')) {
                return null;
            }

            $order = wc_get_order($source_id);
            if (!$order) {
                return null;
            }

            return new TheStitch_WooCommerce_Order_Adapter($order);
        }

        if ($source_type === 'recreate_submission') {
            $post = get_post($source_id);
            if (!$post || $post->post_type !== 'form_submission') {
                return null;
            }

            return new TheStitch_Recreate_Submission_Adapter($post);
        }

        return null;
    }
}

add_action('plugins_loaded', ['TheStitch_Payment_Emails', 'init']);

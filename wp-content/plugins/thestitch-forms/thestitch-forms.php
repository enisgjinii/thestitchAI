<?php
/**
 * Plugin Name: The Stitch Custom Forms
 * Description: Custom Bridal Consultation and Recreate Submission Forms.
 * Version: 1.0.3
 * Author: Enis Gjini - VENOM
 */

if (!defined('ABSPATH')) {
    exit;
}

class TheStitch_Forms {

    private function asset_version($relative_path) {
        $full_path = plugin_dir_path(__FILE__) . ltrim($relative_path, '/');
        if (file_exists($full_path)) {
            return (string) filemtime($full_path);
        }
        return '1.0.3';
    }

    public function __construct() {
        add_action('init', [$this, 'register_cpt_submissions']);
        add_action('init', [$this, 'maybe_migrate_legacy_brown_colors']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_block_editor_assets']);
        add_action('admin_init', [$this, 'create_upload_directory']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_init', [$this, 'maybe_handle_settings_save']);
        add_action('init', [$this, 'register_blocks']);
        
        // Admin CPT Submissions Customizations
        add_filter('manage_form_submission_posts_columns', [$this, 'set_submission_columns']);
        add_action('manage_form_submission_posts_custom_column', [$this, 'custom_submission_column'], 10, 2);
        add_action('add_meta_boxes', [$this, 'add_submission_meta_box']);

        // Shortcodes
        add_shortcode('bridal_consultation_form', [$this, 'render_bridal_form']);
        add_shortcode('dream_outfit_form', [$this, 'render_dream_outfit_form']);
        add_shortcode('recreate_form', [$this, 'render_dream_outfit_form']);
        
        // AJAX handlers
        add_action('wp_ajax_submit_bridal_form', [$this, 'handle_bridal_form']);
        add_action('wp_ajax_nopriv_submit_bridal_form', [$this, 'handle_bridal_form']);
        add_action('wp_ajax_submit_dream_outfit_form', [$this, 'handle_dream_outfit_form']);
        add_action('wp_ajax_nopriv_submit_dream_outfit_form', [$this, 'handle_dream_outfit_form']);
        add_action('wp_ajax_submit_recreate_form', [$this, 'handle_dream_outfit_form']);
        add_action('wp_ajax_nopriv_submit_recreate_form', [$this, 'handle_dream_outfit_form']);
        add_action('wp_ajax_preview_form_style', [$this, 'preview_form_style']);
        add_action('wp_ajax_nopriv_preview_form_style', [$this, 'preview_form_style']);
        add_action('wp_ajax_thestitch_refresh_nonce', [$this, 'refresh_forms_nonce']);
        add_action('wp_ajax_nopriv_thestitch_refresh_nonce', [$this, 'refresh_forms_nonce']);
        add_action('wp_ajax_thestitch_send_test_customer_email', [$this, 'send_test_customer_email_ajax']);
        add_action('admin_post_thestitch_export_csv', [$this, 'handle_export_csv']);
        add_action('wp_ajax_thestitch_mark_read', [$this, 'handle_mark_read']);
        add_action('admin_post_thestitch_save_settings', [$this, 'handle_save_settings']);

        // Frontend safeguards: normalize CTA anchors on /create and /recreate,
        // and prevent duplicated configurator embeds on /create.
        add_action('wp_footer', [$this, 'dedupe_create_configurator_embed'], 100);

        // Notify admin when a new Create (WooCommerce / 3D configurator) order arrives.
        add_action('woocommerce_new_order', [$this, 'notify_admin_on_woocommerce_order'], 20, 2);
    }

    public function dedupe_create_configurator_embed() {
        if (is_admin() || !is_page(['create', 'recreate'])) {
            return;
        }
        ?>
        <script>
        (function () {
            var path = (window.location.pathname || '').replace(/\/+$/, '');
            var isCreatePage = /\/create$/i.test(path);
            var isRecreatePage = /\/recreate$/i.test(path);

            function ensureElementId(element, id) {
                if (!element) {
                    return;
                }

                if (!element.id || element.id !== id) {
                    element.id = id;
                }
            }

            function setRecreateAnchorTarget() {
                if (document.getElementById('RecreateForm')) {
                    return;
                }

                var recreateWrap = document.querySelector('.thestitch-form-container.recreate-form-wrap');
                if (recreateWrap) {
                    ensureElementId(recreateWrap, 'RecreateForm');
                }
            }

            function setCreateAnchorTarget() {
                if (document.getElementById('configurator')) {
                    return;
                }

                var iframe = document.querySelector('iframe[src*="3d-wearing-configurator.vercel.app"]');
                if (!iframe) {
                    return;
                }

                var target = iframe;
                if (iframe.closest) {
                    target = iframe.closest('.elementor-element, section, div') || iframe;
                } else if (iframe.parentNode) {
                    target = iframe.parentNode;
                }

                ensureElementId(target, 'configurator');
            }

            function removeById(id) {
                var node = document.getElementById(id);
                if (node && node.parentNode) {
                    node.parentNode.removeChild(node);
                }
            }

            function normalizeHash(hash) {
                var value = String(hash || '').toLowerCase().replace(/^#/, '');
                if (value === 'configurator' || value === 'create' || value === 'create-configurator') {
                    return 'configurator';
                }
                if (value === 'recreateform' || value === 'recreate-form' || value === 'recreate') {
                    return 'RecreateForm';
                }
                return null;
            }

            function scrollToTargetByHash(hash) {
                var normalized = normalizeHash(hash);
                if (!normalized) {
                    return;
                }

                var target = document.getElementById(normalized);
                if (target && typeof target.scrollIntoView === 'function') {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }

            function patchCtaLinks() {
                var links = Array.prototype.slice.call(document.querySelectorAll('a[href]'));
                if (!links.length) {
                    return;
                }

                links.forEach(function (link) {
                    var rawHref = link.getAttribute('href') || '';
                    if (!rawHref) {
                        return;
                    }

                    var lowerHref = rawHref.toLowerCase();

                    if (lowerHref === '#configurator' || lowerHref === '#create' || lowerHref === '#create-configurator') {
                        link.setAttribute('href', '/create/#configurator');
                        return;
                    }

                    if (lowerHref === '#recreate' || lowerHref === '#recreateform' || lowerHref === '#recreate-form') {
                        link.setAttribute('href', '/recreate/#RecreateForm');
                        return;
                    }

                    if (lowerHref.indexOf('/create#') !== -1 || lowerHref.indexOf('/create/#') !== -1) {
                        link.setAttribute('href', '/create/#configurator');
                        return;
                    }

                    if (lowerHref.indexOf('/recreate#') !== -1 || lowerHref.indexOf('/recreate/#') !== -1) {
                        link.setAttribute('href', '/recreate/#RecreateForm');
                    }
                });
            }

            function normalizeCurrentLocationHash() {
                if (!window.location.hash) {
                    return;
                }

                var normalized = normalizeHash(window.location.hash);
                if (!normalized) {
                    return;
                }

                var canonicalHash = normalized === 'configurator' ? '#configurator' : '#RecreateForm';
                if (window.location.hash !== canonicalHash && window.history && window.history.replaceState) {
                    window.history.replaceState(null, '', window.location.pathname + canonicalHash);
                }

                scrollToTargetByHash(canonicalHash);
            }

            window.addEventListener('hashchange', function () {
                normalizeCurrentLocationHash();
            });

            patchCtaLinks();

            if (isRecreatePage) {
                setRecreateAnchorTarget();
                normalizeCurrentLocationHash();
            }

            if (!isCreatePage) {
                return;
            }

            removeById('garment-configurator-wrapper');
            removeById('garment-configurator-container');
            setCreateAnchorTarget();
            normalizeCurrentLocationHash();

            var selector = 'iframe[src*="3d-wearing-configurator.vercel.app"]';
            var iframes = Array.prototype.slice.call(document.querySelectorAll(selector));

            if (iframes.length <= 1) {
                return;
            }

            for (var i = 1; i < iframes.length; i++) {
                var iframe = iframes[i];
                if (iframe && iframe.parentNode) {
                    iframe.parentNode.removeChild(iframe);
                }
            }

            if (window.MutationObserver) {
                var observer = new MutationObserver(function () {
                    removeById('garment-configurator-wrapper');
                    removeById('garment-configurator-container');
                    setCreateAnchorTarget();
                });

                observer.observe(document.body, { childList: true, subtree: true });

                setTimeout(function () {
                    observer.disconnect();
                }, 5000);
            }
        })();
        </script>
        <?php
    }

    public function register_blocks() {
        if (!function_exists('register_block_type')) {
            return;
        }

        register_block_type('thestitch/bridal-form', [
            'render_callback' => [$this, 'render_bridal_form']
        ]);
        
        register_block_type('thestitch/dream-outfit-form', [
            'render_callback' => [$this, 'render_dream_outfit_form']
        ]);
    }

    public function enqueue_admin_assets($hook) {
        $allowed_hooks = [
            'form_submission_page_thestitch-submissions',
            'form_submission_page_thestitch-forms-customize',
            'form_submission_page_thestitch-forms-help',
        ];

        $load_assets = in_array($hook, $allowed_hooks, true) || strpos($hook, 'thestitch-forms') !== false;

        if (!$load_assets && in_array($hook, ['post.php', 'post-new.php'], true)) {
            $screen = function_exists('get_current_screen') ? get_current_screen() : null;
            if ($screen && $screen->post_type === 'form_submission') {
                $load_assets = true;
            }
        }

        if (!$load_assets && function_exists('wc_get_page_screen_id')) {
            $order_screen = wc_get_page_screen_id('shop-order');
            if ($hook === $order_screen || $hook === 'woocommerce_page_wc-orders') {
                $load_assets = true;
            }
        }

        if (!$load_assets) {
            return;
        }
        wp_enqueue_media();
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_style('thestitch-forms-style', plugin_dir_url(__FILE__) . 'assets/css/forms.css', [], $this->asset_version('assets/css/forms.css'));
        wp_enqueue_script('thestitch-admin-js', plugin_dir_url(__FILE__) . 'assets/js/admin.js', ['wp-color-picker', 'jquery', 'jquery-ui-sortable'], $this->asset_version('assets/js/admin.js'), true);
        wp_enqueue_style('thestitch-admin-css', plugin_dir_url(__FILE__) . 'assets/css/admin.css', [], $this->asset_version('assets/css/admin.css'));

        if ($hook === 'form_submission_page_thestitch-forms-customize') {
            wp_enqueue_style('thestitch-intl-tel-input-style', 'https://cdn.jsdelivr.net/npm/intl-tel-input@26.9.1/build/css/intlTelInput.css', [], '26.9.1');
            wp_enqueue_script('thestitch-intl-tel-input-script', 'https://cdn.jsdelivr.net/npm/intl-tel-input@26.9.1/build/js/intlTelInput.min.js', [], '26.9.1', true);
            wp_enqueue_script('thestitch-forms-script', plugin_dir_url(__FILE__) . 'assets/js/forms.js', ['jquery', 'thestitch-intl-tel-input-script'], $this->asset_version('assets/js/forms.js'), true);

            wp_localize_script('thestitch-forms-script', 'thestitch_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('thestitch_forms_nonce'),
                'phone_utils_url' => 'https://cdn.jsdelivr.net/npm/intl-tel-input@26.9.1/build/js/utils.js'
            ]);
        }

        wp_localize_script('thestitch-admin-js', 'thestitch_admin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('thestitch_forms_nonce'),
            'email_template_presets' => $this->get_email_template_presets(),
            'site_name' => get_bloginfo('name'),
        ]);
    }

    public function enqueue_block_editor_assets() {
        wp_enqueue_script('thestitch-blocks-js', plugin_dir_url(__FILE__) . 'assets/js/blocks.js', ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-server-side-render'], $this->asset_version('assets/js/blocks.js'), true);
        
        // Ensure standard form styles load in the editor
        wp_enqueue_style('thestitch-forms-style', plugin_dir_url(__FILE__) . 'assets/css/forms.css', [], $this->asset_version('assets/css/forms.css'));
        
        // Inline CSS with customized colors so it looks right in block editor
        $colors = get_option('thestitch_forms_colors', $this->get_default_colors());
        $branding = get_option('thestitch_forms_branding', $this->get_default_branding());
        $width_value = $this->normalize_width_value($branding['width'] ?? '100%');
        
        $custom_css = "
        :root {
            --thestitch-primary: {$colors['button_primary']};
            --thestitch-hover: {$colors['button_hover']};
            --thestitch-border: {$colors['input_border']};
            --thestitch-focus: {$colors['input_focus']};
            --thestitch-success: {$colors['success_color']};
            --thestitch-error: {$colors['error_color']};
            --thestitch-bg: {$colors['background']};
            --thestitch-text: {$colors['text_color']};
            --thestitch-width: {$width_value};
            --thestitch-radius: {$branding['border_radius']}px;
            --thestitch-btn-radius: {$branding['button_radius']}px;
        }
        {$branding['custom_css']}
        ";
        wp_add_inline_style('thestitch-forms-style', $custom_css);
    }

    public function register_settings() {
        register_setting('thestitch_forms_settings', 'thestitch_forms_colors', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_colors']
        ]);
        register_setting('thestitch_forms_settings', 'thestitch_forms_branding', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_branding']
        ]);
        register_setting('thestitch_forms_settings', 'thestitch_forms_labels', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_labels']
        ]);
        register_setting('thestitch_forms_settings', 'thestitch_forms_email', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_email_settings']
        ]);
    }

    private function get_icon_markup($icon, $class = '') {
        $icons = [
            'sparkles' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.9 4.6L18.5 9.5l-4.6 1.9L12 16l-1.9-4.6L5.5 9.5l4.6-1.9L12 3z"></path><path d="M19 14l.9 2.1L22 17l-2.1.9L19 20l-.9-2.1L16 17l2.1-.9L19 14z"></path><path d="M5 14l.9 2.1L8 17l-2.1.9L5 20l-.9-2.1L2 17l2.1-.9L5 14z"></path></svg>',
            'camera' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h3l2-2h6l2 2h3v11H4z"></path><circle cx="12" cy="13" r="3.5"></circle></svg>',
            'search' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="6"></circle><path d="M20 20l-4-4"></path></svg>',
            'notes' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h9l3 3v15H6z"></path><path d="M9 9h6"></path><path d="M9 13h6"></path><path d="M9 17h4"></path></svg>',
            'palette' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a9 9 0 1 0 0 18h1.5a2.5 2.5 0 0 0 0-5H12a2 2 0 0 1 0-4h1a4 4 0 0 0 0-8h-1z"></path><circle cx="7.5" cy="10" r=".8" fill="currentColor"></circle><circle cx="9.5" cy="7" r=".8" fill="currentColor"></circle><circle cx="13.5" cy="7" r=".8" fill="currentColor"></circle></svg>',
            'ruler' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4l6 6-9 9-6-6z"></path><path d="M12 6l6 6"></path><path d="M9 9l2 2"></path><path d="M6 12l2 2"></path></svg>',
            'standard' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8 4h8l2 4-2 12H8L6 8l2-4z"></path><path d="M9 8h6"></path></svg>',
            'custom' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 19l4-4"></path><path d="M14 5l5 5"></path><path d="M7 17l-2 2"></path><path d="M3 21l2-2"></path><path d="M14 5l-7.5 7.5 4.5 4.5L18.5 9.5 14 5z"></path></svg>',
            'send' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 3L10 14"></path><path d="M21 3l-7 18-4-7-7-4 18-7z"></path></svg>',
            'database' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="7" ry="3"></ellipse><path d="M5 5v6c0 1.7 3.1 3 7 3s7-1.3 7-3V5"></path><path d="M5 11v6c0 1.7 3.1 3 7 3s7-1.3 7-3v-6"></path></svg>',
            'book' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v18H6.5A2.5 2.5 0 0 0 4 23z"></path><path d="M8 7h8"></path><path d="M8 11h8"></path></svg>',
            'settings' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.6 1.6 0 0 0 .3 1.8l.1.1a2 2 0 0 1-2.8 2.8l-.1-.1a1.6 1.6 0 0 0-1.8-.3 1.6 1.6 0 0 0-1 1.5V21a2 2 0 0 1-4 0v-.2a1.6 1.6 0 0 0-1-1.5 1.6 1.6 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.6 1.6 0 0 0 .3-1.8 1.6 1.6 0 0 0-1.5-1H3a2 2 0 0 1 0-4h.2a1.6 1.6 0 0 0 1.5-1 1.6 1.6 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.6 1.6 0 0 0 1.8.3h.1a1.6 1.6 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.2a1.6 1.6 0 0 0 1 1.5h.1a1.6 1.6 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.6 1.6 0 0 0-.3 1.8v.1a1.6 1.6 0 0 0 1.5 1H21a2 2 0 0 1 0 4h-.2a1.6 1.6 0 0 0-1.4 1z"></path></svg>',
            'calendar' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4"></path><path d="M8 3v4"></path><path d="M3 10h18"></path></svg>',
            'clock' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 3"></path></svg>',
            'phone' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1A19.5 19.5 0 0 1 5.2 13 19.8 19.8 0 0 1 2 4.3 2 2 0 0 1 4 2h3a2 2 0 0 1 2 1.7l.5 3.1a2 2 0 0 1-.6 1.8L7 10.5a16 16 0 0 0 6.5 6.5l1.9-1.9a2 2 0 0 1 1.8-.6l3.1.5A2 2 0 0 1 22 16.9z"></path></svg>',
            'mail' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="M3 7l9 6 9-6"></path></svg>',
            'user' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"></circle><path d="M4 20a8 8 0 0 1 16 0"></path></svg>'
        ];

        $svg = $icons[$icon] ?? $icons['sparkles'];

        return '<span class="ts-icon ' . esc_attr($class) . '" aria-hidden="true">' . $svg . '</span>';
    }

    private function sanitize_referral_code($input) {
        if ($input === null || $input === '') {
            return '';
        }

        $trimmed = trim((string) $input);
        if ($trimmed === '') {
            return '';
        }

        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $trimmed);
        if ($sanitized === '') {
            return '';
        }

        return substr($sanitized, 0, 64);
    }

    private function format_measurement_unit_label($unit) {
        if ($unit === 'cm') {
            return 'cm';
        }

        if ($unit === 'inches' || $unit === 'in') {
            return 'in';
        }

        // Legacy fallback for submissions saved before measurement_unit was stored.
        return 'in';
    }

    private function get_upload_field_label($field) {
        $labels = [
            'dream_images' => 'Outfit Images',
            'ref_images' => 'Reference Images',
            'color_images' => 'Color / Pattern Files',
        ];

        return $labels[$field] ?? 'Uploads';
    }

    private function get_measurement_field_labels() {
        return [
            'height' => 'Height',
            'preferred_fit' => 'Preferred Fit',
            'bust' => 'Bust',
            'waist' => 'Waist',
            'hips' => 'Hips',
            'sleeve_length' => 'Sleeve Length',
            'dress_length' => 'Dress Length',
            'shoulder_width' => 'Shoulder Width',
            'shoulder_to_bust_point' => 'Shoulder to Bust Point',
            'bust_point_to_bust_point' => 'Bust Point to Bust Point',
            'chest' => 'Chest (Full Bust)',
            'underbust' => 'Underbust',
            'shoulder_to_waist_front' => 'Shoulder to Waist (Front)',
            'shoulder_to_waist_back' => 'Shoulder to Waist (Back)',
            'armhole' => 'Armhole',
            'bicep_circumference' => 'Bicep Circumference',
            'elbow_circumference' => 'Elbow Circumference',
            'wrist_circumference' => 'Wrist Circumference',
            'neck_circumference' => 'Neck Circumference',
            'waist_to_hip' => 'Waist to Hip',
            'hip_circumference' => 'Hip Circumference',
            'thigh_circumference' => 'Thigh Circumference',
            'knee_circumference' => 'Knee Circumference',
            'calf_circumference' => 'Calf Circumference',
            'waist_to_floor' => 'Waist to Floor',
        ];
    }

    private function get_measurement_fields_by_fit($fit_type = 'quick-fit') {
        $quick = ['height', 'preferred_fit', 'bust', 'waist', 'hips', 'sleeve_length', 'dress_length'];
        $full = [
            'height', 'preferred_fit', 'shoulder_width', 'shoulder_to_bust_point', 'bust_point_to_bust_point',
            'chest', 'underbust', 'waist', 'shoulder_to_waist_front', 'shoulder_to_waist_back', 'armhole',
            'sleeve_length', 'bicep_circumference', 'elbow_circumference', 'wrist_circumference',
            'neck_circumference', 'waist_to_hip', 'hip_circumference', 'thigh_circumference',
            'knee_circumference', 'calf_circumference', 'waist_to_floor', 'dress_length'
        ];

        return $fit_type === 'full-fit' ? $full : $quick;
    }

    private function collect_custom_measurements_from_post($fit_type = 'quick-fit') {
        $fields = $this->get_measurement_fields_by_fit($fit_type);
        $measurements = [];

        foreach ($fields as $field) {
            if (!isset($_POST[$field])) {
                continue;
            }

            if ($field === 'preferred_fit') {
                $value = sanitize_text_field(wp_unslash($_POST[$field]));
                if (in_array($value, ['slim', 'regular', 'loose'], true)) {
                    $measurements[$field] = $value;
                }
                continue;
            }

            $value = floatval(wp_unslash($_POST[$field]));
            if ($value > 0) {
                $measurements[$field] = $value;
            }
        }

        return $measurements;
    }

    private function build_measurements_email_summary($fit_type, $measurements, $unit = 'inches') {
        if (empty($measurements) || !is_array($measurements)) {
            return 'No custom measurements provided.';
        }

        $labels = $this->get_measurement_field_labels();
        $lines = [];
        $lines[] = 'Custom Fit Type: ' . ($fit_type === 'full-fit' ? 'Full Fit' : 'Quick Fit');
        $lines[] = 'Measurement Unit: ' . $this->format_measurement_unit_label($unit);

        foreach ($measurements as $key => $value) {
            $label = isset($labels[$key]) ? $labels[$key] : ucwords(str_replace('_', ' ', $key));
            if ($key === 'preferred_fit') {
                $lines[] = $label . ': ' . ucfirst($value);
            } else {
                $lines[] = $label . ': ' . $value . ' ' . $this->format_measurement_unit_label($unit);
            }
        }

        return implode("\n", $lines);
    }

    private function get_admin_notification_recipient() {
        $email_settings = get_option('thestitch_forms_email', $this->get_default_email());
        $recipient = !empty($email_settings['recipient']) ? $email_settings['recipient'] : get_option('admin_email');
        return is_email($recipient) ? $recipient : get_option('admin_email');
    }

    private function create_email_thumbnail_url($filepath) {
        if (!file_exists($filepath) || !function_exists('wp_get_image_editor')) {
            return '';
        }

        $pathinfo = pathinfo($filepath);
        $thumb_filename = ($pathinfo['filename'] ?? 'image') . '-email-thumb.jpg';
        $thumb_path = trailingslashit($pathinfo['dirname'] ?? dirname($filepath)) . $thumb_filename;

        if (file_exists($thumb_path)) {
            $upload_dir = wp_upload_dir();
            return trailingslashit($upload_dir['baseurl']) . 'thestitch-forms/' . $thumb_filename;
        }

        $editor = wp_get_image_editor($filepath);
        if (is_wp_error($editor)) {
            return '';
        }

        $editor->resize(180, 180, true);
        $editor->set_quality(72);
        $saved = $editor->save($thumb_path, 'image/jpeg');

        if (is_wp_error($saved) || empty($saved['path'])) {
            return '';
        }

        $upload_dir = wp_upload_dir();
        return trailingslashit($upload_dir['baseurl']) . 'thestitch-forms/' . basename($saved['path']);
    }

    private function build_admin_submission_email_html($title, $details, $uploaded_files = [], $admin_url = '') {
        $rows_html = '';
        foreach ($details as $label => $value) {
            if ($value === '' || $value === null) {
                continue;
            }
            $rows_html .= '<tr>';
            $rows_html .= '<td style="padding:8px 0;border-bottom:1px solid #eeeeee;color:#666666;font-weight:600;width:42%;">' . esc_html($label) . '</td>';
            $rows_html .= '<td style="padding:8px 0;border-bottom:1px solid #eeeeee;color:#111111;">' . nl2br(esc_html((string) $value)) . '</td>';
            $rows_html .= '</tr>';
        }

        $images_html = '';
        if (!empty($uploaded_files) && is_array($uploaded_files)) {
            $group_labels = [
                'dream_images' => 'Outfit Images',
                'ref_images' => 'Reference Images',
                'color_images' => 'Color / Pattern Files',
            ];
            $grouped = [];
            foreach ($uploaded_files as $file) {
                $field = isset($file['field']) ? $file['field'] : 'dream_images';
                if (!isset($grouped[$field])) {
                    $grouped[$field] = [];
                }
                $grouped[$field][] = $file;
            }

            $images_html .= '<div style="margin-top:18px;">';
            $images_html .= '<div style="font-size:12px;font-weight:700;color:#111111;letter-spacing:.04em;text-transform:uppercase;margin-bottom:10px;">Uploaded Images</div>';

            foreach ($grouped as $field => $files) {
                $label = isset($group_labels[$field]) ? $group_labels[$field] : ucwords(str_replace('_', ' ', $field));
                $images_html .= '<div style="margin-bottom:12px;">';
                $images_html .= '<div style="font-size:12px;font-weight:600;color:#666666;margin-bottom:6px;">' . esc_html($label) . '</div>';
                $images_html .= '<div>';

                $shown = 0;
                foreach ($files as $file) {
                    if ($shown >= 4) {
                        break;
                    }

                    $filepath = '';
                    if (!empty($file['stored_name'])) {
                        $upload_dir = wp_upload_dir();
                        $filepath = trailingslashit($upload_dir['basedir']) . 'thestitch-forms/' . basename((string) $file['stored_name']);
                    }

                    $thumb_url = $filepath ? $this->create_email_thumbnail_url($filepath) : '';
                    if ($thumb_url === '' && !empty($file['url'])) {
                        $thumb_url = esc_url($file['url']);
                    }

                    if ($thumb_url === '') {
                        continue;
                    }

                    $images_html .= '<img src="' . esc_url($thumb_url) . '" alt="' . esc_attr($file['original_name'] ?? 'Upload') . '" width="90" height="90" style="width:90px;height:90px;object-fit:cover;border-radius:8px;border:1px solid #e5e5e5;margin:0 8px 8px 0;display:inline-block;">';
                    $shown++;
                }

                $remaining = max(0, count($files) - $shown);
                if ($remaining > 0) {
                    $images_html .= '<span style="display:inline-block;font-size:12px;color:#666666;vertical-align:top;padding-top:34px;">+' . esc_html((string) $remaining) . ' more</span>';
                }

                $images_html .= '</div></div>';
            }

            $images_html .= '</div>';
        }

        $admin_button_html = $admin_url
            ? '<div style="margin-top:20px;text-align:center;"><a href="' . esc_url($admin_url) . '" style="display:inline-block;background:#111111;color:#ffffff;padding:12px 22px;text-decoration:none;border-radius:8px;font-weight:600;font-size:14px;">View in WordPress</a></div>'
            : '';

        return '
            <div style="margin:0;padding:24px;background:#f5f5f5;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;color:#111111;">
                <div style="max-width:560px;margin:0 auto;background:#ffffff;border:1px solid #e5e5e5;border-radius:14px;overflow:hidden;">
                    <div style="padding:22px 24px;background:#111111;color:#ffffff;">
                        <div style="font-size:12px;letter-spacing:.08em;text-transform:uppercase;opacity:.9;">The Stitch</div>
                        <h1 style="margin:8px 0 0;font-size:24px;line-height:1.2;">' . esc_html($title) . '</h1>
                    </div>
                    <div style="padding:22px 24px;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">' . $rows_html . '</table>
                        ' . $images_html . '
                        ' . $admin_button_html . '
                    </div>
                </div>
            </div>
        ';
    }

    private function send_admin_submission_email($to, $subject, $title, $details, $uploaded_files = [], $admin_url = '') {
        if ($admin_url === '' && !empty($uploaded_files['__post_id'])) {
            $post_id = (int) $uploaded_files['__post_id'];
            unset($uploaded_files['__post_id']);
            $admin_url = get_edit_post_link($post_id, 'raw');
        }

        if ($admin_url === '') {
            $admin_url = admin_url('admin.php?page=thestitch-submissions');
        }

        $html = $this->build_admin_submission_email_html($title, $details, $uploaded_files, $admin_url);
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        wp_mail($to, $subject, $html, $headers);
    }

    public function notify_admin_on_woocommerce_order($order_id, $order = null) {
        if (!function_exists('wc_get_order')) {
            return;
        }

        if (!$order instanceof WC_Order) {
            $order = wc_get_order($order_id);
        }

        if (!$order) {
            return;
        }

        $is_configurator_order = $order->get_meta('_garment_configurator_order') === 'yes';
        if (!$is_configurator_order) {
            return;
        }

        $recipient = $this->get_admin_notification_recipient();
        if (!is_email($recipient)) {
            return;
        }

        $customer_name = trim($order->get_formatted_billing_full_name());
        if ($customer_name === '') {
            $customer_name = 'Guest customer';
        }

        $garment_config_raw = $order->get_meta('_garment_config_full');
        $garment_config = [];
        if (is_string($garment_config_raw) && $garment_config_raw !== '') {
            $decoded = json_decode($garment_config_raw, true);
            if (is_array($decoded)) {
                $garment_config = $decoded;
            }
        }

        $details = [
            'Order #' => (string) $order->get_id(),
            'Customer' => $customer_name,
            'Email' => $order->get_billing_email() ?: 'Not provided',
            'Phone' => $order->get_billing_phone() ?: 'Not provided',
            'Total' => wp_strip_all_tags($order->get_formatted_order_total()),
        ];

        $referral_code = $order->get_meta('_thestitch_referral_code');
        if ($referral_code !== '') {
            $details['Referral Code'] = $referral_code;
        }

        if (!empty($garment_config)) {
            $details['Garment'] = (string) ($garment_config['designDetails']['garmentType'] ?? $garment_config['garmentType'] ?? 'Custom');
            $details['Size'] = (string) ($garment_config['size'] ?? (!empty($garment_config['measurements']) ? 'Custom measurements' : 'Not selected'));
            $details['Primary Color'] = (string) ($garment_config['primaryColor'] ?? $garment_config['colors']['primary'] ?? 'N/A');
        }

        $pattern_url = $order->get_meta('Custom Pattern URL');
        if ($pattern_url === '') {
            $pattern_url = $order->get_meta('custom_pattern_url');
        }

        $uploaded_files = [];
        if ($pattern_url) {
            $uploaded_files[] = [
                'field' => 'dream_images',
                'original_name' => 'Fabric / pattern',
                'url' => esc_url_raw($pattern_url),
            ];
        }

        $preview_3d = $order->get_meta('View 3D Design');
        if ($preview_3d) {
            $details['3D Design Link'] = $preview_3d;
        }

        $subject = sprintf('New Create order #%d — %s', $order->get_id(), $customer_name);
        $admin_url = method_exists($order, 'get_edit_order_url') ? $order->get_edit_order_url() : admin_url('admin.php?page=wc-orders&action=edit&id=' . $order->get_id());

        $this->send_admin_submission_email(
            $recipient,
            $subject,
            'New Create Order',
            $details,
            $uploaded_files,
            $admin_url
        );
    }

    private function send_customer_confirmation_email($email, $subject, $message, $context = []) {
        if (empty($email) || !is_email($email)) {
            return;
        }

        $email_settings = get_option('thestitch_forms_email', $this->get_default_email());
        $site_name = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
        $safe_subject = sanitize_text_field($subject);
        $safe_message = wp_kses_post(wpautop($message));
        $submission_type = isset($context['type']) ? sanitize_text_field($context['type']) : 'submission';
        $submitted_at = date_i18n('F j, Y g:i A');
        $heading = !empty($email_settings['customer_email_heading'])
            ? sanitize_text_field($email_settings['customer_email_heading'])
            : 'Thank you ✨';
        $subheading = !empty($email_settings['customer_email_subheading'])
            ? sanitize_text_field($email_settings['customer_email_subheading'])
            : 'We received your request and our team is already reviewing it.';
        $signature = !empty($email_settings['customer_email_signature'])
            ? wp_kses_post(wpautop($email_settings['customer_email_signature']))
            : 'Need to add more details? Reply to this email and we’ll attach it to your request.';
        $theme = !empty($email_settings['customer_email_theme']) && in_array($email_settings['customer_email_theme'], ['luxury', 'classic', 'minimal'], true)
            ? $email_settings['customer_email_theme']
            : 'luxury';
        $button_text = !empty($email_settings['customer_email_button_text'])
            ? sanitize_text_field($email_settings['customer_email_button_text'])
            : '';
        $button_url = !empty($email_settings['customer_email_button_url'])
            ? esc_url($email_settings['customer_email_button_url'])
            : '';
        $use_custom_template = !empty($email_settings['customer_email_use_custom_template']) && $email_settings['customer_email_use_custom_template'] === 'yes';
        $custom_template_html = !empty($email_settings['customer_email_template_html'])
            ? wp_kses_post($email_settings['customer_email_template_html'])
            : '';

        $details_html = '';
        if (!empty($context['details']) && is_array($context['details'])) {
            $details_html .= '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin-top:8px;">';
            foreach ($context['details'] as $label => $value) {
                if ($value === '' || $value === null) {
                    continue;
                }
                $details_html .= '<tr>';
                $details_html .= '<td style="padding:8px 0;border-bottom:1px solid #eeeeee;color:#666666;font-weight:600;width:42%;">' . esc_html($label) . '</td>';
                $details_html .= '<td style="padding:8px 0;border-bottom:1px solid #eeeeee;color:#111111;">' . esc_html($value) . '</td>';
                $details_html .= '</tr>';
            }
            $details_html .= '</table>';
        }

        $theme_tokens = [
            'outer_bg' => '#f5f5f5',
            'card_border' => '#e5e5e5',
            'hero_bg' => '#111111',
            'hero_text' => '#ffffff',
            'note_bg' => '#fafafa',
            'note_border' => '#e5e5e5',
            'footer_bg' => '#ffffff',
            'footer_text' => '#666666',
            'cta_bg' => '#111111',
            'cta_text' => '#ffffff',
        ];

        if ($theme === 'classic') {
            $theme_tokens = [
                'outer_bg' => '#f5f7fb',
                'card_border' => '#dde5f0',
                'hero_bg' => 'linear-gradient(120deg,#6b7fa8,#8fabc8)',
                'hero_text' => '#ffffff',
                'note_bg' => '#f8fbff',
                'note_border' => '#dfeaf8',
                'footer_bg' => '#f9fbff',
                'footer_text' => '#5f6f84',
                'cta_bg' => '#6b7fa8',
                'cta_text' => '#ffffff',
            ];
        } elseif ($theme === 'minimal') {
            $theme_tokens = [
                'outer_bg' => '#f6f6f6',
                'card_border' => '#e7e7e7',
                'hero_bg' => '#ffffff',
                'hero_text' => '#2a2a2a',
                'note_bg' => '#fafafa',
                'note_border' => '#ececec',
                'footer_bg' => '#ffffff',
                'footer_text' => '#666666',
                'cta_bg' => '#2f2f2f',
                'cta_text' => '#ffffff',
            ];
        }

        $cta_html = '';
        if ($button_text !== '' && $button_url !== '') {
            $cta_html = '<div style="margin-top:18px;">'
                . '<a href="' . esc_url($button_url) . '" style="display:inline-block;padding:12px 18px;border-radius:10px;text-decoration:none;font-weight:600;background:' . esc_attr($theme_tokens['cta_bg']) . ';color:' . esc_attr($theme_tokens['cta_text']) . ';">'
                . esc_html($button_text)
                . '</a>'
                . '</div>';
        }

        if ($use_custom_template && $custom_template_html !== '') {
            $placeholders = [
                '{{heading}}' => esc_html($heading),
                '{{subheading}}' => esc_html($subheading),
                '{{message}}' => $safe_message,
                '{{submission_type}}' => esc_html(ucfirst($submission_type)),
                '{{submitted_at}}' => esc_html($submitted_at),
                '{{details_table}}' => $details_html,
                '{{cta_button}}' => $cta_html,
                '{{signature}}' => $signature,
                '{{site_name}}' => esc_html($site_name),
                '{{year}}' => esc_html(date('Y')),
            ];

            $html = strtr($custom_template_html, $placeholders);
            $headers = ['Content-Type: text/html; charset=UTF-8'];
            wp_mail($email, $safe_subject, $html, $headers);
            return;
        }

        $html = '
            <div style="margin:0;padding:24px;background:' . esc_attr($theme_tokens['outer_bg']) . ';font-family:Inter,-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#111111;">
                <div style="max-width:640px;margin:0 auto;background:#fff;border:1px solid ' . esc_attr($theme_tokens['card_border']) . ';border-radius:18px;overflow:hidden;box-shadow:0 14px 36px rgba(32,22,8,.08);">
                    <div style="padding:26px 28px;background:' . esc_attr($theme_tokens['hero_bg']) . ';color:' . esc_attr($theme_tokens['hero_text']) . ';">
                        <div style="font-size:13px;letter-spacing:.08em;text-transform:uppercase;opacity:.92;">The Stitch</div>
                        <h1 style="margin:8px 0 0;font-size:28px;line-height:1.2;color:#ffffff;">' . esc_html($heading) . '</h1>
                        <p style="margin:10px 0 0;font-size:15px;line-height:1.5;opacity:.96;color:#ffffff;">' . esc_html($subheading) . '</p>
                    </div>
                    <div style="padding:24px 28px;">
                        <div style="font-size:15px;line-height:1.7;color:#333333;">' . $safe_message . '</div>
                        ' . $cta_html . '
                        <div style="margin-top:20px;padding:16px 18px;background:' . esc_attr($theme_tokens['note_bg']) . ';border:1px solid ' . esc_attr($theme_tokens['note_border']) . ';border-radius:12px;">
                            <div style="font-size:13px;font-weight:700;color:#111111;letter-spacing:.04em;text-transform:uppercase;">Submission Summary</div>
                            <div style="margin-top:8px;font-size:14px;color:#111111;">
                                <div><strong>Type:</strong> ' . esc_html(ucfirst($submission_type)) . '</div>
                                <div><strong>Received:</strong> ' . esc_html($submitted_at) . '</div>
                            </div>
                            ' . $details_html . '
                        </div>
                    </div>
                    <div style="padding:16px 28px;border-top:1px solid #f2e8da;background:' . esc_attr($theme_tokens['footer_bg']) . ';color:' . esc_attr($theme_tokens['footer_text']) . ';font-size:12px;line-height:1.7;">
                        ' . $signature . '<br>
                        © ' . esc_html(date('Y')) . ' ' . esc_html($site_name) . '
                    </div>
                </div>
            </div>
        ';

        $headers = ['Content-Type: text/html; charset=UTF-8'];
        wp_mail($email, $safe_subject, $html, $headers);
    }

    public function sanitize_colors($input) {
        $output = [];
        $allowed_colors = ['button_primary', 'button_hover', 'input_border', 'input_focus', 'success_color', 'error_color', 'background', 'text_color'];
        foreach ($allowed_colors as $color) {
            if (isset($input[$color])) {
                // Validate hex color
                $value = sanitize_hex_color($input[$color]);
                if ($value) {
                    $output[$color] = $value;
                }
            }
        }
        return $output;
    }

    public function sanitize_branding($input) {
        $output = [];
        if (isset($input['width'])) {
            $output['width'] = $this->normalize_width_value($input['width']);
        }
        if (isset($input['border_radius'])) {
            $output['border_radius'] = absint($input['border_radius']);
        }
        if (isset($input['button_radius'])) {
            $output['button_radius'] = absint($input['button_radius']);
        }
        if (isset($input['padding'])) {
            $output['padding'] = absint($input['padding']);
        }
        if (isset($input['button_text'])) {
            $output['button_text'] = sanitize_text_field($input['button_text']);
        }
        if (isset($input['shadow'])) {
            $output['shadow'] = in_array($input['shadow'], ['yes', 'no']) ? $input['shadow'] : 'yes';
        }
        if (isset($input['custom_css'])) {
            $output['custom_css'] = sanitize_textarea_field($input['custom_css']);
        }
        if (isset($input['size_chart_image_url'])) {
            $output['size_chart_image_url'] = esc_url_raw($input['size_chart_image_url']);
        }
        return $output;
    }

    private function normalize_width_value($value) {
        $value = strtolower(trim((string) $value));

        if ($value === '') {
            return '100%';
        }

        if (preg_match('/^\d+$/', $value)) {
            $px = max(240, min(2000, (int) $value));
            return $px . 'px';
        }

        if (preg_match('/^(\d+)px$/', $value, $matches)) {
            $px = max(240, min(2000, (int) $matches[1]));
            return $px . 'px';
        }

        if (preg_match('/^(\d{1,3})%$/', $value, $matches)) {
            $percent = max(10, min(100, (int) $matches[1]));
            return $percent . '%';
        }

        return '100%';
    }

    public function sanitize_labels($input) {
        $output = [];
        $allowed = ['bridal_title', 'bridal_name', 'bridal_email', 'bridal_button', 'dream_title', 'dream_button'];
        foreach ($allowed as $key) {
            if (isset($input[$key])) {
                $output[$key] = sanitize_text_field($input[$key]);
            }
        }
        return $output;
    }

    public function sanitize_email_settings($input) {
        $output = [];
        if (isset($input['recipient'])) {
            $output['recipient'] = sanitize_email($input['recipient']);
        }
        if (isset($input['bridal_subject'])) {
            $output['bridal_subject'] = sanitize_text_field($input['bridal_subject']);
        }
        if (isset($input['dream_subject'])) {
            $output['dream_subject'] = sanitize_text_field($input['dream_subject']);
        }
        if (isset($input['send_customer_email'])) {
            $output['send_customer_email'] = in_array($input['send_customer_email'], ['yes', 'no']) ? $input['send_customer_email'] : 'yes';
        }
        if (isset($input['customer_message'])) {
            $output['customer_message'] = sanitize_textarea_field($input['customer_message']);
        }
        if (isset($input['customer_email_heading'])) {
            $output['customer_email_heading'] = sanitize_text_field($input['customer_email_heading']);
        }
        if (isset($input['customer_email_subheading'])) {
            $output['customer_email_subheading'] = sanitize_text_field($input['customer_email_subheading']);
        }
        if (isset($input['customer_email_signature'])) {
            $output['customer_email_signature'] = sanitize_textarea_field($input['customer_email_signature']);
        }
        if (isset($input['customer_email_theme'])) {
            $output['customer_email_theme'] = in_array($input['customer_email_theme'], ['luxury', 'minimal', 'classic'], true)
                ? $input['customer_email_theme']
                : 'luxury';
        }
        if (isset($input['customer_email_button_text'])) {
            $output['customer_email_button_text'] = sanitize_text_field($input['customer_email_button_text']);
        }
        if (isset($input['customer_email_button_url'])) {
            $output['customer_email_button_url'] = esc_url_raw($input['customer_email_button_url']);
        }
        if (isset($input['customer_email_use_custom_template'])) {
            $output['customer_email_use_custom_template'] = in_array($input['customer_email_use_custom_template'], ['yes', 'no'], true)
                ? $input['customer_email_use_custom_template']
                : 'no';
        }
        if (isset($input['customer_email_template_html'])) {
            $output['customer_email_template_html'] = wp_kses_post($input['customer_email_template_html']);
        }
        return $output;
    }

    public function add_admin_menu() {
        $unread = $this->get_unread_count();
        $badge  = $unread > 0
            ? ' <span class="awaiting-mod count-' . $unread . '"><span class="pending-count">' . $unread . '</span></span>'
            : '';

        remove_submenu_page( 'edit.php?post_type=form_submission', 'edit.php?post_type=form_submission' );
        remove_submenu_page( 'edit.php?post_type=form_submission', 'post-new.php?post_type=form_submission' );

        add_submenu_page(
            'edit.php?post_type=form_submission',
            'All Submissions',
            'All Submissions' . $badge,
            'manage_options',
            'thestitch-submissions',
            [$this, 'render_submissions_dashboard']
        );

        add_submenu_page(
            'edit.php?post_type=form_submission',
            'Customize Forms',
            'Customize',
            'manage_options',
            'thestitch-forms-customize',
            [$this, 'render_customize_page']
        );

        add_submenu_page(
            'edit.php?post_type=form_submission',
            'How to Use Forms',
            'How to Use',
            'manage_options',
            'thestitch-forms-help',
            [$this, 'render_help_page']
        );
    }

    public function render_customize_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Access denied');
        }

        $colors   = wp_parse_args((array) get_option('thestitch_forms_colors', []), $this->get_default_colors());
        $branding = wp_parse_args((array) get_option('thestitch_forms_branding', []), $this->get_default_branding());
        $labels   = wp_parse_args((array) get_option('thestitch_forms_labels', []), $this->get_default_labels());
        $email    = wp_parse_args((array) get_option('thestitch_forms_email', []), $this->get_default_email());
        $submission_count       = wp_count_posts('form_submission');
        $published_submissions  = isset($submission_count->publish) ? intval($submission_count->publish) : 0;

        // Show save notice
        $saved   = isset($_GET['ts_saved'])   && $_GET['ts_saved']   === '1';
        $save_err = isset($_GET['ts_error'])  && $_GET['ts_error']   === '1';
        ?>
        <div class="wrap ts-admin-wrap">

        <?php if ($saved) : ?>
        <div class="notice notice-success is-dismissible"><p><strong>Settings saved.</strong> All changes have been applied.</p></div>
        <?php elseif ($save_err) : ?>
        <div class="notice notice-error is-dismissible"><p><strong>Error:</strong> Settings could not be saved. Please try again.</p></div>
        <?php endif; ?>

        <!-- Page header -->
        <div class="ts-page-header">
            <div>
                <h1>Form Settings</h1>
                <p>Customize colors, branding, labels &amp; email notifications for your forms.</p>
            </div>
            <div class="ts-header-actions">
                <button type="button" id="ts-open-preview" class="ts-btn-secondary">&#128065; Preview Form</button>
                <input type="submit" form="ts-main-form" id="ts-save-btn-top" value="Save Settings" class="ts-btn-primary">
            </div>
        </div>

        <!-- Quick stats -->
        <div class="ts-stats">
            <div class="ts-stat-card">
                <div class="ts-stat-label">Total Submissions</div>
                <div class="ts-stat-value"><?php echo esc_html($published_submissions); ?></div>
                <div class="ts-stat-note">Stored in WordPress database</div>
            </div>
            <div class="ts-stat-card">
                <div class="ts-stat-label">Quick Style Presets</div>
                <div class="ts-stat-note" style="margin-top:6px;">
                    <div class="ts-presets" style="margin:0;">
                        <button type="button" class="ts-btn-secondary" id="ts-preset-dark">Dark Luxury</button>
                        <button type="button" class="ts-btn-secondary" id="ts-preset-light">Light Classic</button>
                        <button type="button" class="ts-btn-secondary" id="ts-preset-reset">Reset</button>
                    </div>
                </div>
            </div>
            <div class="ts-stat-card">
                <div class="ts-stat-label">Form Shortcodes</div>
                <div class="ts-stat-note" style="margin-top:6px;">
                    <span class="ts-sc-chip">[bridal_consultation_form]</span>
                    <span class="ts-sc-chip">[recreate_form]</span>
                </div>
            </div>
        </div>

        <!-- Main form -->
        <form method="post" action="<?php echo esc_url($this->get_admin_page_url('thestitch-forms-customize')); ?>" id="ts-main-form">
            <input type="hidden" name="ts_action" value="save_settings">
            <input type="hidden" name="page" value="thestitch-forms-customize">
            <input type="hidden" name="post_type" value="form_submission">
            <?php wp_nonce_field('thestitch_save_settings', 'thestitch_settings_nonce'); ?>

            <!-- Tab bar -->
            <div class="ts-tab-bar">
                <a href="#" class="ts-tab active" data-target="ts-pane-colors">Colors</a>
                <a href="#" class="ts-tab" data-target="ts-pane-branding">Branding</a>
                <a href="#" class="ts-tab" data-target="ts-pane-text">Text</a>
                <a href="#" class="ts-tab" data-target="ts-pane-email">Email</a>
            </div>

            <!-- ── COLORS ─────────────────────────────────────── -->
            <div id="ts-pane-colors" class="ts-pane active">
                <div class="ts-card">
                    <div class="ts-card-head">Colors <span class="ts-card-sub">Match your brand identity</span></div>
                    <div class="ts-card-body">
                        <div class="ts-color-grid">
                            <div class="ts-color-item">
                                <label>Primary Button Color</label>
                                <input type="text" name="thestitch_forms_colors[button_primary]" class="ts-color-input" value="<?php echo esc_attr($colors['button_primary'] ?? '#111111'); ?>">
                            </div>
                            <div class="ts-color-item">
                                <label>Button Hover Color</label>
                                <input type="text" name="thestitch_forms_colors[button_hover]" class="ts-color-input" value="<?php echo esc_attr($colors['button_hover'] ?? '#000000'); ?>">
                            </div>
                            <div class="ts-color-item">
                                <label>Input Border Color</label>
                                <input type="text" name="thestitch_forms_colors[input_border]" class="ts-color-input" value="<?php echo esc_attr($colors['input_border'] ?? '#e0e0e0'); ?>">
                            </div>
                            <div class="ts-color-item">
                                <label>Input Focus Color</label>
                                <input type="text" name="thestitch_forms_colors[input_focus]" class="ts-color-input" value="<?php echo esc_attr($colors['input_focus'] ?? '#111111'); ?>">
                            </div>
                            <div class="ts-color-item">
                                <label>Success Message Color</label>
                                <input type="text" name="thestitch_forms_colors[success_color]" class="ts-color-input" value="<?php echo esc_attr($colors['success_color'] ?? '#4caf50'); ?>">
                            </div>
                            <div class="ts-color-item">
                                <label>Error Message Color</label>
                                <input type="text" name="thestitch_forms_colors[error_color]" class="ts-color-input" value="<?php echo esc_attr($colors['error_color'] ?? '#f44336'); ?>">
                            </div>
                            <div class="ts-color-item">
                                <label>Form Background Color</label>
                                <input type="text" name="thestitch_forms_colors[background]" class="ts-color-input" value="<?php echo esc_attr($colors['background'] ?? '#ffffff'); ?>">
                            </div>
                            <div class="ts-color-item">
                                <label>Text Color</label>
                                <input type="text" name="thestitch_forms_colors[text_color]" class="ts-color-input" value="<?php echo esc_attr($colors['text_color'] ?? '#333333'); ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── BRANDING ───────────────────────────────────── -->
            <div id="ts-pane-branding" class="ts-pane">
                <div class="ts-card">
                    <div class="ts-card-head">Branding &amp; Layout</div>
                    <div class="ts-card-body">

                        <div class="ts-field">
                            <label>Form Width</label>
                            <input type="text" name="thestitch_forms_branding[width]" value="<?php echo esc_attr($branding['width'] ?? '100%'); ?>" placeholder="100% or 820px">
                            <p class="ts-hint">Examples: <code>100%</code>, <code>820px</code>, or just <code>820</code> (auto-converts).</p>
                        </div>

                        <div class="ts-field">
                            <label>Border Radius (px)</label>
                            <input type="number" name="thestitch_forms_branding[border_radius]" value="<?php echo esc_attr($branding['border_radius'] ?? '8'); ?>" min="0" max="50">
                            <p class="ts-hint">Rounded corners for the form container.</p>
                        </div>

                        <div class="ts-field">
                            <label>Button Border Radius (px)</label>
                            <input type="number" name="thestitch_forms_branding[button_radius]" value="<?php echo esc_attr($branding['button_radius'] ?? '8'); ?>" min="0" max="999">
                        </div>

                        <div class="ts-field">
                            <label>Form Padding (px)</label>
                            <input type="number" name="thestitch_forms_branding[padding]" value="<?php echo esc_attr($branding['padding'] ?? '30'); ?>" min="0" max="100">
                        </div>

                        <div class="ts-field">
                            <label>Submit Button Text</label>
                            <input type="text" name="thestitch_forms_branding[button_text]" value="<?php echo esc_attr($branding['button_text'] ?? 'Submit'); ?>">
                        </div>

                        <div class="ts-field">
                            <label>Enable Form Shadow</label>
                            <select name="thestitch_forms_branding[shadow]">
                                <option value="yes" <?php selected($branding['shadow'] ?? 'yes', 'yes'); ?>>Yes</option>
                                <option value="no"  <?php selected($branding['shadow'] ?? 'yes', 'no'); ?>>No</option>
                            </select>
                        </div>

                        <hr class="ts-sep">

                        <div class="ts-field">
                            <label>Size Chart Image <span style="font-weight:400;color:var(--ts-muted);">(optional)</span></label>
                            <input type="url" id="ts-size-chart-url" name="thestitch_forms_branding[size_chart_image_url]" value="<?php echo esc_attr($branding['size_chart_image_url'] ?? ''); ?>" placeholder="https://example.com/size-chart.png">
                            <p class="ts-hint">When set, the Recreate form shows this image instead of the built-in size table.</p>
                            <p style="margin-top:8px;">
                                <button type="button" class="button ts-btn-secondary" id="ts-pick-size-chart">Select from Media Library</button>
                                <button type="button" class="button ts-btn-secondary" id="ts-clear-size-chart" style="margin-left:6px;">Clear</button>
                            </p>
                            <div id="ts-size-chart-preview" style="<?php echo empty($branding['size_chart_image_url']) ? 'display:none;' : ''; ?>">
                                <img src="<?php echo esc_url($branding['size_chart_image_url'] ?? ''); ?>" alt="Size Chart Preview">
                            </div>
                        </div>

                        <div class="ts-field">
                            <label>Custom CSS <span style="font-weight:400;color:var(--ts-muted);">(optional)</span></label>
                            <textarea name="thestitch_forms_branding[custom_css]" rows="6" placeholder=".thestitch-form-container { color: red; }"><?php echo esc_textarea($branding['custom_css'] ?? ''); ?></textarea>
                        </div>

                    </div>
                </div>
            </div>

            <!-- ── TEXT / LABELS ─────────────────────────────── -->
            <div id="ts-pane-text" class="ts-pane">
                <div class="ts-card">
                    <div class="ts-card-head">Bridal Consultation Form</div>
                    <div class="ts-card-body">
                        <div class="ts-field">
                            <label>Form Title</label>
                            <input type="text" name="thestitch_forms_labels[bridal_title]" value="<?php echo esc_attr($labels['bridal_title'] ?? 'Bridal Consultation'); ?>">
                        </div>
                        <div class="ts-field">
                            <label>Full Name Placeholder</label>
                            <input type="text" name="thestitch_forms_labels[bridal_name]" value="<?php echo esc_attr($labels['bridal_name'] ?? 'Full Name'); ?>">
                        </div>
                        <div class="ts-field">
                            <label>Email Placeholder</label>
                            <input type="text" name="thestitch_forms_labels[bridal_email]" value="<?php echo esc_attr($labels['bridal_email'] ?? 'Email Address'); ?>">
                        </div>
                        <div class="ts-field">
                            <label>Submit Button Text</label>
                            <input type="text" name="thestitch_forms_labels[bridal_button]" value="<?php echo esc_attr($labels['bridal_button'] ?? 'Request Consultation'); ?>">
                        </div>
                    </div>
                </div>

                <div class="ts-card">
                    <div class="ts-card-head">Recreate Form</div>
                    <div class="ts-card-body">
                        <div class="ts-field">
                            <label>Form Title</label>
                            <input type="text" name="thestitch_forms_labels[dream_title]" value="<?php echo esc_attr($labels['dream_title'] ?? 'Recreate Form'); ?>">
                        </div>
                        <div class="ts-field">
                            <label>Submit Button Text</label>
                            <input type="text" name="thestitch_forms_labels[dream_button]" value="<?php echo esc_attr($labels['dream_button'] ?? 'Submit Request'); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── EMAIL ─────────────────────────────────────── -->
            <div id="ts-pane-email" class="ts-pane">

                <div class="ts-card">
                    <div class="ts-card-head">Notification Settings</div>
                    <div class="ts-card-body">
                        <div class="ts-field">
                            <label>Order notification email</label>
                            <input type="email" name="thestitch_forms_email[recipient]" value="<?php echo esc_attr($email['recipient'] ?? get_option('admin_email')); ?>">
                            <p class="ts-hint">New Bridal, Recreate, and Create (3D configurator) orders are emailed to this address — use the inbox you check on your phone.</p>
                        </div>
                        <div class="ts-field">
                            <label>Email Subject — Bridal Form</label>
                            <input type="text" name="thestitch_forms_email[bridal_subject]" value="<?php echo esc_attr($email['bridal_subject'] ?? 'New Bridal Consultation Request'); ?>">
                        </div>
                        <div class="ts-field">
                            <label>Email Subject — Recreate Form</label>
                            <input type="text" name="thestitch_forms_email[dream_subject]" value="<?php echo esc_attr($email['dream_subject'] ?? 'New Recreate Form Submission'); ?>">
                        </div>
                        <div class="ts-field">
                            <label>Send Confirmation to Customer</label>
                            <select name="thestitch_forms_email[send_customer_email]">
                                <option value="yes" <?php selected($email['send_customer_email'] ?? 'yes', 'yes'); ?>>Yes</option>
                                <option value="no"  <?php selected($email['send_customer_email'] ?? 'yes', 'no');  ?>>No</option>
                            </select>
                        </div>
                        <div class="ts-field">
                            <label>Customer Email Message</label>
                            <textarea name="thestitch_forms_email[customer_message]" rows="4"><?php echo esc_textarea($email['customer_message'] ?? 'Thank you for your submission! We will get back to you shortly.'); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="ts-card">
                    <div class="ts-card-head">Customer Email Design</div>
                    <div class="ts-card-body">
                        <div class="ts-field">
                            <label>Thank You Heading</label>
                            <input type="text" name="thestitch_forms_email[customer_email_heading]" value="<?php echo esc_attr($email['customer_email_heading'] ?? 'Thank you ✨'); ?>">
                        </div>
                        <div class="ts-field">
                            <label>Email Subheading</label>
                            <input type="text" name="thestitch_forms_email[customer_email_subheading]" value="<?php echo esc_attr($email['customer_email_subheading'] ?? 'We received your request and our team is already reviewing it.'); ?>">
                        </div>
                        <div class="ts-field">
                            <label>Email Theme</label>
                            <select name="thestitch_forms_email[customer_email_theme]">
                                <option value="luxury"  <?php selected($email['customer_email_theme'] ?? 'luxury', 'luxury'); ?>>Luxury Gradient</option>
                                <option value="classic" <?php selected($email['customer_email_theme'] ?? 'luxury', 'classic'); ?>>Classic Soft</option>
                                <option value="minimal" <?php selected($email['customer_email_theme'] ?? 'luxury', 'minimal'); ?>>Minimal Clean</option>
                            </select>
                        </div>
                        <div class="ts-field">
                            <label>CTA Button Text <span style="font-weight:400;color:var(--ts-muted);">(optional)</span></label>
                            <input type="text" name="thestitch_forms_email[customer_email_button_text]" value="<?php echo esc_attr($email['customer_email_button_text'] ?? ''); ?>" placeholder="View your request">
                        </div>
                        <div class="ts-field">
                            <label>CTA Button URL <span style="font-weight:400;color:var(--ts-muted);">(optional)</span></label>
                            <input type="url" name="thestitch_forms_email[customer_email_button_url]" value="<?php echo esc_attr($email['customer_email_button_url'] ?? ''); ?>" placeholder="https://your-site.com/contact">
                        </div>
                        <div class="ts-field">
                            <label>Email Footer Note</label>
                            <textarea name="thestitch_forms_email[customer_email_signature]" rows="3"><?php echo esc_textarea($email['customer_email_signature'] ?? 'Need to add more details? Reply to this email and we\'ll attach it to your request.'); ?></textarea>
                        </div>
                        <div class="ts-field">
                            <label>Use Custom Template</label>
                            <select name="thestitch_forms_email[customer_email_use_custom_template]" id="ts-use-custom-template">
                                <option value="no"  <?php selected($email['customer_email_use_custom_template'] ?? 'no', 'no'); ?>>No — use built-in theme above</option>
                                <option value="yes" <?php selected($email['customer_email_use_custom_template'] ?? 'no', 'yes'); ?>>Yes — use drag & drop builder</option>
                            </select>
                        </div>

                        <div id="ts-custom-template-row" style="<?php echo (($email['customer_email_use_custom_template'] ?? 'no') === 'yes') ? '' : 'display:none;'; ?>">

                            <p class="ts-section-label" style="margin-top:16px;">Template Presets</p>
                            <div class="ts-preset-template-row">
                                <button type="button" class="button ts-btn-secondary" data-template-preset="luxury">Luxury</button>
                                <button type="button" class="button ts-btn-secondary" data-template-preset="minimal">Minimal</button>
                                <button type="button" class="button ts-btn-secondary" data-template-preset="boutique">Boutique</button>
                            </div>

                            <p class="ts-section-label">Drag &amp; Drop Builder</p>
                            <p class="ts-hint" style="margin:0 0 10px;">Add blocks, drag to reorder, then click <strong>Apply to Editor</strong>.</p>

                            <div class="ts-builder-wrap">
                                <div class="ts-builder-tool-row">
                                    <span class="ts-tool-label">Add block:</span>
                                    <button type="button" class="ts-add-block-btn" data-add-block="hero">+ Hero</button>
                                    <button type="button" class="ts-add-block-btn" data-add-block="message">+ Message</button>
                                    <button type="button" class="ts-add-block-btn" data-add-block="summary">+ Summary</button>
                                    <button type="button" class="ts-add-block-btn" data-add-block="cta">+ CTA Button</button>
                                    <button type="button" class="ts-add-block-btn" data-add-block="footer">+ Footer</button>
                                </div>

                                <ul id="ts-builder-canvas" class="ts-builder-canvas"></ul>

                                <div class="ts-builder-actions">
                                    <button type="button" class="button button-primary ts-btn-primary" id="ts-builder-apply">Apply to Editor</button>
                                    <button type="button" class="button ts-btn-secondary" id="ts-builder-clear">Clear Builder</button>
                                </div>
                            </div>

                            <p class="ts-section-label" style="margin-top:16px;">Available Placeholders</p>
                            <p class="ts-hint" style="margin-bottom:10px;">
                                <code>{{heading}}</code> <code>{{subheading}}</code> <code>{{message}}</code>
                                <code>{{submission_type}}</code> <code>{{submitted_at}}</code> <code>{{details_table}}</code>
                                <code>{{cta_button}}</code> <code>{{signature}}</code> <code>{{site_name}}</code> <code>{{year}}</code>
                            </p>

                            <?php
                            wp_editor(
                                $email['customer_email_template_html'] ?? '',
                                'thestitch_forms_email_customer_email_template_html',
                                [
                                    'textarea_name' => 'thestitch_forms_email[customer_email_template_html]',
                                    'textarea_rows' => 14,
                                    'media_buttons' => false,
                                    'quicktags'     => true,
                                ]
                            );
                            ?>
                        </div>
                    </div>
                </div>

                <div class="ts-card">
                    <div class="ts-card-head">Send Test Email</div>
                    <div class="ts-card-body">
                        <div class="ts-field">
                            <label>Recipient for Test</label>
                            <input type="email" id="ts-test-email-recipient" value="<?php echo esc_attr(wp_get_current_user()->user_email ?: get_option('admin_email')); ?>" placeholder="you@example.com">
                            <p class="ts-hint">Sends a preview using current values on this screen (before saving).</p>
                        </div>
                        <div class="ts-test-row">
                            <button type="button" class="button button-secondary ts-btn-secondary" id="ts-send-test-email">Send Test Email</button>
                            <span id="ts-test-email-status"></span>
                        </div>
                    </div>
                </div>

            </div><!-- end email pane -->

            <!-- Save footer -->
            <div class="ts-card">
                <div class="ts-save-footer">
                    <input type="submit" id="ts-save-btn" value="Save All Settings" class="ts-btn-primary">
                    <span style="font-size:13px;color:var(--ts-muted);">Changes are applied immediately after saving.</span>
                </div>
            </div>

        </form>

        </div><!-- .ts-admin-wrap -->

        <!-- ── Fullscreen Preview Overlay ─────────────────── -->
        <div id="ts-preview-overlay">
            <div class="ts-preview-topbar">
                <div class="ts-preview-tabs">
                    <button type="button" class="ts-preview-tab active" data-form="bridal">Bridal Consultation</button>
                    <button type="button" class="ts-preview-tab" data-form="recreate">Recreate Form</button>
                    <button type="button" class="ts-preview-tab" data-form="email">&#9993; Email</button>
                </div>
                <div class="ts-preview-topbar-right">
                    <span class="ts-preview-label">Live Preview</span>
                    <button type="button" id="ts-close-preview" class="ts-preview-close" title="Close preview">&times;</button>
                </div>
            </div>
            <div class="ts-preview-body">
                <div id="ts-preview-bridal" class="ts-preview-pane active">
                    <?php echo do_shortcode('[bridal_consultation_form]'); ?>
                </div>
                <div id="ts-preview-recreate" class="ts-preview-pane" style="display:none;">
                    <?php echo do_shortcode('[recreate_form]'); ?>
                </div>
                <div id="ts-preview-email" class="ts-preview-pane" style="display:none;">
                    <div class="ts-email-preview-shell">
                        <div class="ts-email-preview-chrome">
                            <span class="ts-ep-dot"></span>
                            <span class="ts-ep-dot"></span>
                            <span class="ts-ep-dot"></span>
                            <span class="ts-ep-subject"><?php echo esc_html(get_option('thestitch_forms_email', $this->get_default_email())['bridal_subject'] ?? 'New Bridal Consultation Request'); ?></span>
                        </div>
                        <div class="ts-email-preview-content">
                            <iframe id="ts-email-preview-iframe" src="" title="Email Preview" scrolling="yes"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="ts-preview-backdrop"></div>

        <?php
    }

    private function get_default_colors() {
        return [
            'button_primary' => '#111111',
            'button_hover' => '#000000',
            'input_border' => '#e5e5e5',
            'input_focus' => '#111111',
            'success_color' => '#111111',
            'error_color' => '#dc2626',
            'background' => '#ffffff',
            'text_color' => '#111111',
        ];
    }

    public function maybe_migrate_legacy_brown_colors() {
        $colors = get_option('thestitch_forms_colors');
        if (!is_array($colors)) {
            return;
        }

        $legacy_primaries = ['#8b7355', '#6d5a47', '#c9a96e'];
        if (!in_array(strtolower((string) ($colors['button_primary'] ?? '')), $legacy_primaries, true)) {
            return;
        }

        update_option('thestitch_forms_colors', $this->get_default_colors());
    }

    private function get_default_branding() {
        return [
            'width' => '100%',
            'border_radius' => '8',
            'button_radius' => '8',
            'padding' => '30',
            'button_text' => 'Submit',
            'shadow' => 'yes',
            'custom_css' => '',
            'size_chart_image_url' => ''
        ];
    }

    private function get_default_labels() {
        return [
            'bridal_title' => 'Bridal Consultation',
            'bridal_name' => 'Full Name',
            'bridal_email' => 'Email Address',
            'bridal_button' => 'Request Consultation',
            'dream_title' => 'Recreate Form',
            'dream_button' => 'Send My Recreate Request'
        ];
    }

    private function get_default_email() {
        return [
            'recipient' => get_option('admin_email'),
            'bridal_subject' => 'New Bridal Consultation Request',
            'dream_subject' => 'New Recreate Form Submission',
            'send_customer_email' => 'yes',
            'customer_message' => 'Thank you for your submission! We will get back to you shortly.',
            'customer_email_heading' => 'Thank you ✨',
            'customer_email_subheading' => 'We received your request and our team is already reviewing it.',
            'customer_email_signature' => 'Need to add more details? Reply to this email and we’ll attach it to your request.',
            'customer_email_theme' => 'luxury',
            'customer_email_button_text' => '',
            'customer_email_button_url' => '',
            'customer_email_use_custom_template' => 'no',
            'customer_email_template_html' => ''
        ];
    }

    private function get_email_template_presets() {
        return [
            'luxury' => '<div style="margin:0;padding:24px;background:#f5f5f5;font-family:Inter,-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#111111;">\n'
                . '<div style="max-width:640px;margin:0 auto;background:#fff;border:1px solid #e5e5e5;border-radius:18px;overflow:hidden;box-shadow:0 14px 36px rgba(0,0,0,.08);">\n'
                . '<div style="padding:26px 28px;background:#111111;color:#fff;">\n'
                . '<div style="font-size:13px;letter-spacing:.08em;text-transform:uppercase;opacity:.92;">{{site_name}}</div>\n'
                . '<h1 style="margin:8px 0 0;font-size:28px;line-height:1.2;">{{heading}}</h1>\n'
                . '<p style="margin:10px 0 0;font-size:15px;line-height:1.5;opacity:.96;">{{subheading}}</p>\n'
                . '</div>\n'
                . '<div style="padding:24px 28px;">\n'
                . '<div style="font-size:15px;line-height:1.7;color:#333333;">{{message}}</div>\n'
                . '{{cta_button}}\n'
                . '<div style="margin-top:20px;padding:16px 18px;background:#fafafa;border:1px solid #e5e5e5;border-radius:12px;">\n'
                . '<div style="font-size:13px;font-weight:700;color:#111111;letter-spacing:.04em;text-transform:uppercase;">Submission Summary</div>\n'
                . '<div style="margin-top:8px;font-size:14px;color:#111111;">\n'
                . '<div><strong>Type:</strong> {{submission_type}}</div>\n'
                . '<div><strong>Received:</strong> {{submitted_at}}</div>\n'
                . '</div>\n'
                . '{{details_table}}\n'
                . '</div>\n'
                . '</div>\n'
                . '<div style="padding:16px 28px;border-top:1px solid #e5e5e5;background:#ffffff;color:#666666;font-size:12px;line-height:1.7;">\n'
                . '{{signature}}<br>\n'
                . '© {{year}} {{site_name}}\n'
                . '</div>\n'
                . '</div>\n'
                . '</div>',
            'minimal' => '<div style="margin:0;padding:22px;background:#f6f6f6;font-family:Arial,Helvetica,sans-serif;color:#222;">\n'
                . '<div style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #ececec;border-radius:14px;overflow:hidden;">\n'
                . '<div style="padding:24px 24px 8px;">\n'
                . '<h1 style="margin:0 0 6px;font-size:28px;color:#222;">{{heading}}</h1>\n'
                . '<p style="margin:0;color:#555;font-size:14px;line-height:1.6;">{{subheading}}</p>\n'
                . '</div>\n'
                . '<div style="padding:18px 24px 24px;">\n'
                . '<div style="font-size:15px;line-height:1.75;color:#333;">{{message}}</div>\n'
                . '{{cta_button}}\n'
                . '<div style="margin-top:18px;padding-top:12px;border-top:1px solid #efefef;font-size:13px;color:#666;">\n'
                . '<div><strong>Type:</strong> {{submission_type}}</div>\n'
                . '<div><strong>Received:</strong> {{submitted_at}}</div>\n'
                . '{{details_table}}\n'
                . '</div>\n'
                . '</div>\n'
                . '<div style="padding:14px 24px;background:#fafafa;border-top:1px solid #efefef;font-size:12px;color:#6a6a6a;">\n'
                . '{{signature}}<br>© {{year}} {{site_name}}\n'
                . '</div>\n'
                . '</div>\n'
                . '</div>',
            'boutique' => '<div style="margin:0;padding:24px;background:#f9f5f2;font-family:Georgia,Times New Roman,serif;color:#3d2f2a;">\n'
                . '<div style="max-width:660px;margin:0 auto;background:#fff;border:1px solid #ead9cf;border-radius:20px;overflow:hidden;">\n'
                . '<div style="padding:28px;background:#f0e2d7;border-bottom:1px solid #e8d5c8;">\n'
                . '<div style="font-size:12px;letter-spacing:.18em;text-transform:uppercase;color:#7f6454;">{{site_name}}</div>\n'
                . '<h1 style="margin:10px 0 0;font-size:30px;line-height:1.2;color:#4f3d32;">{{heading}}</h1>\n'
                . '<p style="margin:10px 0 0;font-size:15px;line-height:1.65;color:#6e5749;">{{subheading}}</p>\n'
                . '</div>\n'
                . '<div style="padding:24px 28px;">\n'
                . '<div style="font-size:16px;line-height:1.75;color:#47372d;">{{message}}</div>\n'
                . '{{cta_button}}\n'
                . '<div style="margin-top:20px;padding:14px 16px;background:#fff8f3;border:1px solid #f0dfd2;border-radius:12px;">\n'
                . '<div style="font-size:13px;font-weight:700;color:#7a5b4a;text-transform:uppercase;letter-spacing:.05em;">Request Details</div>\n'
                . '<div style="margin-top:7px;font-size:14px;color:#5b4438;">\n'
                . '<div><strong>Type:</strong> {{submission_type}}</div>\n'
                . '<div><strong>Received:</strong> {{submitted_at}}</div>\n'
                . '</div>\n'
                . '{{details_table}}\n'
                . '</div>\n'
                . '</div>\n'
                . '<div style="padding:16px 28px;background:#fefaf6;border-top:1px solid #efe2d8;font-size:12px;color:#8b6e5d;line-height:1.7;">\n'
                . '{{signature}}<br>© {{year}} {{site_name}}\n'
                . '</div>\n'
                . '</div>\n'
                . '</div>',
        ];
    }

    public function preview_form_style() {
        check_ajax_referer('thestitch_forms_nonce', 'nonce');
        $colors = get_option('thestitch_forms_colors', $this->get_default_colors());
        wp_send_json_success(['colors' => $colors]);
    }

    public function refresh_forms_nonce() {
        nocache_headers();
        wp_send_json_success([
            'nonce' => wp_create_nonce('thestitch_forms_nonce'),
        ]);
    }

    public function send_test_customer_email_ajax() {
        check_ajax_referer('thestitch_forms_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied.');
        }

        $to = isset($_POST['to_email']) ? sanitize_email(wp_unslash($_POST['to_email'])) : '';
        if (empty($to) || !is_email($to)) {
            wp_send_json_error('Please provide a valid test email address.');
        }

        $existing_settings = get_option('thestitch_forms_email', $this->get_default_email());
        $incoming = [
            'customer_message' => isset($_POST['customer_message']) ? sanitize_textarea_field(wp_unslash($_POST['customer_message'])) : ($existing_settings['customer_message'] ?? ''),
            'customer_email_heading' => isset($_POST['customer_email_heading']) ? sanitize_text_field(wp_unslash($_POST['customer_email_heading'])) : ($existing_settings['customer_email_heading'] ?? ''),
            'customer_email_subheading' => isset($_POST['customer_email_subheading']) ? sanitize_text_field(wp_unslash($_POST['customer_email_subheading'])) : ($existing_settings['customer_email_subheading'] ?? ''),
            'customer_email_signature' => isset($_POST['customer_email_signature']) ? sanitize_textarea_field(wp_unslash($_POST['customer_email_signature'])) : ($existing_settings['customer_email_signature'] ?? ''),
            'customer_email_theme' => isset($_POST['customer_email_theme']) ? sanitize_text_field(wp_unslash($_POST['customer_email_theme'])) : ($existing_settings['customer_email_theme'] ?? 'luxury'),
            'customer_email_button_text' => isset($_POST['customer_email_button_text']) ? sanitize_text_field(wp_unslash($_POST['customer_email_button_text'])) : ($existing_settings['customer_email_button_text'] ?? ''),
            'customer_email_button_url' => isset($_POST['customer_email_button_url']) ? esc_url_raw(wp_unslash($_POST['customer_email_button_url'])) : ($existing_settings['customer_email_button_url'] ?? ''),
            'customer_email_use_custom_template' => isset($_POST['customer_email_use_custom_template']) ? sanitize_text_field(wp_unslash($_POST['customer_email_use_custom_template'])) : ($existing_settings['customer_email_use_custom_template'] ?? 'no'),
            'customer_email_template_html' => isset($_POST['customer_email_template_html']) ? wp_kses_post(wp_unslash($_POST['customer_email_template_html'])) : ($existing_settings['customer_email_template_html'] ?? ''),
        ];

        $preview_settings = array_merge($existing_settings, $this->sanitize_email_settings($incoming));
        $preview_filter = function() use ($preview_settings) {
            return $preview_settings;
        };
        add_filter('pre_option_thestitch_forms_email', $preview_filter);

        $subject = isset($_POST['subject'])
            ? sanitize_text_field(wp_unslash($_POST['subject']))
            : 'The Stitch · Test Customer Confirmation Email';
        if ($subject === '') {
            $subject = 'The Stitch · Test Customer Confirmation Email';
        }

        $message = $incoming['customer_message'] !== ''
            ? $incoming['customer_message']
            : 'Thank you for your submission! We will get back to you shortly.';

        $this->send_customer_confirmation_email($to, $subject, $message, [
            'type' => 'test preview',
            'details' => [
                'Status' => 'This is a preview email',
                'Theme' => ucfirst($preview_settings['customer_email_theme'] ?? 'luxury'),
            ],
        ]);

        remove_filter('pre_option_thestitch_forms_email', $preview_filter);

        wp_send_json_success('Test email sent to ' . $to . '.');
    }

    public function render_help_page() {
        ?>
        <div class="wrap thestitch-help-wrap">
            <div class="ts-docs-hero">
                <div>
                    <h1>The Stitch Custom Forms</h1>
                    <p>Everything you need to place the forms, manage submissions, and keep the experience polished on the front-end.</p>
                </div>
                <div class="ts-docs-shortcodes">
                    <div><span>Bridal</span><code>[bridal_consultation_form]</code></div>
                    <div><span>Recreate</span><code>[recreate_form]</code></div>
                </div>
            </div>

            <div class="ts-docs-grid">
                <section class="ts-doc-card">
                    <h2>Available Forms</h2>
                    <div class="ts-doc-list">
                        <article>
                            <h3>Bridal Consultation Form</h3>
                            <p>Best for consultation bookings on the homepage, bridal page, or contact page.</p>
                            <code>[bridal_consultation_form]</code>
                        </article>
                        <article>
                            <h3>Recreate Form</h3>
                            <p>Best for the Recreate tab, custom design page, or services page.</p>
                            <code>[recreate_form]</code>
                        </article>
                    </div>
                </section>

                <section class="ts-doc-card">
                    <h2>How to Add the Forms</h2>
                    <div class="ts-doc-steps">
                        <div>
                            <h3>WordPress Block Editor</h3>
                            <ol>
                                <li>Open the page you want to edit.</li>
                                <li>Add a <strong>Shortcode</strong> block.</li>
                                <li>Paste the correct shortcode.</li>
                                <li>Update or publish the page.</li>
                            </ol>
                        </div>
                        <div>
                            <h3>Elementor</h3>
                            <ol>
                                <li>Edit the page with Elementor.</li>
                                <li>Drag in the <strong>Shortcode</strong> widget.</li>
                                <li>Paste <code>[bridal_consultation_form]</code> or <code>[recreate_form]</code>.</li>
                                <li>Save the page.</li>
                            </ol>
                        </div>
                    </div>
                </section>

                <section class="ts-doc-card">
                    <h2>Managing Submissions</h2>
                    <ul class="ts-doc-bullets">
                        <li>Go to <strong>Form Submissions</strong> in the left admin menu.</li>
                        <li>Open any record to see contact details, appointment data, sizing info, grouped uploads, and notes.</li>
                        <li>All submissions are saved in the WordPress database automatically.</li>
                        <li>Email notifications are sent based on your Customize settings.</li>
                    </ul>
                </section>

                <section class="ts-doc-card">
                    <h2>Frequently Asked Questions</h2>
                    <div class="ts-doc-faq">
                        <div>
                            <h3>Do I need another form plugin?</h3>
                            <p>No. This plugin runs standalone.</p>
                        </div>
                        <div>
                            <h3>Where are submissions stored?</h3>
                            <p>Inside WordPress under <strong>Form Submissions</strong>, with files stored in uploads.</p>
                        </div>
                        <div>
                            <h3>Can I customize the look?</h3>
                            <p>Yes. Use <strong>Form Submissions → Customize</strong> for quick presets, labels, colors, and email settings.</p>
                        </div>
                        <div>
                            <h3>Which file types are supported?</h3>
                            <p>JPG, PNG, GIF, and WebP up to 5MB per image.</p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <?php
    }

    public function create_upload_directory() {
        $upload_dir = wp_upload_dir();
        $forms_dir = $upload_dir['basedir'] . '/thestitch-forms';
        if (!is_dir($forms_dir)) {
            wp_mkdir_p($forms_dir);
        }
    }

    public function register_cpt_submissions() {
        register_post_type('form_submission', [
            'labels' => [
                'name'               => 'Form Submissions',
                'singular_name'      => 'Submission',
                'menu_name'          => 'Form Submissions',
                'add_new'            => 'Add New Submission',
                'add_new_item'       => 'Add New Submission',
                'edit_item'          => 'Edit Submission',
                'new_item'           => 'New Submission',
                'view_item'          => 'View Submission',
                'search_items'       => 'Search Submissions',
                'not_found'          => 'No submissions found',
                'not_found_in_trash' => 'No submissions found in Trash',
                'all_items'          => 'All Submissions',
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'supports' => ['title'],
            'menu_icon' => 'dashicons-email',
            'capability_type' => 'post',
        ]);
    }

    public function set_submission_columns($columns) {
        $new_columns = [];
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = 'Subject';
        $new_columns['submission_type'] = 'Type';
        $new_columns['email'] = 'Email';
        $new_columns['referral_code'] = 'Referral Code';
        $new_columns['phone']     = 'Phone';
        $new_columns['ts_status'] = 'Status';
        $new_columns['date']      = $columns['date'];
        return $new_columns;
    }

    public function custom_submission_column($column, $post_id) {
        switch ($column) {
            case 'submission_type':
                $title = get_the_title($post_id);
                if (strpos($title, 'Bridal Consultation') !== false) {
                    echo '<strong>Bridal Consultation</strong>';
                } elseif (strpos($title, 'Dream Outfit Submission') !== false || strpos($title, 'Recreate Form Submission') !== false) {
                    echo '<strong>Recreate</strong>';
                } else {
                    echo '-';
                }
                break;
            case 'email':
                $email = get_post_meta($post_id, 'email', true);
                if ($email) {
                    echo '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>';
                } else {
                    echo '-';
                }
                break;
            case 'referral_code':
                $referral_code = get_post_meta($post_id, 'referral_code', true);
                echo $referral_code ? esc_html($referral_code) : '<span class="ts-muted">Not provided</span>';
                break;
            case 'phone':
                echo esc_html(get_post_meta($post_id, 'phone', true) ?: '-');
                break;
            case 'ts_status':
                $st    = get_post_meta($post_id, 'ts_status', true) ?: 'new';
                $bg    = $st === 'new' ? '#dcfce7' : '#f3f4f6';
                $color = $st === 'new' ? '#16a34a' : '#6b7280';
                $lbl   = $st === 'new' ? 'New' : 'Read';
                echo '<span style="display:inline-block;padding:2px 10px;border-radius:999px;background:' . esc_attr($bg) . ';color:' . esc_attr($color) . ';font-size:11px;font-weight:700;">' . esc_html($lbl) . '</span>';
                break;
        }
    }

    public function add_submission_meta_box() {
        add_meta_box(
            'form_submission_details',
            'Submission Details',
            [$this, 'render_submission_meta_box'],
            'form_submission',
            'normal',
            'high'
        );
    }

    public function render_submission_meta_box($post) {
        // Auto-mark as read when admin opens this submission
        if (get_post_meta($post->ID, 'ts_status', true) === 'new') {
            update_post_meta($post->ID, 'ts_status', 'read');
        }
        $email = get_post_meta($post->ID, 'email', true);
        $full_name = get_post_meta($post->ID, 'full_name', true);
        $phone = get_post_meta($post->ID, 'phone', true);
        $country_code = get_post_meta($post->ID, 'country_code', true);
        $phone_country_iso = strtoupper((string) get_post_meta($post->ID, 'phone_country_iso', true));
        $mobile_number = get_post_meta($post->ID, 'mobile_number', true);
        $preferred_date = get_post_meta($post->ID, 'preferred_date', true);
        $preferred_time = get_post_meta($post->ID, 'preferred_time', true);
        $wedding_date = get_post_meta($post->ID, 'wedding_date', true);
        $message = get_post_meta($post->ID, 'message', true);
        $submission_type = get_post_meta($post->ID, 'submission_type', true);
        $sizing_type = get_post_meta($post->ID, 'sizing_type', true);
        $custom_fit_type = get_post_meta($post->ID, 'custom_fit_type', true);
        $standard_size = get_post_meta($post->ID, 'standard_size', true);
        $bust = get_post_meta($post->ID, 'bust', true);
        $waist = get_post_meta($post->ID, 'waist', true);
        $hips = get_post_meta($post->ID, 'hips', true);
        $notes = get_post_meta($post->ID, 'notes', true);
        $uploaded_files = get_post_meta($post->ID, 'uploaded_files', true);
        $upload_count_total = get_post_meta($post->ID, 'upload_count_total', true);
        $upload_count_outfit = get_post_meta($post->ID, 'upload_count_outfit', true);
        $upload_count_reference = get_post_meta($post->ID, 'upload_count_reference', true);
        $upload_count_color = get_post_meta($post->ID, 'upload_count_color', true);
        $referral_code = get_post_meta($post->ID, 'referral_code', true);
        $measurement_unit = get_post_meta($post->ID, 'measurement_unit', true);

        $is_dream_outfit = ($submission_type === 'recreate') || !empty($sizing_type);
        $submitted_at = get_the_date('M j, Y g:i a', $post);
        $grouped_files = [
            'dream_images' => [],
            'ref_images' => [],
            'color_images' => [],
        ];

        if (!$full_name && !$is_dream_outfit) {
            $full_name = str_replace('Bridal Consultation: ', '', get_the_title($post->ID));
        }

        if (empty($message) && !$is_dream_outfit) {
            $message = $post->post_content;
        }

        if (!empty($uploaded_files) && is_array($uploaded_files)) {
            foreach ($uploaded_files as $file) {
                $field = isset($file['field']) ? $file['field'] : 'dream_images';
                if (!isset($grouped_files[$field])) {
                    $grouped_files[$field] = [];
                }
                $grouped_files[$field][] = $file;
            }
        }

        echo '<div class="ts-submission-grid">';

        echo '<div class="ts-submission-card"><h3>Overview</h3><dl class="ts-submission-list">';
        echo '<div class="ts-submission-row"><dt>Type</dt><dd><span class="ts-type-badge ts-type-badge--' . ($is_dream_outfit ? 'recreate' : 'bridal') . '">' . esc_html($is_dream_outfit ? 'Recreate' : 'Bridal') . '</span></dd></div>';
        echo '<div class="ts-submission-row"><dt>Submitted</dt><dd>' . esc_html($submitted_at) . '</dd></div>';
        if (!$is_dream_outfit) {
            echo '<div class="ts-submission-row"><dt>Client</dt><dd>' . esc_html($full_name ?: '-') . '</dd></div>';
        }
        echo '<div class="ts-submission-row"><dt>Email</dt><dd>' . ($email ? '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>' : '-') . '</dd></div>';
        echo '<div class="ts-submission-row"><dt>Referral Code</dt><dd>' . esc_html($referral_code ?: 'Not provided') . '</dd></div>';
        echo '</dl></div>';

        if (!$is_dream_outfit) {
            echo '<div class="ts-submission-card"><h3>Contact & Appointment</h3><dl class="ts-submission-list">';
            echo '<div class="ts-submission-row"><dt>Phone</dt><dd>' . esc_html($phone ?: '-') . '</dd></div>';
            echo '<div class="ts-submission-row"><dt>Dial Code</dt><dd>' . esc_html($country_code ?: '-') . '</dd></div>';
            echo '<div class="ts-submission-row"><dt>Country</dt><dd>' . esc_html($phone_country_iso ?: '-') . '</dd></div>';
            echo '<div class="ts-submission-row"><dt>Local Number</dt><dd>' . esc_html($mobile_number ?: '-') . '</dd></div>';
            echo '<div class="ts-submission-row"><dt>Preferred Date</dt><dd>' . esc_html($preferred_date ?: '-') . '</dd></div>';
            echo '<div class="ts-submission-row"><dt>Preferred Time</dt><dd>' . esc_html($preferred_time ?: '-') . '</dd></div>';
            echo '<div class="ts-submission-row"><dt>Wedding Date</dt><dd>' . esc_html($wedding_date ?: '-') . '</dd></div>';
            echo '</dl></div>';

            echo '<div class="ts-submission-card" style="grid-column:1 / -1"><h3>Inquiry Details</h3><div class="ts-note-box">' . esc_html($message ?: 'No additional message provided.') . '</div></div>';
        } else {
            echo '<div class="ts-submission-card"><h3>Sizing</h3><dl class="ts-submission-list">';
            echo '<div class="ts-submission-row"><dt>Sizing Type</dt><dd>' . esc_html(ucfirst($sizing_type ?: '-')) . '</dd></div>';
            if ($sizing_type === 'standard') {
                echo '<div class="ts-submission-row"><dt>Standard Size</dt><dd>' . esc_html($standard_size ?: '-') . '</dd></div>';
            } else {
                $labels = $this->get_measurement_field_labels();
                $fit_type = $custom_fit_type && in_array($custom_fit_type, ['quick-fit', 'full-fit'], true) ? $custom_fit_type : 'quick-fit';
                $fit_title = $fit_type === 'full-fit' ? 'Full Fit' : 'Quick Fit';
                echo '<div class="ts-submission-row"><dt>Custom Fit Type</dt><dd>' . esc_html($fit_title) . '</dd></div>';
                $unit_label = $this->format_measurement_unit_label($measurement_unit);
                echo '<div class="ts-submission-row"><dt>Measurement Unit</dt><dd>' . esc_html($unit_label) . '</dd></div>';

                $measurement_fields = $this->get_measurement_fields_by_fit($fit_type);
                foreach ($measurement_fields as $field_key) {
                    $value = get_post_meta($post->ID, $field_key, true);
                    if ($value === '' || $value === null) {
                        continue;
                    }

                    $label = isset($labels[$field_key]) ? $labels[$field_key] : ucwords(str_replace('_', ' ', $field_key));
                    $display_value = $field_key === 'preferred_fit'
                        ? ucfirst((string) $value)
                        : ((string) $value . ' ' . $unit_label);
                    echo '<div class="ts-submission-row"><dt>' . esc_html($label) . '</dt><dd>' . esc_html($display_value) . '</dd></div>';
                }
            }
            echo '</dl></div>';

            echo '<div class="ts-submission-card"><h3>Upload Summary</h3><dl class="ts-submission-list">';
            echo '<div class="ts-submission-row"><dt>Total Files</dt><dd>' . esc_html($upload_count_total ?: (is_array($uploaded_files) ? count($uploaded_files) : 0)) . '</dd></div>';
            echo '<div class="ts-submission-row"><dt>Outfit</dt><dd>' . esc_html($upload_count_outfit ?: count($grouped_files['dream_images'])) . '</dd></div>';
            echo '<div class="ts-submission-row"><dt>Reference</dt><dd>' . esc_html($upload_count_reference ?: count($grouped_files['ref_images'])) . '</dd></div>';
            echo '<div class="ts-submission-row"><dt>Color / Pattern</dt><dd>' . esc_html($upload_count_color ?: count($grouped_files['color_images'])) . '</dd></div>';
            echo '</dl></div>';

            echo '<div class="ts-submission-card" style="grid-column:1 / -1"><h3>Design Notes</h3><div class="ts-note-box">' . esc_html($notes ?: 'No notes provided.') . '</div></div>';

            echo '<div class="ts-submission-card" style="grid-column:1 / -1"><h3>Uploaded Files</h3><div class="ts-upload-groups">';
            foreach ($grouped_files as $field => $files) {
                echo '<div class="ts-upload-group">';
                echo '<div class="ts-upload-group-header"><span class="ts-upload-group-title">' . esc_html($this->get_upload_field_label($field)) . '</span><span class="ts-upload-count">' . esc_html(count($files)) . ' file(s)</span></div>';
                if (!empty($files)) {
                    echo '<div class="ts-upload-grid">';
                    foreach ($files as $file) {
                        $file_name = !empty($file['original_name']) ? $file['original_name'] : basename((string) parse_url($file['url'], PHP_URL_PATH));
                        echo '<a class="ts-upload-item" href="' . esc_url($file['url']) . '" target="_blank" rel="noopener noreferrer">';
                        echo '<span class="ts-upload-thumb"><img src="' . esc_url($file['url']) . '" alt="' . esc_attr($file_name) . '"></span>';
                        echo '<span class="ts-upload-name">' . esc_html($file_name) . '</span>';
                        echo '</a>';
                    }
                    echo '</div>';
                } else {
                    echo '<p class="ts-muted">No files uploaded in this section.</p>';
                }
                echo '</div>';
            }
            echo '</div></div>';
        }

        echo '</div>';
    }

    public function enqueue_assets() {
        wp_enqueue_style('thestitch-forms-style', plugin_dir_url(__FILE__) . 'assets/css/forms.css', [], $this->asset_version('assets/css/forms.css'));
        wp_enqueue_style('thestitch-intl-tel-input-style', 'https://cdn.jsdelivr.net/npm/intl-tel-input@26.9.1/build/css/intlTelInput.css', [], '26.9.1');
        wp_enqueue_script('thestitch-intl-tel-input-script', 'https://cdn.jsdelivr.net/npm/intl-tel-input@26.9.1/build/js/intlTelInput.min.js', [], '26.9.1', true);
        wp_enqueue_script('thestitch-forms-script', plugin_dir_url(__FILE__) . 'assets/js/forms.js', ['jquery', 'thestitch-intl-tel-input-script'], $this->asset_version('assets/js/forms.js'), true);
        
        wp_localize_script('thestitch-forms-script', 'thestitch_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('thestitch_forms_nonce'),
            'phone_utils_url' => 'https://cdn.jsdelivr.net/npm/intl-tel-input@26.9.1/build/js/utils.js'
        ]);

        // Inline CSS with customized colors
        $colors = get_option('thestitch_forms_colors', $this->get_default_colors());
        $branding = get_option('thestitch_forms_branding', $this->get_default_branding());
        $width_value = $this->normalize_width_value($branding['width'] ?? '100%');
        
        $dynamic_css = "
        :root {
            --thestitch-primary: {$colors['button_primary']};
            --thestitch-hover: {$colors['button_hover']};
            --thestitch-border: {$colors['input_border']};
            --thestitch-focus: {$colors['input_focus']};
            --thestitch-success: {$colors['success_color']};
            --thestitch-error: {$colors['error_color']};
            --thestitch-bg: {$colors['background']};
            --thestitch-text: {$colors['text_color']};
            --thestitch-width: {$width_value};
            --thestitch-radius: {$branding['border_radius']}px;
            --thestitch-btn-radius: 999px;
            --thestitch-panel-padding: {$branding['padding']}px;
        }
        .thestitch-form-container {
            border-radius: var(--thestitch-radius);
            background: {$colors['background']};
        }
        .form-header-art {
            background: {$colors['button_primary']};
        }
        .btn-submit, .btn-next {
            background: {$colors['button_primary']} !important;
            border: 1px solid {$colors['button_primary']} !important;
            color: #ffffff !important;
            border-radius: 999px !important;
        }
        .btn-submit:hover, .btn-next:hover {
            background: {$colors['button_hover']} !important;
            border-color: {$colors['button_hover']} !important;
        }
        .thestitch-form input:focus, .thestitch-form textarea:focus, .thestitch-form select:focus {
            border-color: {$colors['input_focus']};
        }
        " . ($branding['custom_css'] ?? '');
        
        wp_add_inline_style('thestitch-forms-style', $dynamic_css);
    }

    public function render_bridal_form() {
        $labels = get_option('thestitch_forms_labels', $this->get_default_labels());
        $submit_label = $labels['bridal_button'] ?? 'Request Consultation';
        
        ob_start();
        ?>
        <div class="thestitch-form-container bridal-form-wrap">
            <div class="form-header-art bridal-header-art">
                <span class="header-icon"><?php echo $this->get_icon_markup('sparkles', 'hero-icon'); ?></span>
            </div>
            <h3><?php echo esc_html($labels['bridal_title'] ?? 'Bridal Consultation'); ?></h3>
            <p class="form-intro bridal-intro">Let&apos;s craft your perfect bridal look. Share your details and we&apos;ll schedule your consultation.</p>

            <form id="bridal-form" class="thestitch-form bridal-form">
                <div class="bridal-grid">
                    <div class="field-wrap field-half">
                        <label class="field-label" for="bridal_full_name">Full Name</label>
                        <input id="bridal_full_name" class="input-fun" type="text" name="full_name" placeholder="<?php echo esc_attr($labels['bridal_name'] ?? 'Full Name'); ?>" required>
                    </div>

                    <div class="field-wrap field-half">
                        <label class="field-label" for="bridal_mobile_number">Mobile Number</label>
                        <div class="phone-input-wrap">
                            <input id="bridal_mobile_number" class="input-fun" type="tel" name="mobile_number" placeholder="Mobile Number" autocomplete="tel" required>
                            <input type="hidden" name="country_code" value="">
                            <input type="hidden" name="phone_full" value="">
                            <input type="hidden" name="phone_country_iso" value="">
                        </div>
                    </div>

                    <div class="field-wrap field-half">
                        <label class="field-label" for="bridal_email">Email Address</label>
                        <input id="bridal_email" class="input-fun" type="email" name="email" placeholder="<?php echo esc_attr($labels['bridal_email'] ?? 'Email Address'); ?>" required>
                    </div>

                    <div class="field-wrap field-half">
                        <label class="field-label" for="bridal_wedding_date">Wedding Date</label>
                        <input id="bridal_wedding_date" class="input-fun" type="date" name="wedding_date">
                    </div>

                    <div class="field-wrap field-full">
                        <label class="field-label" for="bridal_message">Message / Inquiry</label>
                        <textarea id="bridal_message" name="message" placeholder="Tell us your vision, style, and any special requests" rows="4"></textarea>
                    </div>

                    <div class="field-wrap date-grid field-full">
                        <div>
                            <label class="field-label" for="bridal_preferred_date">Preferred Date</label>
                            <input id="bridal_preferred_date" class="input-fun" type="date" name="preferred_date" required>
                        </div>
                        <div>
                            <label class="field-label" for="bridal_preferred_time">Preferred Time</label>
                            <input id="bridal_preferred_time" class="input-fun" type="time" name="preferred_time" required>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit btn-fun btn-glow bridal-submit" data-default-label="<?php echo esc_attr($submit_label); ?>"><?php echo esc_html($submit_label); ?></button>
                <div id="bridal-form-response" class="form-response"></div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    public function render_dream_outfit_form() {
        $labels = get_option('thestitch_forms_labels', $this->get_default_labels());
        $branding = get_option('thestitch_forms_branding', $this->get_default_branding());
        $size_chart_image_url = isset($branding['size_chart_image_url']) ? esc_url($branding['size_chart_image_url']) : '';
        
        ob_start();
        ?>
        <div id="RecreateForm" class="thestitch-form-container multi-step recreate-form-wrap">
            <div class="form-header-art">
                <span class="header-icon"><?php echo $this->get_icon_markup('sparkles', 'hero-icon'); ?></span>
            </div>
            <h3><?php echo esc_html($labels['dream_title'] ?? 'Remake the Magic'); ?></h3>
            <p class="form-intro">Loved a look? Upload your inspiration, references, and sizing — we’ll tailor it perfectly to you.</p>

            <div class="step-track">
                <div class="step-node active" data-step="1">
                    <div class="node-circle"><span>1</span></div>
                    <div class="node-label">Inspiration</div>
                </div>
                <div class="step-line"></div>
                <div class="step-node" data-step="2">
                    <div class="node-circle"><span>2</span></div>
                    <div class="node-label">References</div>
                </div>
                <div class="step-line"></div>
                <div class="step-node" data-step="3">
                    <div class="node-circle"><span>3</span></div>
                    <div class="node-label">Notes</div>
                </div>
                <div class="step-line"></div>
                <div class="step-node" data-step="4">
                    <div class="node-circle"><span>4</span></div>
                    <div class="node-label">Color</div>
                </div>
                <div class="step-line"></div>
                <div class="step-node" data-step="5">
                    <div class="node-circle"><span>5</span></div>
                    <div class="node-label">Fit</div>
                </div>
            </div>

            <form id="dream-outfit-form" class="thestitch-form" enctype="multipart/form-data">
                
                <!-- Step 1 -->
                <div class="form-step active" id="step-1">
                    <div class="step-card">
                        <div class="step-emoji"><?php echo $this->get_icon_markup('camera', 'step-svg'); ?></div>
                        <h4>Show Us The Look</h4>
                        <p class="step-helper">Drop photos of the outfit you want us to recreate. The more the merrier!</p>
                        
                        <div class="drop-zone" data-target="dream_images">
                            <div class="drop-zone-content">
                                <div class="drop-icon"><?php echo $this->get_icon_markup('camera', 'drop-svg'); ?></div>
                                <p class="drop-text">Drag & drop images here</p>
                                <p class="drop-subtext">or click to browse</p>
                            </div>
                            <input type="file" name="dream_images[]" multiple accept="image/*" required>
                        </div>
                        <div class="file-preview" id="preview-dream_images"></div>
                    </div>
                    <div class="button-group">
                        <button type="button" class="btn-next btn-fun">
                            <span>Continue to Details</span>
                            <span class="btn-arrow">&rarr;</span>
                        </button>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="form-step" id="step-2" style="display: none;">
                    <div class="step-card">
                        <div class="step-emoji"><?php echo $this->get_icon_markup('search', 'step-svg'); ?></div>
                        <h4>Reference Images</h4>
                        <p class="step-helper">Share extra angles, close-ups, and details that help us recreate the look exactly.</p>
                        
                        <label class="field-label">Reference Images <span class="optional-tag">optional</span></label>
                        <div class="drop-zone" data-target="ref_images">
                            <div class="drop-zone-content">
                                <div class="drop-icon"><?php echo $this->get_icon_markup('search', 'drop-svg'); ?></div>
                                <p class="drop-text">Drop extra reference images</p>
                                <p class="drop-subtext">angles, close-ups, anything helpful</p>
                            </div>
                            <input type="file" name="ref_images[]" multiple accept="image/*">
                        </div>
                        <div class="file-preview" id="preview-ref_images"></div>
                    </div>
                    <div class="button-group">
                        <button type="button" class="btn-prev btn-fun btn-back">
                            <span class="btn-arrow">&larr;</span>
                            <span>Back</span>
                        </button>
                        <button type="button" class="btn-next btn-fun">
                            <span>Continue (or Skip)</span>
                            <span class="btn-arrow">&rarr;</span>
                        </button>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="form-step" id="step-3" style="display: none;">
                    <div class="step-card">
                        <div class="step-emoji"><?php echo $this->get_icon_markup('notes', 'step-svg'); ?></div>
                        <h4>Add Your Notes</h4>
                        <p class="step-helper">Tell us what to keep, tweak, remix, or personalize.</p>

                        <label class="field-label">Your Notes <span class="optional-tag">optional</span></label>
                        <div class="textarea-wrap">
                            <textarea name="notes" placeholder="Keep the neckline, add longer sleeves, switch to emerald green..." rows="4"></textarea>
                            <span class="char-hint">Tell us everything!</span>
                        </div>
                    </div>
                    <div class="button-group">
                        <button type="button" class="btn-prev btn-fun btn-back">
                            <span class="btn-arrow">&larr;</span>
                            <span>Back</span>
                        </button>
                        <button type="button" class="btn-next btn-fun">
                            <span>Continue (or Skip)</span>
                            <span class="btn-arrow">&rarr;</span>
                        </button>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="form-step" id="step-4" style="display: none;">
                    <div class="step-card">
                        <div class="step-emoji"><?php echo $this->get_icon_markup('palette', 'step-svg'); ?></div>
                        <h4>Color / Pattern</h4>
                        <p class="step-helper">Upload any swatches, fabric photos, or pattern references (optional).</p>

                        <label class="field-label">Color / Pattern Refs <span class="optional-tag">optional</span></label>
                        <div class="drop-zone" data-target="color_images">
                            <div class="drop-zone-content">
                                <div class="drop-icon"><?php echo $this->get_icon_markup('palette', 'drop-svg'); ?></div>
                                <p class="drop-text">Drop color swatches or patterns</p>
                                <p class="drop-subtext">helps us nail the exact shade</p>
                            </div>
                            <input type="file" name="color_images[]" multiple accept="image/*">
                        </div>
                        <div class="file-preview" id="preview-color_images"></div>
                    </div>
                    <div class="button-group">
                        <button type="button" class="btn-prev btn-fun btn-back">
                            <span class="btn-arrow">&larr;</span>
                            <span>Back</span>
                        </button>
                        <button type="button" class="btn-next btn-fun">
                            <span>Continue to Sizing</span>
                            <span class="btn-arrow">&rarr;</span>
                        </button>
                    </div>
                </div>

                <!-- Step 5 -->
                <div class="form-step" id="step-5" style="display: none;">
                    <div class="step-card">
                        <div class="step-emoji"><?php echo $this->get_icon_markup('ruler', 'step-svg'); ?></div>
                        <h4>Perfect Fit & Contact</h4>
                        <p class="step-helper">Last step! Let us know how to reach you and your sizing.</p>

                        <label class="field-label">Email Address</label>
                        <input type="email" name="email" placeholder="your@email.com" required class="input-fun">

                        <label class="field-label">Referral Code <span class="ts-optional">(Optional)</span></label>
                        <input type="text" name="referral_code" placeholder="Enter referral code if you have one" maxlength="64" autocomplete="off" class="input-fun">
                        <p class="step-helper">Letters, numbers, hyphens, and underscores only. Max 64 characters.</p>

                        <label class="field-label">How do you take your size?</label>
                        <div class="sizing-toggle">
                            <label class="toggle-option">
                                <input type="radio" name="sizing_type" value="standard" required>
                                <span class="toggle-card">
                                    <span class="toggle-icon"><?php echo $this->get_icon_markup('standard', 'toggle-svg'); ?></span>
                                    <span class="toggle-label">Standard</span>
                                    <span class="toggle-desc">XS &ndash; 5XL</span>
                                </span>
                            </label>
                            <label class="toggle-option">
                                <input type="radio" name="sizing_type" value="custom">
                                <span class="toggle-card">
                                    <span class="toggle-icon"><?php echo $this->get_icon_markup('custom', 'toggle-svg'); ?></span>
                                    <span class="toggle-label">Custom</span>
                                    <span class="toggle-desc">Your measurements</span>
                                </span>
                            </label>
                        </div>

                        <div id="standard-sizing" class="sizing-panel" style="display: none;">
                            <div class="size-header-row">
                                <label class="field-label">Pick Your Size</label>
                                <button type="button" class="size-chart-toggle" data-target="#size-chart-panel">View Size Chart</button>
                            </div>
                            <div class="size-chips">
                                <label class="chip"><input type="radio" name="standard_size" value="XS"><span>XS</span></label>
                                <label class="chip"><input type="radio" name="standard_size" value="S"><span>S</span></label>
                                <label class="chip"><input type="radio" name="standard_size" value="M"><span>M</span></label>
                                <label class="chip"><input type="radio" name="standard_size" value="L"><span>L</span></label>
                                <label class="chip"><input type="radio" name="standard_size" value="XL"><span>XL</span></label>
                                <label class="chip"><input type="radio" name="standard_size" value="2XL"><span>2XL</span></label>
                                <label class="chip"><input type="radio" name="standard_size" value="3XL"><span>3XL</span></label>
                                <label class="chip"><input type="radio" name="standard_size" value="4XL"><span>4XL</span></label>
                                <label class="chip"><input type="radio" name="standard_size" value="5XL"><span>5XL</span></label>
                            </div>

                            <div id="size-chart-panel" class="size-chart-panel" style="display:none;">
                                <p class="size-chart-note"><strong>The Stitch - Women's Dress Size Chart (XS to 5XL)</strong></p>
                                <?php if (!empty($size_chart_image_url)) : ?>
                                    <div class="size-chart-image-wrap">
                                        <img src="<?php echo esc_url($size_chart_image_url); ?>" alt="The Stitch Women's Dress Size Chart" class="size-chart-image" loading="lazy">
                                    </div>
                                <?php else : ?>
                                    <div class="size-chart-table-wrap">
                                        <table class="size-chart-table" role="table">
                                            <thead>
                                                <tr>
                                                    <th>Size</th>
                                                    <th>UK</th>
                                                    <th>US</th>
                                                    <th>EU</th>
                                                    <th>Bust (in)</th>
                                                    <th>Waist (in)</th>
                                                    <th>Hip (in)</th>
                                                    <th>Bust (cm)</th>
                                                    <th>Waist (cm)</th>
                                                    <th>Hip (cm)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr><td>XS</td><td>6</td><td>2</td><td>34</td><td>32-34</td><td>26-28</td><td>35-37</td><td>81-86</td><td>66-71</td><td>89-94</td></tr>
                                                <tr><td>S</td><td>8</td><td>4</td><td>36</td><td>34-36</td><td>28-30</td><td>37-39</td><td>86-91</td><td>71-76</td><td>94-99</td></tr>
                                                <tr><td>M</td><td>10</td><td>6</td><td>38</td><td>36-38</td><td>30-32</td><td>39-41</td><td>91-96</td><td>76-81</td><td>99-104</td></tr>
                                                <tr><td>L</td><td>12-14</td><td>8-10</td><td>40-42</td><td>38-41</td><td>32-35</td><td>41-44</td><td>96-104</td><td>81-89</td><td>104-112</td></tr>
                                                <tr><td>XL</td><td>16</td><td>12</td><td>44</td><td>41-43</td><td>35-37</td><td>44-46</td><td>104-109</td><td>89-94</td><td>112-117</td></tr>
                                                <tr><td>2XL</td><td>18</td><td>14</td><td>46</td><td>44-46</td><td>38-40</td><td>47-49</td><td>112-117</td><td>97-102</td><td>119-124</td></tr>
                                                <tr><td>3XL</td><td>20</td><td>16</td><td>48</td><td>48-50</td><td>41-43</td><td>51-53</td><td>122-127</td><td>104-110</td><td>130-135</td></tr>
                                                <tr><td>4XL</td><td>22-24</td><td>18-20</td><td>50-52</td><td>52-54</td><td>44-46</td><td>56-58</td><td>132-137</td><td>112-117</td><td>142-147</td></tr>
                                                <tr><td>5XL</td><td>26-28</td><td>22-24</td><td>54-56</td><td>56-58</td><td>47-49</td><td>59-61</td><td>142-147</td><td>119-124</td><td>150-155</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                                <p class="size-chart-note" style="margin-top:10px;">Note: Measurements are based on body dimensions (not garment size). For a comfortable fit, measure yourself while wearing light clothing.</p>
                            </div>
                        </div>

                        <div id="custom-sizing" class="sizing-panel" style="display: none;">
                            <label class="field-label">Measurement Unit</label>
                            <div class="ts-unit-selector" style="display:flex;gap:10px;margin-bottom:16px;">
                                <label class="toggle-option">
                                    <input type="radio" name="measurement_unit" value="inches" checked>
                                    <span class="toggle-card">
                                        <span class="toggle-label">Inches</span>
                                    </span>
                                </label>
                                <label class="toggle-option">
                                    <input type="radio" name="measurement_unit" value="cm">
                                    <span class="toggle-card">
                                        <span class="toggle-label">Centimeters</span>
                                    </span>
                                </label>
                            </div>
                            <label class="field-label">Select Custom Fit Type</label>
                            <div class="custom-fit-toggle">
                                <label class="toggle-option">
                                    <input type="radio" name="custom_fit_type" value="quick-fit">
                                    <span class="toggle-card">
                                        <span class="toggle-icon"><?php echo $this->get_icon_markup('ruler', 'toggle-svg'); ?></span>
                                        <span class="toggle-label">Quick Fit</span>
                                        <span class="toggle-desc">Essential measurements</span>
                                    </span>
                                </label>
                                <label class="toggle-option">
                                    <input type="radio" name="custom_fit_type" value="full-fit">
                                    <span class="toggle-card">
                                        <span class="toggle-icon"><?php echo $this->get_icon_markup('settings', 'toggle-svg'); ?></span>
                                        <span class="toggle-label">Full Fit</span>
                                        <span class="toggle-desc">Detailed tailoring set</span>
                                    </span>
                                </label>
                            </div>

                            <div class="custom-fit-section" data-fit-type="quick-fit" style="display:none;">
                                <label class="field-label">Quick Fit Measurements (<span class="ts-unit-label">inches</span>)</label>
                                <div class="measurement-grid measurement-grid-quick">
                                    <div class="measure-field"><input type="number" name="height" placeholder="&mdash;" step="any" min="0" class="input-fun"><span class="measure-label">Height</span></div>
                                    <div class="measure-field"><select name="preferred_fit" class="input-fun"><option value="">Preferred Fit</option><option value="slim">Slim</option><option value="regular">Regular</option><option value="loose">Loose</option></select><span class="measure-label">Fit Style</span></div>
                                    <div class="measure-field"><input type="number" name="bust" placeholder="&mdash;" step="any" min="0" class="input-fun"><span class="measure-label">Bust*</span></div>
                                    <div class="measure-field"><input type="number" name="waist" placeholder="&mdash;" step="any" min="0" class="input-fun"><span class="measure-label">Waist*</span></div>
                                    <div class="measure-field"><input type="number" name="hips" placeholder="&mdash;" step="any" min="0" class="input-fun"><span class="measure-label">Hips*</span></div>
                                    <div class="measure-field"><input type="number" name="sleeve_length" placeholder="&mdash;" step="any" min="0" class="input-fun"><span class="measure-label">Sleeve Length</span></div>
                                    <div class="measure-field"><input type="number" name="dress_length" placeholder="&mdash;" step="any" min="0" class="input-fun"><span class="measure-label">Dress Length</span></div>
                                </div>
                            </div>

                            <div class="custom-fit-section" data-fit-type="full-fit" style="display:none;">
                                <label class="field-label">Full Fit Measurements (<span class="ts-unit-label">inches</span>)</label>
                                <div class="measurement-grid measurement-grid-full">
                                    <div class="measure-field"><input type="number" name="height" placeholder="&mdash;" step="any" min="0" class="input-fun"><span class="measure-label">Height</span></div>
                                    <div class="measure-field"><select name="preferred_fit" class="input-fun"><option value="">Preferred Fit</option><option value="slim">Slim</option><option value="regular">Regular</option><option value="loose">Loose</option></select><span class="measure-label">Fit Style</span></div>
                                    <div class="measure-field"><input type="number" name="shoulder_width" placeholder="&mdash;" step="any" min="0" class="input-fun"><span class="measure-label">Shoulder Width</span></div>
                                    <div class="measure-field"><input type="number" name="shoulder_to_bust_point" placeholder="&mdash;" step="any" min="0" class="input-fun"><span class="measure-label">Shoulder to Bust Point</span></div>
                                    <div class="measure-field"><input type="number" name="bust_point_to_bust_point" placeholder="&mdash;" step="any" min="0" class="input-fun"><span class="measure-label">Bust Point to Bust Point</span></div>
                                    <div class="measure-field"><input type="number" name="chest" placeholder="&mdash;" step="any" min="0" class="input-fun"><span class="measure-label">Chest*</span></div>
                                    <div class="measure-field"><input type="number" name="underbust" placeholder="&mdash;" step="any" min="0" class="input-fun"><span class="measure-label">Underbust</span></div>
                                    <div class="measure-field"><input type="number" name="waist" placeholder="&mdash;" step="any" min="0" class="input-fun"><span class="measure-label">Waist*</span></div>
                                    <div class="measure-field"><input type="number" name="shoulder_to_waist_front" placeholder="&mdash;" step="any" min="0" class="input-fun"><span class="measure-label">Shoulder to Waist Front</span></div>
                                    <div class="measure-field"><input type="number" name="shoulder_to_waist_back" placeholder="&mdash;" step="any" min="0" class="input-fun"><span class="measure-label">Shoulder to Waist Back</span></div>
                                    <div class="measure-field"><input type="number" name="armhole" placeholder="&mdash;" step="any" min="0" class="input-fun"><span class="measure-label">Armhole</span></div>
                                    <div class="measure-field"><input type="number" name="sleeve_length" placeholder="&mdash;" step="any" min="0" class="input-fun"><span class="measure-label">Sleeve Length</span></div>
                                    <div class="measure-field"><input type="number" name="bicep_circumference" placeholder="&mdash;" step="any" min="0" class="input-fun"><span class="measure-label">Bicep Circumference</span></div>
                                    <div class="measure-field"><input type="number" name="elbow_circumference" placeholder="&mdash;" step="any" min="0" class="input-fun"><span class="measure-label">Elbow Circumference</span></div>
                                    <div class="measure-field"><input type="number" name="wrist_circumference" placeholder="&mdash;" step="any" min="0" class="input-fun"><span class="measure-label">Wrist Circumference</span></div>
                                    <div class="measure-field"><input type="number" name="neck_circumference" placeholder="&mdash;" step="any" min="0" class="input-fun"><span class="measure-label">Neck Circumference</span></div>
                                    <div class="measure-field"><input type="number" name="waist_to_hip" placeholder="&mdash;" step="any" min="0" class="input-fun"><span class="measure-label">Waist to Hip</span></div>
                                    <div class="measure-field"><input type="number" name="hip_circumference" placeholder="&mdash;" step="any" min="0" class="input-fun"><span class="measure-label">Hip Circumference*</span></div>
                                    <div class="measure-field"><input type="number" name="thigh_circumference" placeholder="&mdash;" step="any" min="0" class="input-fun"><span class="measure-label">Thigh Circumference</span></div>
                                    <div class="measure-field"><input type="number" name="knee_circumference" placeholder="&mdash;" step="any" min="0" class="input-fun"><span class="measure-label">Knee Circumference</span></div>
                                    <div class="measure-field"><input type="number" name="calf_circumference" placeholder="&mdash;" step="any" min="0" class="input-fun"><span class="measure-label">Calf Circumference</span></div>
                                    <div class="measure-field"><input type="number" name="waist_to_floor" placeholder="&mdash;" step="any" min="0" class="input-fun"><span class="measure-label">Waist to Floor</span></div>
                                    <div class="measure-field"><input type="number" name="dress_length" placeholder="&mdash;" step="any" min="0" class="input-fun"><span class="measure-label">Dress Length</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="button-group">
                        <button type="button" class="btn-prev btn-fun btn-back">
                            <span class="btn-arrow">&larr;</span>
                            <span>Back</span>
                        </button>
                        <button type="submit" class="btn-submit btn-fun btn-glow">
                            <span class="submit-icon"><?php echo $this->get_icon_markup('send', 'submit-svg'); ?></span>
                            <span><?php echo esc_html($labels['dream_button'] ?? 'Send My Request'); ?></span>
                        </button>
                    </div>
                </div>
                
                <div id="dream-form-response" class="form-response"></div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    public function handle_bridal_form() {
        check_ajax_referer('thestitch_forms_nonce', 'nonce');

        $name = isset($_POST['full_name']) ? sanitize_text_field(wp_unslash($_POST['full_name'])) : '';
        $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $country_code = isset($_POST['country_code']) ? sanitize_text_field(wp_unslash($_POST['country_code'])) : '';
        $phone_full = isset($_POST['phone_full']) ? sanitize_text_field(wp_unslash($_POST['phone_full'])) : '';
        $phone_country_iso = isset($_POST['phone_country_iso']) ? sanitize_text_field(wp_unslash($_POST['phone_country_iso'])) : '';
        $mobile_number = isset($_POST['mobile_number']) ? sanitize_text_field(wp_unslash($_POST['mobile_number'])) : '';
        $preferred_date = isset($_POST['preferred_date']) ? sanitize_text_field(wp_unslash($_POST['preferred_date'])) : '';
        $preferred_time = isset($_POST['preferred_time']) ? sanitize_text_field(wp_unslash($_POST['preferred_time'])) : '';
        $wedding_date = isset($_POST['wedding_date']) ? sanitize_text_field(wp_unslash($_POST['wedding_date'])) : '';
        $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';
        $stored_phone = !empty($phone_full) ? $phone_full : trim($country_code . ' ' . $mobile_number);

        if (empty($name) || empty($email) || empty($mobile_number) || empty($preferred_date) || empty($preferred_time)) {
            wp_send_json_error('Please fill all required fields.');
            wp_die();
        }

        if (empty($stored_phone)) {
            wp_send_json_error('Please provide a valid mobile number.');
            wp_die();
        }

        if (!is_email($email)) {
            wp_send_json_error('Please provide a valid email address.');
            wp_die();
        }
        
        $post_id = wp_insert_post([
            'post_title' => 'Bridal Consultation: ' . $name,
            'post_type' => 'form_submission',
            'post_status' => 'publish',
            'post_content' => $message
        ]);

        if ($post_id) {
            update_post_meta($post_id, 'ts_status', 'new');
            update_post_meta($post_id, 'submission_type', 'bridal');
            update_post_meta($post_id, 'full_name', $name);
            update_post_meta($post_id, 'email', $email);
            update_post_meta($post_id, 'message', $message);
            update_post_meta($post_id, 'phone', $stored_phone);
            update_post_meta($post_id, 'country_code', $country_code);
            update_post_meta($post_id, 'phone_country_iso', $phone_country_iso);
            update_post_meta($post_id, 'mobile_number', $mobile_number);
            update_post_meta($post_id, 'preferred_date', $preferred_date);
            update_post_meta($post_id, 'preferred_time', $preferred_time);
            update_post_meta($post_id, 'wedding_date', $wedding_date);
            
            // Send admin email
            $email_settings = get_option('thestitch_forms_email', $this->get_default_email());
            $to = $this->get_admin_notification_recipient();
            $subject = !empty($email_settings['bridal_subject']) ? $email_settings['bridal_subject'] : 'New Bridal Consultation Request';
            $this->send_admin_submission_email($to, $subject, 'New Bridal Consultation', [
                'Name' => $name,
                'Email' => $email,
                'Phone' => $stored_phone,
                'Preferred Date/Time' => trim($preferred_date . ' ' . $preferred_time),
                'Wedding Date' => $wedding_date,
                'Message' => $message,
            ], [], get_edit_post_link($post_id, 'raw'));

            // Optional customer confirmation
            if (!empty($email_settings['send_customer_email']) && $email_settings['send_customer_email'] === 'yes') {
                $customer_subject = 'We received your bridal consultation request';
                $customer_message = !empty($email_settings['customer_message'])
                    ? $email_settings['customer_message']
                    : 'Thank you for your submission! We will get back to you shortly.';
                $this->send_customer_confirmation_email($email, $customer_subject, $customer_message, [
                    'type' => 'bridal consultation',
                    'details' => [
                        'Preferred Date' => $preferred_date,
                        'Preferred Time' => $preferred_time,
                        'Wedding Date' => $wedding_date,
                    ],
                ]);
            }

            wp_send_json_success('Thank you! Your consultation request has been received.');
        }

        wp_send_json_error('There was an error submitting your request. Please try again.');
    }

    public function handle_dream_outfit_form() {
        check_ajax_referer('thestitch_forms_nonce', 'nonce');

        if (!isset($_POST['email']) || !isset($_POST['sizing_type'])) {
            wp_send_json_error('Missing required fields.');
            wp_die();
        }

        $email = sanitize_email(wp_unslash($_POST['email']));
        $sizing_type = sanitize_text_field(wp_unslash($_POST['sizing_type']));
        $notes = isset($_POST['notes']) ? sanitize_textarea_field(wp_unslash($_POST['notes'])) : '';
        $measurement_unit = (isset($_POST['measurement_unit']) && $_POST['measurement_unit'] === 'cm') ? 'cm' : 'inches';
        $referral_code = isset($_POST['referral_code'])
            ? $this->sanitize_referral_code(wp_unslash($_POST['referral_code']))
            : '';

        if (!is_email($email)) {
            wp_send_json_error('Please provide a valid email address.');
            wp_die();
        }

        if (!in_array($sizing_type, ['standard', 'custom'], true)) {
            wp_send_json_error('Invalid sizing type selected.');
            wp_die();
        }

        $custom_fit_type = 'quick-fit';
        $custom_measurements = [];

        if ($sizing_type === 'standard') {
            $allowed_sizes = ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL'];
            $standard_size = isset($_POST['standard_size']) ? sanitize_text_field(wp_unslash($_POST['standard_size'])) : '';
            if (!in_array($standard_size, $allowed_sizes, true)) {
                wp_send_json_error('Please select a valid standard size.');
                wp_die();
            }
        } else {
            $custom_fit_type = isset($_POST['custom_fit_type']) ? sanitize_text_field(wp_unslash($_POST['custom_fit_type'])) : '';
            if (!in_array($custom_fit_type, ['quick-fit', 'full-fit'], true)) {
                wp_send_json_error('Please select a custom fit type.');
                wp_die();
            }

            $custom_measurements = $this->collect_custom_measurements_from_post($custom_fit_type);
            $required_fields = $custom_fit_type === 'full-fit'
                ? ['chest', 'waist', 'hip_circumference']
                : ['bust', 'waist', 'hips'];

            foreach ($required_fields as $required_field) {
                if (empty($custom_measurements[$required_field])) {
                    wp_send_json_error('Please provide all required custom measurements.');
                    wp_die();
                }
            }
        }

        if (empty($_FILES['dream_images']) || empty($_FILES['dream_images']['name']) || empty($_FILES['dream_images']['name'][0])) {
            wp_send_json_error('Please upload at least one outfit image.');
            wp_die();
        }

        
        // Handle file uploads
        $upload_dir = wp_upload_dir();
        $forms_dir = $upload_dir['basedir'] . '/thestitch-forms';
        
        if (!is_dir($forms_dir)) {
            wp_mkdir_p($forms_dir);
        }

        $uploaded_files = [];

        // Process dream images
        if (!empty($_FILES['dream_images'])) {
            $dream_files = $this->handle_file_uploads($_FILES['dream_images'], 'dream_images', $forms_dir);
            $uploaded_files = array_merge($uploaded_files, $dream_files);
        }

        // Process reference images
        if (!empty($_FILES['ref_images'])) {
            $ref_files = $this->handle_file_uploads($_FILES['ref_images'], 'ref_images', $forms_dir);
            $uploaded_files = array_merge($uploaded_files, $ref_files);
        }

        // Process color images
        if (!empty($_FILES['color_images'])) {
            $color_files = $this->handle_file_uploads($_FILES['color_images'], 'color_images', $forms_dir);
            $uploaded_files = array_merge($uploaded_files, $color_files);
        }

        // Create post
        $post_content = "Sizing Type: $sizing_type\n\nNotes: $notes\n\nUploaded Files: " . count($uploaded_files);
        
        $post_id = wp_insert_post([
            'post_title' => 'Recreate Form Submission: ' . $email,
            'post_type' => 'form_submission',
            'post_status' => 'publish',
            'post_content' => $post_content
        ]);

        if ($post_id) {
            update_post_meta($post_id, 'ts_status', 'new');
            $dream_images_count = count(array_filter($uploaded_files, static function($file) {
                return isset($file['field']) && $file['field'] === 'dream_images';
            }));
            $reference_images_count = count(array_filter($uploaded_files, static function($file) {
                return isset($file['field']) && $file['field'] === 'ref_images';
            }));
            $color_images_count = count(array_filter($uploaded_files, static function($file) {
                return isset($file['field']) && $file['field'] === 'color_images';
            }));

            update_post_meta($post_id, 'submission_type', 'recreate');
            update_post_meta($post_id, 'email', $email);
            update_post_meta($post_id, 'referral_code', $referral_code);
            update_post_meta($post_id, 'sizing_type', $sizing_type);
            update_post_meta($post_id, 'notes', $notes);
            update_post_meta($post_id, 'uploaded_files', $uploaded_files);
            update_post_meta($post_id, 'upload_count_total', count($uploaded_files));
            update_post_meta($post_id, 'upload_count_outfit', $dream_images_count);
            update_post_meta($post_id, 'upload_count_reference', $reference_images_count);
            update_post_meta($post_id, 'upload_count_color', $color_images_count);

            // Store sizing details
            if ($sizing_type === 'standard') {
                update_post_meta($post_id, 'standard_size', sanitize_text_field(wp_unslash($_POST['standard_size'])));
            } else {
                update_post_meta($post_id, 'custom_fit_type', $custom_fit_type);
                update_post_meta($post_id, 'measurement_unit', $measurement_unit);
                foreach ($custom_measurements as $field_key => $field_value) {
                    update_post_meta($post_id, $field_key, $field_value);
                }
            }

            // Send admin email
            $email_settings = get_option('thestitch_forms_email', $this->get_default_email());
            $to = $this->get_admin_notification_recipient();
            $subject = !empty($email_settings['dream_subject']) ? $email_settings['dream_subject'] : 'New Recreate Form Submission';
            $measurement_summary = $sizing_type === 'custom'
                ? $this->build_measurements_email_summary($custom_fit_type, $custom_measurements, $measurement_unit)
                : 'Standard Size: ' . sanitize_text_field(wp_unslash($_POST['standard_size']));

            $admin_details = [
                'Email' => $email,
                'Sizing Type' => ucfirst($sizing_type),
                'Measurements / Size' => $measurement_summary,
                'Notes' => $notes,
                'Total Files Uploaded' => (string) count($uploaded_files),
            ];

            if ($referral_code !== '') {
                $admin_details['Referral Code'] = $referral_code;
            }

            $this->send_admin_submission_email(
                $to,
                $subject,
                'New Recreate Request',
                $admin_details,
                $uploaded_files,
                get_edit_post_link($post_id, 'raw')
            );

            // Optional customer confirmation
            if (!empty($email_settings['send_customer_email']) && $email_settings['send_customer_email'] === 'yes') {
                $customer_subject = 'We received your recreate request';
                $customer_message = !empty($email_settings['customer_message'])
                    ? $email_settings['customer_message']
                    : 'Thank you for your submission! We will get back to you shortly.';

                $size_summary = $sizing_type === 'standard'
                    ? sanitize_text_field(wp_unslash($_POST['standard_size']))
                    : ($custom_fit_type === 'full-fit' ? 'Full Fit (Custom)' : 'Quick Fit (Custom)');

                $this->send_customer_confirmation_email($email, $customer_subject, $customer_message, [
                    'type' => 'recreate request',
                    'details' => [
                        'Sizing Type' => ucfirst($sizing_type),
                        'Selected Fit / Size' => $size_summary,
                        'Measurement Unit' => $sizing_type === 'custom' ? $this->format_measurement_unit_label($measurement_unit) : '',
                        'Referral Code' => $referral_code !== '' ? $referral_code : '',
                        'Uploaded Images' => count($uploaded_files),
                    ],
                ]);
            }

            wp_send_json_success('Thank you! Your recreate request is in. Our team will review it and get back to you shortly.');
        } else {
            wp_send_json_error('There was an error saving your submission. Please try again.');
        }

        wp_die();
    }

    private function handle_file_uploads($files, $field_name, $base_dir) {
        $uploaded_files = [];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_file_size = 5 * 1024 * 1024; // 5MB

        if (!is_array($files['name'])) {
            return $uploaded_files;
        }

        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            // Validate file type
            $file_type = wp_check_filetype($files['name'][$i]);
            if (!in_array($file_type['type'], $allowed_types)) {
                continue;
            }

            // Validate file size
            if ($files['size'][$i] > $max_file_size) {
                continue;
            }

            // Generate unique filename
            $original_name = sanitize_file_name($files['name'][$i]);
            $filename = time() . '_' . wp_rand(100, 999) . '_' . $original_name;
            $filepath = $base_dir . '/' . $filename;

            // Move uploaded file
            if (move_uploaded_file($files['tmp_name'][$i], $filepath)) {
                $uploaded_files[] = [
                    'original_name' => $original_name,
                    'stored_name' => $filename,
                    'field' => $field_name,
                    'url' => wp_upload_dir()['baseurl'] . '/thestitch-forms/' . $filename
                ];
            }
        }

        return $uploaded_files;
    }

    private function delete_submission_uploads( $post_id ) {
        $uploaded_files = get_post_meta( $post_id, 'uploaded_files', true );

        if ( empty( $uploaded_files ) || ! is_array( $uploaded_files ) ) {
            return;
        }

        $upload_dir = wp_upload_dir();
        $forms_dir  = trailingslashit( $upload_dir['basedir'] ) . 'thestitch-forms/';

        foreach ( $uploaded_files as $file ) {
            $stored_name = ! empty( $file['stored_name'] ) ? basename( (string) $file['stored_name'] ) : '';
            $file_path   = $stored_name ? $forms_dir . $stored_name : '';

            if ( $file_path === '' && ! empty( $file['url'] ) ) {
                $file_path = $forms_dir . basename( (string) parse_url( $file['url'], PHP_URL_PATH ) );
            }

            if ( $file_path && file_exists( $file_path ) ) {
                if ( function_exists( 'wp_delete_file' ) ) {
                    wp_delete_file( $file_path );
                } else {
                    unlink( $file_path );
                }
            }
        }
    }

    // =========================================================
    // Submissions Dashboard
    // =========================================================

    public function render_submissions_dashboard() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Access denied' );
        }

        $marked_notice = false;
        $type_filter   = isset( $_GET['ts_type'] ) ? sanitize_text_field( wp_unslash( $_GET['ts_type'] ) ) : 'all';
        $search_query  = isset( $_GET['ts_search'] ) ? sanitize_text_field( wp_unslash( $_GET['ts_search'] ) ) : '';
        $page_url      = admin_url( 'admin.php?page=thestitch-submissions' );

        $redirect_args = [];
        if ( $type_filter !== 'all' ) {
            $redirect_args['ts_type'] = $type_filter;
        }
        if ( $search_query !== '' ) {
            $redirect_args['ts_search'] = $search_query;
        }

        $current_page_url = add_query_arg(
            array_merge( [ 'page' => 'thestitch-submissions' ], $redirect_args ),
            admin_url( 'admin.php' )
        );

        $delete_redirect = function ( $notice, $count = 1 ) use ( $page_url, $redirect_args ) {
            $args = $redirect_args;
            $args['ts_notice'] = $notice;
            $args['ts_count']  = absint( $count );
            wp_safe_redirect( add_query_arg( $args, $page_url ) );
            exit;
        };

        // Handle mark-all-read (nonce-protected GET action processed inline)
        if (
            isset( $_GET['ts_action'] ) && $_GET['ts_action'] === 'mark_all_read' &&
            isset( $_GET['_wpnonce'] ) &&
            wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'ts_mark_all_read' )
        ) {
            $new_ids = get_posts( [
                'post_type'      => 'form_submission',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'meta_query'     => [ [ 'key' => 'ts_status', 'value' => 'new' ] ],
            ] );
            foreach ( $new_ids as $id ) {
                update_post_meta( $id, 'ts_status', 'read' );
            }
            $marked_notice = true;
        }

        if (
            isset( $_GET['ts_action'] ) && $_GET['ts_action'] === 'delete_submission' &&
            isset( $_GET['ts_id'], $_GET['_wpnonce'] )
        ) {
            $submission_id = absint( wp_unslash( $_GET['ts_id'] ) );
            $nonce         = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) );

            if ( $submission_id && wp_verify_nonce( $nonce, 'thestitch_delete_submission_' . $submission_id ) ) {
                if ( current_user_can( 'delete_post', $submission_id ) ) {
                    $this->delete_submission_uploads( $submission_id );
                    wp_delete_post( $submission_id, true );
                    $delete_redirect( 'deleted', 1 );
                }

                wp_die( 'Access denied' );
            }

            wp_die( 'Invalid delete request' );
        }

        if (
            $_SERVER['REQUEST_METHOD'] === 'POST' &&
            isset( $_POST['ts_action'] ) && $_POST['ts_action'] === 'bulk_delete' &&
            isset( $_POST['thestitch_submission_nonce'] )
        ) {
            check_admin_referer( 'thestitch_submission_bulk_action', 'thestitch_submission_nonce' );

            $selected_ids = isset( $_POST['selected_ids'] ) ? (array) wp_unslash( $_POST['selected_ids'] ) : [];
            $selected_ids = array_values( array_filter( array_map( 'absint', $selected_ids ) ) );

            if ( empty( $selected_ids ) ) {
                $delete_redirect( 'none', 0 );
            }

            $deleted_count = 0;
            foreach ( $selected_ids as $submission_id ) {
                if ( ! current_user_can( 'delete_post', $submission_id ) ) {
                    continue;
                }

                $this->delete_submission_uploads( $submission_id );

                if ( wp_delete_post( $submission_id, true ) ) {
                    $deleted_count++;
                }
            }

            $delete_redirect( $deleted_count > 0 ? 'bulk_deleted' : 'none', $deleted_count );
        }

        $base_args = [
            'post_type'      => 'form_submission',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];

        $filter_args = $base_args;
        if ( $type_filter === 'bridal' ) {
            $filter_args['meta_query'] = [ [ 'key' => 'submission_type', 'value' => 'bridal' ] ];
        } elseif ( $type_filter === 'recreate' ) {
            $filter_args['meta_query'] = [ [ 'key' => 'submission_type', 'value' => 'recreate' ] ];
        } elseif ( $type_filter === 'new' ) {
            $filter_args['meta_query'] = [ [ 'key' => 'ts_status', 'value' => 'new' ] ];
        }

        $submissions = get_posts( $filter_args );

        // PHP-level search on name / email
        if ( ! empty( $search_query ) ) {
            $sq          = strtolower( $search_query );
            $submissions = array_values( array_filter( $submissions, function ( $sub ) use ( $sq ) {
                $n = strtolower( (string) get_post_meta( $sub->ID, 'full_name', true ) );
                $e = strtolower( (string) get_post_meta( $sub->ID, 'email', true ) );
                $r = strtolower( (string) get_post_meta( $sub->ID, 'referral_code', true ) );
                return strpos( $n, $sq ) !== false || strpos( $e, $sq ) !== false || strpos( $r, $sq ) !== false;
            } ) );
        }

        // Stats (always reflect latest data after any action)
        $total          = count( get_posts( array_merge( $base_args, [ 'fields' => 'ids' ] ) ) );
        $bridal_count   = count( get_posts( array_merge( $base_args, [ 'fields' => 'ids', 'meta_query' => [ [ 'key' => 'submission_type', 'value' => 'bridal' ] ] ] ) ) );
        $recreate_count = count( get_posts( array_merge( $base_args, [ 'fields' => 'ids', 'meta_query' => [ [ 'key' => 'submission_type', 'value' => 'recreate' ] ] ] ) ) );
        $unread_count   = $this->get_unread_count();

        $export_url   = wp_nonce_url( admin_url( 'admin-post.php?action=thestitch_export_csv' ), 'thestitch_export_csv' );
        $mark_all_url = wp_nonce_url( esc_url_raw( $page_url . '&ts_action=mark_all_read' ), 'ts_mark_all_read' );
        ?>
        <div class="wrap ts-admin-wrap">

        <div class="ts-page-header">
            <div>
                <h1 class="ts-page-title">All Submissions</h1>
                <p class="ts-page-subtitle">View and manage every form submission.</p>
            </div>
            <div class="ts-header-actions">
                <?php if ( $unread_count > 0 ) : ?>
                <a href="<?php echo esc_url( $mark_all_url ); ?>" class="ts-btn ts-btn-secondary"
                   onclick="return confirm('Mark all <?php echo esc_js( $unread_count ); ?> submissions as read?');">Mark All Read</a>
                <?php endif; ?>
                <a href="<?php echo esc_url( $export_url ); ?>" class="ts-btn ts-btn-primary">&#8659; Export CSV</a>
            </div>
        </div>

        <?php if ( $marked_notice ) : ?>
        <div class="notice notice-success is-dismissible"><p>All submissions marked as read.</p></div>
        <?php endif; ?>

        <?php if ( isset( $_GET['ts_notice'] ) ) : ?>
            <?php
            $notice_key   = sanitize_text_field( wp_unslash( $_GET['ts_notice'] ) );
            $notice_count  = isset( $_GET['ts_count'] ) ? absint( $_GET['ts_count'] ) : 0;
            $notice_label  = $notice_count === 1 ? 'submission' : 'submissions';
            ?>
            <?php if ( $notice_key === 'deleted' ) : ?>
                <div class="notice notice-success is-dismissible"><p>Submission deleted.</p></div>
            <?php elseif ( $notice_key === 'bulk_deleted' ) : ?>
                <div class="notice notice-success is-dismissible"><p><?php echo esc_html( $notice_count . ' ' . $notice_label . ' deleted.' ); ?></p></div>
            <?php elseif ( $notice_key === 'none' ) : ?>
                <div class="notice notice-warning is-dismissible"><p>No submissions were selected for deletion.</p></div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Stats Row -->
        <div class="ts-stats-row">
            <div class="ts-stat-card">
                <div class="ts-stat-number"><?php echo esc_html( $total ); ?></div>
                <div class="ts-stat-label">Total</div>
            </div>
            <div class="ts-stat-card">
                <div class="ts-stat-number"><?php echo esc_html( $bridal_count ); ?></div>
                <div class="ts-stat-label">Bridal</div>
            </div>
            <div class="ts-stat-card">
                <div class="ts-stat-number"><?php echo esc_html( $recreate_count ); ?></div>
                <div class="ts-stat-label">Recreate</div>
            </div>
            <div class="ts-stat-card ts-stat-card--accent">
                <div class="ts-stat-number"><?php echo esc_html( $unread_count ); ?></div>
                <div class="ts-stat-label">Unread</div>
            </div>
        </div>

        <!-- Filter + Search -->
        <div class="ts-filter-row">
            <div class="ts-pill-row">
                <a class="ts-sub-tab <?php echo $type_filter === 'all' ? 'ts-sub-tab--active' : ''; ?>"
                   href="<?php echo esc_url( $page_url ); ?>">All (<?php echo esc_html( $total ); ?>)</a>
                <a class="ts-sub-tab <?php echo $type_filter === 'bridal' ? 'ts-sub-tab--active' : ''; ?>"
                   href="<?php echo esc_url( $page_url . '&ts_type=bridal' ); ?>">Bridal (<?php echo esc_html( $bridal_count ); ?>)</a>
                <a class="ts-sub-tab <?php echo $type_filter === 'recreate' ? 'ts-sub-tab--active' : ''; ?>"
                   href="<?php echo esc_url( $page_url . '&ts_type=recreate' ); ?>">Recreate (<?php echo esc_html( $recreate_count ); ?>)</a>
                <a class="ts-sub-tab <?php echo $type_filter === 'new' ? 'ts-sub-tab--active' : ''; ?>"
                   href="<?php echo esc_url( $page_url . '&ts_type=new' ); ?>">Unread (<?php echo esc_html( $unread_count ); ?>)</a>
            </div>
            <form method="GET" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="ts-search-form">
                <input type="hidden" name="page" value="thestitch-submissions">
                <?php if ( $type_filter !== 'all' ) : ?>
                <input type="hidden" name="ts_type" value="<?php echo esc_attr( $type_filter ); ?>">
                <?php endif; ?>
                <input type="text" name="ts_search" class="ts-search-input" placeholder="Search name, email, or referral code…"
                       value="<?php echo esc_attr( $search_query ); ?>">
                <button type="submit" class="ts-btn ts-btn-secondary">Search</button>
                <?php if ( ! empty( $search_query ) ) : ?>
                <a href="<?php echo esc_url( $page_url . ( $type_filter !== 'all' ? '&ts_type=' . esc_attr( $type_filter ) : '' ) ); ?>"
                   class="ts-btn ts-btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Submissions Table -->
        <div class="ts-card ts-submissions-card">
            <?php if ( empty( $submissions ) ) : ?>
            <div class="ts-empty-state">
                <p><?php echo ! empty( $search_query ) ? 'No results for your search.' : 'No submissions found.'; ?></p>
            </div>
            <?php else : ?>
            <form method="post" action="<?php echo esc_url( $current_page_url ); ?>" class="ts-submissions-form">
                <?php wp_nonce_field( 'thestitch_submission_bulk_action', 'thestitch_submission_nonce' ); ?>
                <input type="hidden" name="ts_action" value="bulk_delete">
                <div class="ts-bulk-toolbar">
                    <div class="ts-bulk-toolbar__actions">
                        <span class="ts-bulk-label">Bulk actions</span>
                        <button type="submit" class="ts-btn ts-btn-secondary" onclick="return confirm('Delete the selected submissions permanently? This cannot be undone.');">Delete Selected</button>
                    </div>
                    <div class="ts-bulk-toolbar__note">Select one or more submissions to delete them permanently.</div>
                </div>
                <table class="ts-sub-table">
                    <thead>
                        <tr>
                            <th class="ts-sub-table__check"><input type="checkbox" class="ts-select-all-submissions" aria-label="Select all submissions"></th>
                            <th>Type</th>
                            <th>Name / Email</th>
                            <th>Referral Code</th>
                            <th>Phone</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ( $submissions as $sub ) :
                        $sub_email  = get_post_meta( $sub->ID, 'email', true );
                        $sub_name   = get_post_meta( $sub->ID, 'full_name', true );
                        $sub_referral = get_post_meta( $sub->ID, 'referral_code', true );
                        $sub_phone  = get_post_meta( $sub->ID, 'phone', true );
                        $sub_type   = get_post_meta( $sub->ID, 'submission_type', true );
                        $sub_status = get_post_meta( $sub->ID, 'ts_status', true ) ?: 'new';
                        $view_url   = get_edit_post_link( $sub->ID );
                        $delete_url = wp_nonce_url( add_query_arg( [ 'ts_action' => 'delete_submission', 'ts_id' => $sub->ID ], $current_page_url ), 'thestitch_delete_submission_' . $sub->ID );
                        $is_bridal  = $sub_type === 'bridal';
                        $disp_name  = $sub_name ?: ( $sub_email ?: '—' );
                    ?>
                    <tr class="ts-sub-row <?php echo $sub_status === 'new' ? 'ts-sub-row--new' : ''; ?>">
                        <td class="ts-sub-table__check">
                            <input type="checkbox" name="selected_ids[]" value="<?php echo esc_attr( $sub->ID ); ?>" class="ts-submission-checkbox" aria-label="Select submission <?php echo esc_attr( $sub->ID ); ?>">
                        </td>
                        <td>
                            <span class="ts-type-badge ts-type-badge--<?php echo $is_bridal ? 'bridal' : 'recreate'; ?>">
                                <?php echo $is_bridal ? 'Bridal' : 'Recreate'; ?>
                            </span>
                        </td>
                        <td>
                            <div class="ts-sub-name"><?php echo esc_html( $disp_name ); ?></div>
                            <?php if ( $sub_email && $sub_name ) : ?>
                            <div class="ts-sub-email"><?php echo esc_html( $sub_email ); ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html( $sub_referral ?: '—' ); ?></td>
                        <td><?php echo esc_html( $sub_phone ?: '—' ); ?></td>
                        <td><?php echo esc_html( get_the_date( 'M j, Y', $sub->ID ) ); ?></td>
                        <td>
                            <span class="ts-status-badge ts-status-badge--<?php echo esc_attr( $sub_status ); ?>">
                                <?php echo $sub_status === 'new' ? 'New' : 'Read'; ?>
                            </span>
                        </td>
                        <td>
                            <div class="ts-row-actions">
                                <a href="<?php echo esc_url( $view_url ); ?>" class="ts-action-link">View</a>
                                <?php if ( $sub_email ) : ?>
                                <a href="mailto:<?php echo esc_attr( $sub_email ); ?>" class="ts-action-link">Reply</a>
                                <?php endif; ?>
                                <a href="<?php echo esc_url( $delete_url ); ?>" class="ts-action-link ts-action-link--danger" onclick="return confirm('Delete this submission permanently? This cannot be undone.');">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
            <?php endif; ?>
        </div>

        </div>
        <?php
    }

    // =========================================================
    // CSV Export  (admin-post action, direct download)
    // =========================================================

    public function handle_export_csv() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Access denied' );
        }
        check_admin_referer( 'thestitch_export_csv' );

        $submissions = get_posts( [
            'post_type'      => 'form_submission',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ] );

        $filename = 'thestitch-submissions-' . gmdate( 'Y-m-d' ) . '.csv';
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        $output = fopen( 'php://output', 'w' );
        fputcsv( $output, [
            'ID', 'Type', 'Status', 'Date', 'Full Name', 'Email', 'Phone',
            'Country Code', 'Country ISO', 'Mobile Number',
            'Preferred Date', 'Preferred Time', 'Wedding Date', 'Message',
            'Sizing Type', 'Standard Size', 'Custom Fit Type', 'Measurement Unit',
            'Bust', 'Waist', 'Hips', 'Referral Code', 'Notes',
            'Total Files', 'Outfit Files', 'Reference Files', 'Color Files',
        ] );

        foreach ( $submissions as $sub ) {
            $m = function ( $key ) use ( $sub ) {
                return (string) get_post_meta( $sub->ID, $key, true );
            };
            fputcsv( $output, [
                $sub->ID,
                $m( 'submission_type' ),
                $m( 'ts_status' ) ?: 'new',
                get_the_date( 'Y-m-d H:i:s', $sub->ID ),
                $m( 'full_name' ),
                $m( 'email' ),
                $m( 'phone' ),
                $m( 'country_code' ),
                $m( 'phone_country_iso' ),
                $m( 'mobile_number' ),
                $m( 'preferred_date' ),
                $m( 'preferred_time' ),
                $m( 'wedding_date' ),
                $m( 'message' ),
                $m( 'sizing_type' ),
                $m( 'standard_size' ),
                $m( 'custom_fit_type' ),
                $m( 'measurement_unit' ),
                $m( 'bust' ),
                $m( 'waist' ),
                $m( 'hips' ),
                $m( 'referral_code' ),
                $m( 'notes' ),
                $m( 'upload_count_total' ),
                $m( 'upload_count_outfit' ),
                $m( 'upload_count_reference' ),
                $m( 'upload_count_color' ),
            ] );
        }

        fclose( $output );
        exit;
    }

    // =========================================================
    // AJAX: mark a single submission as read
    // =========================================================

    public function handle_mark_read() {
        check_ajax_referer( 'thestitch_forms_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Unauthorized' );
        }
        $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
        if ( ! $post_id ) {
            wp_send_json_error( 'Invalid ID' );
        }
        update_post_meta( $post_id, 'ts_status', 'read' );
        wp_send_json_success();
    }

    // =========================================================
    // Helper: count unread (new) submissions
    // =========================================================

    private function get_unread_count() {
        return count( get_posts( [
            'post_type'      => 'form_submission',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => [ [ 'key' => 'ts_status', 'value' => 'new' ] ],
        ] ) );
    }

    private function get_admin_page_url($page_slug, $args = []) {
        $base_args = [
            'post_type' => 'form_submission',
            'page'      => $page_slug,
        ];

        return add_query_arg(array_merge($base_args, $args), admin_url('edit.php'));
    }

    private function save_settings_from_request() {
        $colors_raw   = isset($_POST['thestitch_forms_colors']) ? (array) wp_unslash($_POST['thestitch_forms_colors']) : [];
        $branding_raw = isset($_POST['thestitch_forms_branding']) ? (array) wp_unslash($_POST['thestitch_forms_branding']) : [];
        $labels_raw   = isset($_POST['thestitch_forms_labels']) ? (array) wp_unslash($_POST['thestitch_forms_labels']) : [];
        $email_raw    = isset($_POST['thestitch_forms_email']) ? (array) wp_unslash($_POST['thestitch_forms_email']) : [];

        $saved_colors = wp_parse_args($this->sanitize_colors($colors_raw), $this->get_default_colors());
        $saved_branding = wp_parse_args($this->sanitize_branding($branding_raw), $this->get_default_branding());
        $saved_labels = wp_parse_args($this->sanitize_labels($labels_raw), $this->get_default_labels());
        $saved_email = wp_parse_args($this->sanitize_email_settings($email_raw), $this->get_default_email());

        update_option('thestitch_forms_colors', $saved_colors);
        update_option('thestitch_forms_branding', $saved_branding);
        update_option('thestitch_forms_labels', $saved_labels);
        update_option('thestitch_forms_email', $saved_email);
    }

    public function maybe_handle_settings_save() {
        if (!is_admin() || !current_user_can('manage_options')) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $page = isset($_REQUEST['page']) ? sanitize_text_field(wp_unslash($_REQUEST['page'])) : '';
        $action = isset($_POST['ts_action']) ? sanitize_text_field(wp_unslash($_POST['ts_action'])) : '';

        if ($page !== 'thestitch-forms-customize' || $action !== 'save_settings') {
            return;
        }

        check_admin_referer('thestitch_save_settings', 'thestitch_settings_nonce');
        $this->save_settings_from_request();

        wp_safe_redirect($this->get_admin_page_url('thestitch-forms-customize', ['ts_saved' => '1']));
        exit;
    }

    /* ── Save Settings Handler ──────────────────────────────── */
    public function handle_save_settings() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized', 403 );
        }

        check_admin_referer( 'thestitch_save_settings', 'thestitch_settings_nonce' );

        $this->save_settings_from_request();

        wp_safe_redirect($this->get_admin_page_url('thestitch-forms-customize', ['ts_saved' => '1']));
        exit;
    }
}

register_activation_hook( __FILE__, function () {
    if ( ! post_type_exists( 'form_submission' ) ) {
        $plugin = new TheStitch_Forms();
        $plugin->register_cpt_submissions();
    }
    flush_rewrite_rules();
} );

new TheStitch_Forms();
<?php

if (!defined('ABSPATH')) {
    exit;
}

class TheStitch_Recreate_Submission_Adapter extends TheStitch_Payment_Source_Adapter {
    /** @var WP_Post */
    private $post;

    public function __construct($post) {
        parent::__construct($post->ID);
        $this->post = $post;
    }

    public function get_source_type() {
        return 'recreate_submission';
    }

    public function get_order_type_label() {
        return 'Recreate';
    }

    public function get_reference() {
        return 'Recreate #' . $this->post->ID;
    }

    public function get_customer_email() {
        return sanitize_email((string) get_post_meta($this->post->ID, 'email', true));
    }

    public function get_customer_name() {
        $email = $this->get_customer_email();
        return $email !== '' ? $email : 'Valued Customer';
    }

    public function get_referral_code() {
        return (string) get_post_meta($this->post->ID, 'referral_code', true);
    }

    public function get_meta($key, $single = true) {
        return get_post_meta($this->post->ID, $key, $single);
    }

    public function update_meta($key, $value) {
        return update_post_meta($this->post->ID, $key, $value);
    }

    public function get_design_preview_url() {
        $files = get_post_meta($this->post->ID, 'uploaded_files', true);
        if (!is_array($files)) {
            return '';
        }

        foreach ($files as $file) {
            if (($file['field'] ?? '') === 'dream_images' && !empty($file['url'])) {
                return esc_url($file['url']);
            }
        }

        return '';
    }

    public function get_fabric_pattern_url() {
        $files = get_post_meta($this->post->ID, 'uploaded_files', true);
        if (!is_array($files)) {
            return '';
        }

        foreach ($files as $file) {
            if (($file['field'] ?? '') === 'color_images' && !empty($file['url'])) {
                return esc_url($file['url']);
            }
        }

        return '';
    }

    public function get_preview_3d_url() {
        return '';
    }

    public function get_garment_configuration() {
        $notes = (string) get_post_meta($this->post->ID, 'notes', true);
        $details = [];
        if ($notes !== '') {
            $details['Design Notes'] = $notes;
        }
        return $details;
    }

    public function get_sizing_details() {
        $sizing_type = (string) get_post_meta($this->post->ID, 'sizing_type', true);
        $details = ['Sizing Type' => ucfirst($sizing_type ?: '-')];

        if ($sizing_type === 'standard') {
            $details['Standard Size'] = (string) get_post_meta($this->post->ID, 'standard_size', true);
            return $details;
        }

        $fit_type = (string) get_post_meta($this->post->ID, 'custom_fit_type', true);
        $details['Custom Fit Type'] = $fit_type === 'full-fit' ? 'Full Fit' : 'Quick Fit';
        $unit = $this->get_measurement_unit();

        $fields = $fit_type === 'full-fit'
            ? ['height', 'preferred_fit', 'shoulder_width', 'shoulder_to_bust_point', 'bust_point_to_bust_point', 'chest', 'underbust', 'waist', 'shoulder_to_waist_front', 'shoulder_to_waist_back', 'armhole', 'sleeve_length', 'bicep_circumference', 'elbow_circumference', 'wrist_circumference', 'neck_circumference', 'waist_to_hip', 'hip_circumference', 'thigh_circumference', 'knee_circumference', 'calf_circumference', 'waist_to_floor', 'dress_length']
            : ['height', 'preferred_fit', 'bust', 'waist', 'hips', 'sleeve_length', 'dress_length'];

        $labels = [
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

        foreach ($fields as $field) {
            $value = get_post_meta($this->post->ID, $field, true);
            if ($value === '' || $value === null) {
                continue;
            }

            $label = $labels[$field] ?? ucwords(str_replace('_', ' ', $field));
            if ($field === 'preferred_fit') {
                $details[$label] = ucfirst((string) $value);
            } else {
                $details[$label] = (string) $value . ' ' . $unit;
            }
        }

        return $details;
    }

    public function get_measurement_unit() {
        $stored = get_post_meta($this->post->ID, 'measurement_unit', true);
        return TheStitch_Payment_Emails::format_measurement_unit((string) $stored);
    }

    public function get_uploaded_images() {
        $files = get_post_meta($this->post->ID, 'uploaded_files', true);
        if (!is_array($files)) {
            return [];
        }

        $groups = [
            'dream_images' => 'Outfit Images',
            'ref_images' => 'Reference Images',
            'color_images' => 'Color / Pattern Files',
        ];
        $result = [];

        foreach ($groups as $field => $label) {
            $result[$label] = [];
            foreach ($files as $file) {
                if (($file['field'] ?? '') !== $field || empty($file['url'])) {
                    continue;
                }
                $name = !empty($file['original_name']) ? $file['original_name'] : basename((string) parse_url($file['url'], PHP_URL_PATH));
                $result[$label][] = [
                    'url' => esc_url($file['url']),
                    'label' => sanitize_text_field($name),
                ];
            }
            if (empty($result[$label])) {
                unset($result[$label]);
            }
        }

        return $result;
    }
}

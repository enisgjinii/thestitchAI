<?php

if (!defined('ABSPATH')) {
    exit;
}

class TheStitch_WooCommerce_Order_Adapter extends TheStitch_Payment_Source_Adapter {
    /** @var WC_Order */
    private $order;

    public function __construct($order) {
        parent::__construct($order->get_id());
        $this->order = $order;
    }

    public function get_source_type() {
        return 'woocommerce_order';
    }

    public function get_order_type_label() {
        return 'Create';
    }

    public function get_reference() {
        return '#' . $this->order->get_order_number();
    }

    public function get_customer_email() {
        return sanitize_email((string) $this->order->get_billing_email());
    }

    public function get_customer_name() {
        $name = trim($this->order->get_billing_first_name() . ' ' . $this->order->get_billing_last_name());
        return $name !== '' ? $name : 'Valued Customer';
    }

    public function get_referral_code() {
        $private = $this->order->get_meta('_thestitch_referral_code', true);
        if ($private) {
            return (string) $private;
        }

        return (string) $this->order->get_meta('Referral Code', true);
    }

    public function get_meta($key, $single = true) {
        return $this->order->get_meta($key, $single);
    }

    public function update_meta($key, $value) {
        $this->order->update_meta_data($key, $value);
        $this->order->save();
        return true;
    }

    private function get_line_item_meta_map() {
        $map = [];
        foreach ($this->order->get_items('line_item') as $item) {
            foreach ($item->get_meta_data() as $meta) {
                $map[(string) $meta->key] = (string) $meta->value;
            }
        }
        return $map;
    }

    private function get_full_config() {
        $json = $this->order->get_meta('_garment_config_full', true);
        if (!$json) {
            $json = $this->get_line_item_meta_map()['_garment_config_json'] ?? '';
        }

        if (!$json) {
            return [];
        }

        $decoded = json_decode((string) $json, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function get_design_preview_url() {
        return '';
    }

    public function get_fabric_pattern_url() {
        $meta = $this->get_line_item_meta_map();
        if (!empty($meta['Custom Pattern URL'])) {
            return esc_url($meta['Custom Pattern URL']);
        }

        $config = $this->get_full_config();
        $texture = $config['body']['texture'] ?? '';
        if (is_string($texture) && str_starts_with($texture, 'http')) {
            return esc_url($texture);
        }

        if (!empty($meta['Attached Pattern File'])) {
            return esc_url($meta['Attached Pattern File']);
        }

        return '';
    }

    public function get_preview_3d_url() {
        $meta = $this->get_line_item_meta_map();
        if (!empty($meta['🔗 View 3D Design'])) {
            return esc_url($meta['🔗 View 3D Design']);
        }

        return '';
    }

    public function get_garment_configuration() {
        $meta = $this->get_line_item_meta_map();
        $config = $this->get_full_config();
        $details = [];

        $map = [
            'Design Model' => 'Design Model',
            'Variant' => 'Variant',
            'Body Color' => 'Body Color',
            'Body Pattern' => 'Body Pattern',
            'Sleeves Color' => 'Sleeves Color',
            'Neck Trim Color' => 'Neck Trim Color',
            'Pocket Option' => 'Pocket Option',
            'Additional Requirements' => 'Additional Requirements',
        ];

        foreach ($map as $meta_key => $label) {
            if (!empty($meta[$meta_key])) {
                $details[$label] = $meta[$meta_key];
            }
        }

        if (!empty($config['designDetails']) && is_array($config['designDetails'])) {
            foreach (['neck' => 'Neck Style', 'sleeve' => 'Sleeve Style', 'skirt' => 'Skirt Style'] as $key => $label) {
                if (!empty($config['designDetails'][$key])) {
                    $details[$label] = ucwords(str_replace('-', ' ', (string) $config['designDetails'][$key]));
                }
            }
        }

        return $details;
    }

    public function get_sizing_details() {
        $meta = $this->get_line_item_meta_map();
        $details = [];

        if (!empty($meta['Size'])) {
            $details['Size'] = $meta['Size'];
        } elseif (!empty($meta['Measurement Unit'])) {
            $details['Size'] = 'Custom measurements';
        }

        if (!empty($meta['Measurement Unit'])) {
            $details['Measurement Unit'] = $meta['Measurement Unit'];
        }

        foreach ($meta as $key => $value) {
            if (str_starts_with($key, 'Measurement - ') && $value !== '') {
                $details[$key] = $value;
            }
        }

        return $details;
    }

    public function get_measurement_unit() {
        $meta = $this->get_line_item_meta_map();
        $raw = $meta['Measurement Unit'] ?? '';
        if (stripos($raw, 'cm') !== false) {
            return 'cm';
        }
        if (stripos($raw, 'in') !== false) {
            return 'in';
        }

        $config = $this->get_full_config();
        $unit = $config['measurements']['unit'] ?? '';
        return TheStitch_Payment_Emails::format_measurement_unit($unit);
    }

    public function get_uploaded_images() {
        $fabric = $this->get_fabric_pattern_url();
        if (!$fabric) {
            return [];
        }

        return [
            'Fabric / Pattern' => [
                ['url' => $fabric, 'label' => 'Custom fabric or pattern'],
            ],
        ];
    }
}

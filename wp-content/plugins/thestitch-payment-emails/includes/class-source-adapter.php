<?php

if (!defined('ABSPATH')) {
    exit;
}

abstract class TheStitch_Payment_Source_Adapter {
    /** @var int */
    protected $source_id;

    public function __construct($source_id) {
        $this->source_id = absint($source_id);
    }

    abstract public function get_source_type();

    abstract public function get_order_type_label();

    abstract public function get_reference();

    abstract public function get_customer_email();

    abstract public function get_customer_name();

    abstract public function get_referral_code();

    abstract public function get_design_preview_url();

    abstract public function get_fabric_pattern_url();

    abstract public function get_preview_3d_url();

    /** @return array<string, string> */
    abstract public function get_garment_configuration();

    /** @return array<string, string> */
    abstract public function get_sizing_details();

    abstract public function get_measurement_unit();

    /** @return array<string, array<int, array{url:string, label:string}>> */
    abstract public function get_uploaded_images();

    public function get_id() {
        return $this->source_id;
    }

    public function get_meta($key, $single = true) {
        return null;
    }

    public function update_meta($key, $value) {
        return false;
    }

    public function get_payment_meta($key, $default = '') {
        $value = $this->get_meta($key, true);
        return ($value === '' || $value === null) ? $default : $value;
    }

    public function save_payment_fields($fields) {
        foreach ($fields as $key => $value) {
            $this->update_meta($key, $value);
        }
    }
}

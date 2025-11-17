<?php 
class HdKSpUser {
    private static $instance = null;
    private $api;
    private $settings;
    private $customer;

    private function __construct() {
        $this->settings = HdKSpUtilities::get_spektrix_settings();
        $this->api = new HdkSpAPI($this->settings);
        add_action('wp_enqueue_scripts',[$this,'enqueue_scripts']);
        $this->get_current_user();

    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function enqueue_scripts() {
        wp_enqueue_script('customer', plugin_dir_url(__FILE__) . '../js/customer.js', [], HDK_SPEKTRIX_VERSION, true);
        wp_add_inline_script('customer', 'const SPEKTRIXBASEURLCUSTOMER = "https://system.spektrix.com/' .$this->settings['client_code'] . '/api/v3/customer";', 'before');
    }   
    
    public function get_current_user(){
        $cookie = isset($_COOKIE['spektrix_customer']) ? sanitize_text_field($_COOKIE['spektrix_customer']) : '';
        if($cookie){
            $this->customer = json_decode(stripslashes($cookie));
        } else {
            $this->customer = null;
        }
    }

    public function get_customer(){
        return $this->customer;
    }

    public function get_name(){
        if($this->customer && isset($this->customer->name)){
            return $this->customer->name;
        }
        return '';
    }

    public function get_memberships(){
        if($this->customer && isset($this->customer->subscriptions)){
            return $this->customer->subscriptions;
        }
        return [];
    }
    
    public function get_memberships_id(){
        $memberships = $this->get_memberships();
        $ids = [];
        foreach($memberships as $membership){
            $ids[] = $membership->membership->id;
        }
        return $ids;
    }

    public function has_membership(array $membership_ids){
        $memberships = $this->get_memberships();
        foreach($memberships as $membership){
            if(in_array($membership->membership->id, $membership_ids)){
                return true;
            }
        }
        return false;
    }
}
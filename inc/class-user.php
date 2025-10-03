<?php 
class HdKSpUser {
    private static $instance = null;
    private $api;
    private $settings;
    private $customer;

    private function __construct() {
        $this->settings = HdKSpUtilities::get_spektrix_settings();
        $this->api = new HdkSpAPI($this->settings);
        $this->get_current_user();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function get_current_user(){
        $customer = $this->api->get_customer();
        if(is_wp_error($customer)||$customer['0']->key == 'Message'){
            $this->customer = false;
        }
        else{
            $this->customer = $customer;
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
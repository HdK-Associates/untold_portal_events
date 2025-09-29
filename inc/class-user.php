<?php 
class HdKSpUser {
    private $api;
    private $settings;
    private $customer;

    public function __construct() {
        $this->settings = HdKSpUtilities::get_spektrix_settings();
        $this->api = new HdkSpAPI($this->settings);
        $this->get_current_user();
    }
    
    public function get_current_user(){
        $customer = $this->api->get_customer();
        if(is_wp_error($customer)||$customer['response']['code']!=200){
            $this->customer = false;
        }
        $this->customer = $customer;
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

    public function has_membership($membership_id){
        $memberships = $this->get_memberships();
        foreach($memberships as $membership){
            if($membership->membership->id == $membership_id){
                return true;
            }
        }
        return false;
    }
}
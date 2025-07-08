<?php 
class HdKSpForms{
    private $api;

    public function __construct($settings){
        $this->api = new HdkSpAPI($settings);
        add_action( 'admin_post_hdk_settings_form', array($this,'SettingsFormResponse'));
        add_action( 'admin_post_hdk_base_form', array($this,'BaseFormResponse'));
        add_action( 'admin_post_hdk_refresh_form', array($this,'RefreshFormResponse'));
        add_action( 'admin_post_hdk_attribute_form', array($this,'AttributesFormResponse'));
        add_action( 'admin_post_hdk_component_form', array($this,'ComponentsFormResponse'));
    }
    public function SubmitNewsletter($body){
        return $this->api->SpektrixPostAPINewsletter($body);
    }

    public function ProcessFormTags($data){
        if($fname = $data->get_param('FirstName')){
            $fname = sanitize_text_field($fname);
        }
        if($lname = $data->get_param('LastName')){
            $lname = sanitize_text_field($lname);
        }
        if($email = $data->get_param('Email')){
            $email = strtolower(sanitize_email($email));
        }
        if($tags = $data->get_param('Tags')){
            $tags = explode(',',sanitize_text_field($tags));
        }
        $spektrix = new HdKSpektrix();
        return $spektrix->addTagsToUser($fname,$lname,$email,$tags);
    }
    public function SettingsFormResponse(){
        if( isset( $_POST['hdk_settings_nonce'] ) && wp_verify_nonce( $_POST['hdk_settings_nonce'], 'hdk_settings_nonce') ) {

            $client_code = update_option('hdk-client-code', $_POST['hdk-client-code']);
            $stylesheet = update_option('hdk-stylesheet', $_POST['hdk-stylesheet']);
            $subdomain = update_option('hdk-subdomain', $_POST['hdk-subdomain']);
            $cron = update_option('hdk-cron', $_POST['hdk-cron']);
            $retrieve = update_option('hdk-retrieve', $_POST['hdk-retrieve']);

            update_option('hdk-settings-submitted','1');
            if($client_code || $stylesheet || $subdomain || $cron || $retrieve){
                
                $this->FormRedirect($_POST['action'],"Success!");
            }
            else{
                $this->FormRedirect($_POST['action']);
            }
            exit;
        }
        else{
          $this->InvalidNonce();  
        }
    }

    public function BaseFormResponse(){
        if( isset( $_POST['hdk_base_nonce'] ) && wp_verify_nonce( $_POST['hdk_base_nonce'], 'hdk_base_nonce') ) {
            $merch = false;
            $membership = false;
            $funds = false;
            $tags = false;
            $event_cpt = false;
            $merch_cpt = false;
            $activate = false;
            if(isset($_POST['hdk-merch'])){
                $merch = update_option('hdk-merch','1');
            }
            if(isset($_POST['hdk-members'])){
                $membership  = update_option('hdk-members','1');
            }
            if(isset($_POST['hdk-funds'])){
                $funds = update_option('hdk-funds','1');
            }
            if(isset($_POST['hdk-tags'])){
                $tags = update_option('hdk-tags','1');
            }
            if(isset($_POST['hdk-activate'])){
                $activate  = update_option('hdk-initiate-plugin','1');
            }
            if(isset($_POST['hdk-event-cpt'])){
                $event_cpt = update_option('hdk-event-cpt',$_POST['hdk-event-cpt']);
            }
            if(isset($_POST['hdk-merch-cpt'])){
                $merch_cpt = update_option('hdk-merch-cpt',$_POST['hdk-merch-cpt']);
            }
        
            if($merch || $membership || $funds || $tags || $event_cpt || $merch_cpt || $activate){
                $this->FormRedirect($_POST['action'],"Success!");
            }
            else{
                $this->FormRedirect($_POST['action']);
            }
            exit;
        }
        else{
          $this->InvalidNonce();  
        }
    }

    public function RefreshFormResponse(){
        if( isset( $_POST['hdk_refresh_nonce'] ) && wp_verify_nonce( $_POST['hdk_refresh_nonce'], 'hdk_refresh_nonce') ) {
            $populate = new HdKSpPopulate;
            $update = $populate->UpdatePosts();
            $this->FormRedirect($_POST['action'],$update);
            exit;
        }
        else{
          $this->InvalidNonce();  
        }
        
    }

    public function AttributesFormResponse(){
        if( isset( $_POST['hdk_attribute_nonce'] ) && wp_verify_nonce( $_POST['hdk_attribute_nonce'], 'hdk_attribute_nonce') ) {
            $attributes = $_POST['hdk-attributes'];
            $attributes_merch = $_POST['hdk-attributes-merch'];
            $secondary_attributes = $_POST['hdk-secondary-attributes'];
            $secondary_attributes_merch = $_POST['hdk-secondary-attributes-merch'];
            $bool_attributes = $_POST['hdk-bool-attributes'];
            $bool_attributes_merch = $_POST['hdk-bool-attributes-merch'];
            $populate = new HdKSpPopulate;
            $changed_attributes = [];
            $changed_merch_attributes = [];
            if($attributes){
                //$attributes = update_option('hdk-attributes',array_map(function($n){return array('attribute_'.preg_replace('/\s+/', '', $n),$n);},explode(",",$attributes)));
                $attributes = update_option('hdk-attributes',$attributes);
                if($attributes){
                    $changed_attributes[]='hierarchical';
                }
            }
            else{
                $attributes = delete_option('hdk-attributes');
            }
            if($attributes_merch){
                $attributes_merch = update_option('hdk-attributes-merch',$attributes_merch);
                if($attributes_merch){
                    $changed_merch_attributes[]='hierarchical';
                }
            }
            else{
                $attributes_merch = delete_option('hdk-attributes-merch');
            }
            if($secondary_attributes){
                $secondary_attributes = update_option('hdk-secondary-attributes',$secondary_attributes);
                if($secondary_attributes){
                    $changed_attributes[]='secondary';
                }
            }
            else{
                $secondary_attributes = delete_option('hdk-secondary-attributes');
            }
            if($secondary_attributes_merch){
                $secondary_attributes_merch = update_option('hdk-secondary-attributes-merch',$secondary_attributes_merch);
                if($secondary_attributes_merch){
                    $changed_merch_attributes[]='secondary';
                }
            }
            else{
                $secondary_attributes_merch = delete_option('hdk-secondary-attributes-merch',false);
            }

            if($bool_attributes){
                $bool_attributes = update_option('hdk-bool-attributes',$bool_attributes);
                if($bool_attributes){
                    $changed_attributes[]='boolean';
                }
            }
            else{
                $bool_attributes = delete_option('hdk-bool-attributes',false);
            }
            if($bool_attributes_merch){
                $bool_attributes_merch = update_option('hdk-bool-attributes-merch',$bool_attributes_merch);
                if($bool_attributes_merch){
                    $changed_merch_attributes[]='boolean';
                }
            }
            else{
                $bool_attributes_merch = delete_option('hdk-bool-attributes-merch',false);
            }
            /* if( get_option('hdk-attributes-submitted')=='1'){
                if($attributes ||  $secondary_attributes || $bool_attributes  ){
                    $populate->UpdateAttributes('event');
                    update_option('hdk-attributes-changed',$changed_attributes);
                }
                if( $attributes_merch || $secondary_attributes_merch || $bool_attributes_merch ){
                    $populate->UpdateAttributes('merch');
                    update_option('hdk-attributes-merch-changed',$changed_merch_attributes);
                }
            } */

            update_option('hdk-attributes-submitted','1');
            if($attributes || $secondary_attributes || $attributes_merch || $bool_attributes || $secondary_attributes_merch || $bool_attributes_merch){
                
                $this->FormRedirect($_POST['action'],"Success!");
            }
            else{
                $this->FormRedirect($_POST['action']);
            }
            exit;
        }
        else{
          $this->InvalidNonce();  
        }
    }

    public function ComponentsFormResponse(){
        if( isset( $_POST['hdk_component_nonce'] ) && wp_verify_nonce( $_POST['hdk_component_nonce'], 'hdk_component_nonce') ) {
            $pages = $_POST['hdk-iframe-pages'];
            $components = $_POST['hdk-webcomponents'];
            $donate_amounts = $_POST['hdk-donate-amounts'];
            $donate_success = $_POST['hdk-donate-success'];
            $donate_error = $_POST['hdk-donate-error'];
            $members_success = $_POST['hdk-members-success'];
            $members_error = $_POST['hdk-members-error'];
            $merchandise_success = $_POST['hdk-merchandise-success'];
            $merchandise_error = $_POST['hdk-merchandise-error'];
            
            if($pages){
                $pages = update_option('hdk-iframe-pages',$pages);
            } 
            if($components){
                $components = update_option('hdk-webcomponents',$components);
            }
            
            if($donate_amounts){
                $donate_amounts = update_option('hdk-donate-amounts',$donate_amounts);
            }
            
            if($donate_success){
                $donate_success = update_option('hdk-donate-success',$donate_success);
            }
            if($donate_error){
                $donate_error = update_option('hdk-donate-error',$donate_error);
            }
            if($members_success){
                $members_success = update_option('hdk-members-success',$members_success);
            }
            if($members_error){
                $members_error = update_option('hdk-members-error',$members_error);
            }
            if($merchandise_success){
                $merchandisee_success = update_option('hdk-merchandise-success',$merchandise_success);
            }
            if($merchandise_error){
                $merchandise_error = update_option('hdk-merchandise-error',$merchandise_error);
            }

            if($pages || $components ||  $donate_amounts || $donate_success || $donate_error || $members_success || $members_error || $merchandise_success || $merchandise_error ){
                $this->FormRedirect($_POST['action'],"Success!");
            }
            else{
                $this->FormRedirect($_POST['action']);
            }
            exit;
        }
        else{
          $this->InvalidNonce();  
        }    
    }

    public function FormRedirect($name,$message=false,$error=false){
        if($message){
            delete_transient($name.'_error');
            set_transient( $name.'_message', $message, 60 );
        }
        if($error){
            delete_transient( $name.'_message');
            set_transient( $name.'_error', $error, 60 );
        }
        wp_redirect( admin_url('admin.php?page=hdk_spektrix'));
    }


    public function InvalidNonce(){
        return wp_die( __( 'Invalid nonce specified'), __( 'Error' ), array(
            'response' 	=> 403,
            'back_link' => 'admin.php?page=hdk_spektrix',

        ) );
    }

}
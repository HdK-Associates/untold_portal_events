<?php
class HdKSpektrix {

public $tables;
public $settings;
public function __construct() {
    $this->settings = HdKSpUtilities::get_client_codes();
    $build = new HdKSpBuild;
    $this->tables = $build->getTables();
}

/*Functions to get IDs for Merch and Events*/
public function get_event_id($post_id){
    $id_table = $this->tables['relationships'];
    
    return $this->get_ids($post_id,$id_table,'spektrixID');
}

public function get_merch_id($post_id){
    $id_table = $this->tables['relationships_merch'];
    return $this->get_ids($post_id,$id_table,'spektrixID');
}

public function get_event_short_id($post_id){
    $id_table = $this->tables['relationships'];
    return $this->get_ids($post_id,$id_table,'shortID');
}

public function get_merch_short_id($post_id){
    $id_table = $this->tables['relationships_merch'];
    return $this->get_ids($post_id,$id_table,'shortID');
}

/*The actual database call for the public functions*/
private function get_ids($post_id, $table, $id_type){
    global $wpdb;
    $sql ="SELECT ".$id_type." FROM ".$table." WHERE postID='".$post_id."'";
    $data = $wpdb->get_var($sql);
    return $data;
}

/*Get the date functions*/
public function get_date_range($id){
    global $wpdb;
    $events_table = $this->tables['events'];
    $sql ="SELECT instanceDates FROM ".$events_table." WHERE id='".$id."'";
    $data = $wpdb->get_var($sql);
    return $data;
}

/*Returns an array of all event data*/
public function get_event_data($id) {	
    global $wpdb;
    $events_table = $this->tables['events'];
    $sql ="SELECT * FROM ".$events_table." WHERE id='".$id."'";
    $data = $wpdb->get_row($sql, ARRAY_A);
    return $data;
}	

public function get_event_instances($id) {	
    global $wpdb;
    $events_data_table = $this->tables['events_data'];
    $sql ="SELECT * FROM ".$events_data_table." WHERE event_id='".$id."'";
    $data = $wpdb->get_results($sql, ARRAY_A);
    return $data;
}

public function get_seating_plan($instance_id) {	
    global $wpdb;
    $events_data_table = $this->tables['events_data'];
    $sql ="SELECT planID FROM ".$events_data_table." WHERE id='".$instance_id."'";
    $data = $wpdb->get_results($sql, ARRAY_A);
    if(empty($data)){
        return false;
    }
    $planId = $data[0]['planID'];
    $api = new HdkSpAPI($this->settings);
    $seatingPlan = $api->SpektrixGetAPIPlans($planId);
    if($seatingPlan->type == "Group"){
        $name = $seatingPlan->name;
        $image = $seatingPlan->backgroundImageUrl;
        $areas = array_map(function($area){
            return ['name'=>$area->name,'id'=>$area->id];
        },$seatingPlan->areas);
        $seatingPlan = ['name'=>$name,'image'=>$image,'areas'=>$areas];
        return $seatingPlan;
    }
    else{
        return false;
    }
    return $data;
}

public function get_event_price_range($ID) {	
    global $wpdb;
    $events_data_prices_table = $this->tables['events_data_prices'];
    $sql ="SELECT MIN(amount) AS MinPrice, MAX(amount) AS MaxPrice FROM (SELECT amount FROM ".$events_data_prices_table." WHERE EventPriceParentId = '".$ID."' AND NOT ticketType_name = 'Essential Companion' AND NOT ticketType_name = 'Accessible') tmp";
    $data = $wpdb->get_row($sql, ARRAY_A);
    if(fmod($data['MinPrice'],1)==0.0){
        $data['MinPrice']=intval($data['MinPrice']);
    }
    if(fmod($data['MaxPrice'],1)==0.0){
        $data['MaxPrice']=intval($data['MaxPrice']);
    }
    return $data;
}	

public function get_instance_ticket_types($id){
    global $wpdb;
    $events_data_prices_table = $this->tables['events_data_prices'];
    $sql ="SELECT * FROM ".$events_data_prices_table." WHERE EventInstanceID='".$id."'";
    $data = $wpdb->get_results($sql, ARRAY_A);
    return $data;
}

public function populate_price_data($instanceID,$eventID){
    global $wpdb;
    $populate = new HdkSpPopulate;
    $api = new HdkSpAPI($this->settings);
    $eventDataPrices = $api->SpektrixGetAPIEventDataPriceList($instanceID);
    $insert = $populate->insertEventDataPrices($eventID,$instanceID,$eventDataPrices,true); 
    $events_data_prices_table = $this->tables['events_data_prices'];
    $sql ="SELECT * FROM ".$events_data_prices_table." WHERE EventInstanceID='".$instanceID."'";
    $data = $wpdb->get_results($sql, ARRAY_A);
    return $data;
}

public function get_merch_data($id) {	
    global $wpdb;
    $merch_table = $this->tables['merch'];
    $sql ="SELECT * FROM ".$merch_table." WHERE id='".$id."'";
    $data = $wpdb->get_row($sql, ARRAY_A);
    return $data;
}	

public function get_fund_id($name){
    global $wpdb;
    $fund_table = $this->tables['funds'];
    $sql ="SELECT id FROM ".$fund_table." WHERE name='".$name."'";
    $data = $wpdb->get_var($sql);
    return $data;
}

public function get_funds(){
    global $wpdb;
    $fund_table = $this->tables['funds'];
    $sql ="SELECT id, name FROM ".$fund_table;
    $data = $wpdb->get_results($sql, ARRAY_A);
    return $data;
}

public function get_newsletter_tags(){
    global $wpdb;
    $tag_types_table = $this->tables['tag_types'];
    $tags_table = $this->tables['tags'];
    $sql ="SELECT id, description, name FROM ".$tag_types_table;
    $tag_types = $wpdb->get_results($sql, ARRAY_A);
    
    foreach($tag_types as $key=>$tag_type){
        $sql_tags = "SELECT id,name FROM ".$tags_table." WHERE tag_type = '".$tag_type['id']."'";
        $tags = $wpdb->get_results($sql_tags, ARRAY_A);
        $tag_types[$key]['tags']=$tags;
    }
    return $tag_types;
}

public function get_newsletter_form_with_tags(){
    $tags=[];
    if(get_option('hdk-tags')){
        $tags = $this->get_newsletter_tags();
    }
    return HdKSpUtilities::getNewsletterFormWithTags($tags);
}

public function get_newsletter_form_with_tags_by_page(){
    $tags=[];
    if(get_option('hdk-tags')){
        $tags = $this->get_newsletter_tags();
    }
    return HdKSpUtilities::getNewsletterFormWithTagsByPage($tags);
}

public function get_newsletter_form(){
    return HdKSpUtilities::getNewsletterForm();
}

public function get_tag_form(){
    if(!isset($_COOKIE['SpCustomerID'])){
        return;
    }
    $user_id = $_COOKIE['SpCustomerID'];
    $tags=[];
    if(get_option('hdk-tags')){
        $tags = $this->get_newsletter_tags();
    }
    return HdKSpUtilities::getTagForm($tags,$user_id);
}

public function addTagsToUser($fname,$lname,$email,$tags,$server_side=false){
    $api = new HdkSpAPI($this->settings);
    return $api->SpektrixAPIPostUserWithTags($fname,$lname,$email,$tags,$server_side);
}

public function post_tag_handler(){
    if(!isset($_POST['user_id'])){return false;}
    $api = new HdkSpAPI($this->settings);
    $responses = [];
    foreach($_POST['tags'] as $tag){
        $body = json_encode(['id'=>$tag]);
        $responses[] = $api->SpektrixAPIAddTagsByUserID($_POST['user_id'],$body);
    }
    foreach($responses as $response){
        if($response['response']['code']==200){
            return true;
        }
    }
}

public function get_memberships() {	
    global $wpdb;
    $members_data_table = $this->tables['members'];
    $sql ="SELECT * FROM ".$members_data_table;
    $data = $wpdb->get_results($sql, ARRAY_A);
    return $data;
}

/*Need to put the name of the fund directly here. Can be changed if we have more than one relevent fund*/
public function get_donate_component(){
    $fund_id = $this->get_fund_id('Support Us');
    return HdKSpUtilities::get_donate_component($this->settings,$fund_id);
}

public function get_members_component(){
    $memberships = $this->get_memberships();
    return HdKSpUtilities::get_members_component($this->settings,$memberships);
}
public function get_basket_summary_component(){
    return HdKSpUtilities::get_basket_summary_component($this->settings);
}
public function get_basket_summary_event_component(){
    return HdKSpUtilities::get_basket_summary_event_component($this->settings);
}

public function get_login_status_component(){
    return HdKSpUtilities::get_login_status_component($this->settings);
}

public function update_event_relations($post_id,$spektrixID){
    global $wpdb;
    $shortID = preg_split("/[a-zA-Z]/",$spektrixID,2);
    $shortID = $shortID[0];
    $relations = $this->tables['relationships'];
    $wpdb->insert($relations,array('spektrixID'=>$spektrixID,'shortID'=>$shortID,'postID'=>$post_id,'updated'=>date('Y-m-d')));
}
/*

public function SpektrixGetEventShortID($ID) {	
    global $wpdb;
    $events_table = $this->tables['events'];
    $sql ="SELECT * FROM ".$events_table." WHERE id REGEXP '^".$ID."'";
    $Data = $wpdb->get_row($sql, ARRAY_A);
    return $Data;
}	

public function SpektrixAddMerchToBasket($merch_array){
    $json=json_encode($merch_array);
    $Endpoint 	= 'https://'.$this->subdomain .'/'.$this->client_code.'/api/v3/basket/merchandise';		
    $ch = curl_init($Endpoint);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

public function SpektrixGetJavascriptCode() {
?>
    <script type='text/javascript' src='https://'.$this->subdomain .'/'.$this->client_code .'/website/scripts/resizeiframe.js'></script>
    <script src="https://webcomponents.spektrix.com/stable/webcomponents-loader.js"></script>
    <script src="https://webcomponents.spektrix.com/stable/spektrix-component-loader.js" data-components="spektrix-donate,spektrix-merchandise,spektrix-memberships,spektrix-donate" async></script>
    <?php
}
*/


}
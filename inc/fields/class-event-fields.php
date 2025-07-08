<?php 
class APEventFields{
    private $post_id;
    private $fields;
    public function __construct($post_id){
        $this->post_id = $post_id;
        $this->set_fields();
    }

    public function set_fields(){
        $this->fields = [];
        $imgstring = '';
        if($img = $this->get_acf('header_image')){
            $imgstring = 'style="background-image:url(\''.wp_get_attachment_image_url($img,'full').'\');"';
        }
        elseif($img = get_the_post_thumbnail_url($this->post_id,'full')){
            $imgstring = 'style="background-image:url(\''.$img.'\');"';
        }
        $this->fields['bg_image'] = $imgstring;

        $this->fields['info_text'] = $this->get_acf('info_button_text')??'INFO & TEXT';

        $this->fields['title'] = $this->get_acf('custom_heading')??get_the_title($this->post_id);

        $this->fields['start_date'] = wp_date('Y-m-d\TG:i:s', strtotime($this->get_acf('start_date')));
        if($end_date = $this->get_acf('end_date')){
            $end_date_string = $end_date;
        }
        else{
            $end_date_string = '';
        }
        $this->$fields['end_date'] = $end_date_string;
        $location = '';
        if($location = $this->get_acf('location')){
            $location_string = $location->name;
        }
        elseif($location = get_post_meta($this->post_id,'wpcf-ap-event-location',true)){
            $location_string = $location;
        }
        $this->fields['location'] = $location_string;

        $this->fields['alternate-text'] = $this->get_acf('wpcf-alternate-text');

        $this->fields['content'] = $this->get_acf('content');
    }
    
    public function get($field_name){
        if(isset($this->fields[$field_name]) && $this->fields[$field_name]){
            return $this->fields[$field_name];
        }
        elseif($field_name=='title'){
            return get_the_title($this->post_id);
        }
    }

    public function get_acf($field_name){
        return get_field($field_name, $this->post_id);
    }
}
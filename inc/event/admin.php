<?php
class HdKSpEventAdmin{
    private $event;
    private $Spektrix;
    public function  __construct(){
        $this->Spektrix = new HdKSpektrix;
        add_action( 'add_meta_boxes', [$this,'add_event_meta_boxes'],10,1);
        add_action( 'save_post_event',[$this,'save_event_meta'],10,3);
    }
    

    public function add_event_meta_boxes($post_type){
        if($post_type=='event'){
            global $post;
            if(get_post_meta($post->ID,'spektrix_ticketing',true)==1){
                $this->event = new SpektrixEvent($post->ID);
                add_meta_box(
                    'spektrix_event_meta_box',
                    __('Spektrix Event Details','untold'),
                    array($this,'get_event_meta'),
                    'event',
                    'normal',
                    'high'
                );
                add_meta_box(
                    'events_box', 
                    'Spektrix Event Dates and Times', 
                    [$this, 'get_event_dates_times'], 
                    'event', 
                    'normal', 
                    'default',
                    ['event'=>$this->event]
                );
            }
        }
    }

    public function get_event_meta($post){
        $spektrix_id = get_post_meta($post->ID, 'spektrix_id', true);
        $prices = $this->Spektrix->get_event_price_range($spektrix_id);
        $prices_str = '';
        if(0==$prices['MinPrice']){
            $prices['MinPrice']='Free';
        }
        else{
            $prices['MinPrice']='£'.$prices['MinPrice'];
        }
        if(0==$prices['MaxPrice']){
            $prices['MaxPrice']='Free';
        }
        else{
            $prices['MaxPrice']='£'.$prices['MaxPrice'];
        }
        if($prices['MinPrice']==$prices['MaxPrice']){
            $prices_str = $prices['MinPrice'];
        }
        else{
            $prices_str = 'Tickets from: '.$prices['MinPrice']. ' - ' .$prices['MaxPrice'];
        }
       include HDK_SPEKTRIX_DIR . 'inc/partials/event-meta-box.php';
    }

    public function get_event_dates_times($post,$metabox){
        $event = $metabox['args']['event'];
        /* wp_add_inline_script( 'tickets-scripts', 'const EVENTSNONCE = ' . json_encode( array(
            'nonce' => wp_create_nonce( 'wp_rest' )
        ) ), 'before' ); */
        echo $event->get_event_dates_times();
    }

    public function save_event_meta($post_id, $post, $update){
        if(!get_field('spektrix_ticketing',$post_id)) return;
        $event = new SpektrixEvent($post_id);
        if(!$event->active_event) return;
        $instances = $event->instances;
        foreach($instances as $instance){
            $id = $instance['id'];
            $start = date('F j, Y g:i a',strtotime($instance['start']));
            if(isset($_POST['gateopen_'.$id])){
                $current_go_meta = get_post_meta($post_id,'gateopen_'.$id,true);
                if($current_go_meta){
                    update_post_meta($post_id,'gateopen_'.$id,date('Y-m-d\TH:i:s',strtotime($_POST['gateopen_'.$id])));
                }
                elseif($_POST['gateopen_'.$id]!=$start){
                    add_post_meta($post_id,'gateopen_'.$id,date('Y-m-d\TH:i:s',strtotime($_POST['gateopen_'.$id])));
                }
            }
            if(isset($_POST['end_'.$id])){
                $current_end_meta = get_post_meta($post_id,'end_'.$id,true);
                if($current_end_meta){
                    update_post_meta($post_id,'end_'.$id,date('Y-m-d\TH:i:s',strtotime($_POST['end_'.$id])));
                }
                elseif($_POST['end_'.$id]!=$start){
                    add_post_meta($post_id,'end_'.$id,date('Y-m-d\TH:i:s',strtotime($_POST['end_'.$id])));
                }
            }
            if(isset($_POST['addinfo_'.$id]) && $_POST['addinfo_'.$id]){
                if($_POST['addinfo_'.$id]=='none'){
                    delete_post_meta($post_id,'addinfo_'.$id);
                }
                else{
                    update_post_meta($post_id,'addinfo_'.$id,$_POST['addinfo_'.$id]);
                }
            }
            else{
                delete_post_meta($post_id,'addinfo_'.$id);
            }
        }

    }
}
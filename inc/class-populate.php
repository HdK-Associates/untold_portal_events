<?php 
class HdKSpPopulate{
    private $wpdb;
    private array $settings;
    private HdkSpAPI $api;
    private HdkSpBuild $builder;
    private array $tables;
    private array $temp_tables;

    public function __construct($settings=false){
        global $wpdb;
        if(!$settings){
            $settings = HdkSpUtilities::get_spektrix_settings();
        }
        $this->settings = $settings;
        $this->api = new HdkSpAPI($this->settings);
        $this->builder = new HdkSpBuild();
        $this->tables = $this->builder->getTables();
        $this->temp_tables = $this->builder->getTempTables();
        $this->wpdb = $wpdb;
    }

    //Called from the cron or the CLI
    public function UpdateTables() {
        if(false === get_transient('spektrix_update_running')){
            set_transient('spektrix_update_running',true,3600);
        }
        else{
            $message='This script is already running';
            WP_CLI::line( 'This script is already running');
            delete_transient('spektrix_refresh_message');
            set_transient('spektrix_refresh_error',$message,180);
            return;
        } 
        $error = [];
        $count = 0;
        $events = $this->getEvents();
        if(!$events['error']){
            if($this->settings['is_members_active']){
                $members = $this->getMembers();
                if(!$members){
                    $error[] = 'Error on getting Member Data';
                }
            }
            if($this->settings['is_funds_active']){
                $funds = $this->getFunds();
                if(!$funds){
                    $error[] = 'Error on getting Fund Data';
                }
            }
            if($this->settings['is_tags_active']){
                $tags = $this->getTags();
                if(!$tags){
                    $error[] = 'Error on getting Tag Data';
                }
            }
        }
        else{
            $error = $events['error'];
        }
        if(count($error)==0){
            $message = $count .' records updated.';
            //Commenting this out as we often don't have events for this site
            /* counts = $this->ValidateTempTablesCount();
            if($counts['wp_spektrix_events_temp']==0 || $counts['wp_spektrix_events_data_temp']==0 || $counts['wp_spektrix_events_data_prices_temp']==0){
                $this->DropTempTables();
                delete_transient('spektrix_refresh_message');
                set_transient('spektrix_refresh_error',$message,180);
                delete_transient('spektrix_update_running');
                wp_mail('chad@wearehdk.com','Spektrix Cron Errors',wp_date('YmdH:i:s').' '. $message.' ' .json_encode($counts));
                $this->LogError(json_encode($counts));
            }
            else{ */
                WP_CLI::line( 'Updating all Tables');
                $this->LogError($message);
                $this->DropBackupTables();
                $this->DropTables();
                $this->RenameTempTables();
                WP_CLI::line( 'Updating Posts');
                $this->UpdatePosts();
                delete_transient('spektrix_refresh_error');
                set_transient('spektrix_refresh_message',$message,180);
                delete_transient('spektrix_update_running');
                wp_mail('chad@wearehdk.com','Spektrix Cron Successful',wp_date('YmdH:i:s'));
           // }
         }
        else{
            $message='Something went wrong while inserting the records';
            WP_CLI::line( 'Something went wrong while inserting the records');
            $this->DropTempTables();
            delete_transient('spektrix_refresh_message');
            set_transient('spektrix_refresh_error',$message,180);
           // wp_redirect( '/wp-admin/admin.php?page=hdk_spektrix', 302 );
            delete_transient('spektrix_update_running');
            $error_message = '';
            foreach($error as $e){
                $error_message .= implode('; ',$e);
            }
            wp_mail('chad@wearehdk.com','Spektrix Cron Errors',wp_date('YmdH:i:s').' '. $message.' ' .$error_message);
            $this->LogError($error_message);
        } 
    }

    //Called via rest from the UI
    public function UpdateTablesEvents(){
        /* if(false === get_transient('spektrix_update_running')){
            set_transient('spektrix_update_running',true,3600);
        }
        else{
            $message='This script is already running';
            delete_transient('spektrix_refresh_message');
            set_transient('spektrix_refresh_error',$message,180);
            return ['error'=>$message];
        }   */
        $count =0 ;
        $error=[];
        $this->DropTempTables();
        $this->createTempTables();
        //Calling all the small tables first
        if($this->settings['is_members_active']){
            $members = $this->getMembers();
            if(!$members){
                $error[] = 'Error on getting Member Data';
            }
        }
        if($this->settings['is_funds_active']){
            $funds = $this->getFunds();
            if(!$funds){
                $error[] = 'Error on getting Fund Data';
            }
        }
        if($this->settings['is_tags_active']){
            $tags = $this->getTags();
            if(!$tags){
                $error[] = 'Error on getting Tag Data';
            }
        }	
        if(count($error)==0){
            $events = $this->getEvents(false);
            if($events['error']){
                $error = $events['error'];
            }
            else{
                $count = $events['count'];
            }
        }
        if($error){
            $this->DropTempTables();
            $this->LogError(implode('; ',$error));
        }
        //When updating via rest we only return the count of events which then triggers a call to update events in chunks so we don't timeout the server. See UpdateTablesEventInstance and spektrix.js
        return $count;
    }

    public function getEvents($get_data = true){
        $allEvents = $this->api->SpektrixGetAPIEvents();
        if(!$allEvents){
            if ( defined( 'WP_CLI' ) && WP_CLI ) {
                WP_CLI::line( 'Something is wrong with your client code or subdomain' );
            }
            throw new Exception('Please make sure you have entered a client code and a subdomain');
        }	
        $count = 0;
        $error=[];
        $this->DropTempTables();
        $this->createTempTables();
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            WP_CLI::line( 'Processing '. count($allEvents) .' events' );
        }
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            WP_CLI::line( 'Processing '. count($allEvents) .' events' );
        }
        foreach($allEvents as $i=>$event):
            if ( defined( 'WP_CLI' ) && WP_CLI ) {
                WP_CLI::line( $i.': Processing '. $event->name );
            }
            if ( defined( 'WP_CLI' ) && WP_CLI ) {
                WP_CLI::line( $i.': Processing '. $event->name );
            }
            set_time_limit(0);
            ignore_user_abort(true);
            if($event->attribute_EVENTTYPE!=='Regimental Only'){
                continue;
            }
            $insert = $this->insertEvent($event); 
            if(isset($insert['skipped'])){
                if ( defined( 'WP_CLI' ) && WP_CLI ) {
                    WP_CLI::line( 'Skipped '. $event->name .' : '. $insert['skipped'] );
                }
                continue;
            }
            if(isset($insert['error'])){
                $error[] = $insert['error'];
                if ( defined( 'WP_CLI' ) && WP_CLI ) {
                    WP_CLI::line( 'Error on Insert Event for '. $event->name );
                }
                break;
            }
            else{
                $count++;
            }
            if($get_data){
                $eventData = $this->getEventData($event->id);
                if(isset($eventData['error'])){
                    $error[] = $eventData['error'];
                    if ( defined( 'WP_CLI' ) && WP_CLI ) {
                        WP_CLI::line( 'Error on Insert Event Data for '. $event->name );
                    }
                    break;
                }
                if ( defined( 'WP_CLI' ) && WP_CLI ) {
                    WP_CLI::line( 'Processed '. $event->name);
                }
            }
        endforeach;

        return ['count'=>$count,'error'=>$error];
    }

    public function getEventData(string $id,$single=false){
        $available = 0;
        $error = [];
        if($this->settings['refresh_months']){
            $eventData = $this->api->SpektrixGetAPIEventDataMonth($id,$this->settings['refresh_months']);
            if(!$eventData){
                $eventData = $this->api->SpektrixGetAPIEventData($id);
            }
        }
        else{
            $eventData = $this->api->SpektrixGetAPIEventData($id);
        }
        if($eventData===null){
            $error[] = 'Error on getting Event data for '.$id;
            return ['error'=>$error];
        }
        $countEventItem = 0;
        foreach($eventData as $eventItem):
            if ( defined( 'WP_CLI' ) && WP_CLI ) {
                WP_CLI::line( 'Processing '. $id .' Instance: '. $eventItem->id);
            }
            $eventItemStatus=false;
            $eventItemStatus = $this->api->SpektrixGetAPIEventDataStatus($eventItem->id);
            if($eventItemStatus===null){
                //try again
                if ( defined( 'WP_CLI' ) && WP_CLI ) {
                    WP_CLI::line( 'Getting status again for: '. $eventItem->id);
                }
                $eventItemStatus = $this->api->SpektrixGetAPIEventDataStatus($eventItem->id);
            }
            if($eventItemStatus===null){
                $error[] = 'Error on getting Event Status data for '.$eventItem->id;
                break;
            }
            $insert = $this->insertEventData($id,$eventItem,$eventItemStatus,$single);
            if(isset($insert['error'])){
                $error[] = 'Error on Insert Event Data for ID ' . $id . ' and Instance ' .$eventItem->id . ' : '. implode(' | ',$insert['error']);
                if ( defined( 'WP_CLI' ) && WP_CLI ) {
                    WP_CLI::line( 'Error on Insert Event Data for ID ' . $id . ' and Instance ' .$eventItem->id);
                }
                break;
            }
            $available+=$eventItemStatus->available;
            if($countEventItem<5){
                if ( defined( 'WP_CLI' ) && WP_CLI ) {
                    WP_CLI::line( 'Processing '. $id .' Instance: '. $eventItem->id .' pricelists');
                }
                $eventDataPrices = $this->api->SpektrixGetAPIEventDataPriceList($eventItem->id);
                if($eventDataPrices===null){
                    $error[] = 'Error on getting Event Prices data for '.$eventItem->id;
                    break;
                }
                //If there are no prices, then we don't need to insert them or throw an error
                $insert = $this->insertEventDataPrices($id,$eventItem->id,$eventDataPrices,$single);
            }
            $countEventItem++;
        endforeach;
        if($error){
            return ['error'=>$error];
        }
        if(!$single){
            $this->wpdb->update($this->temp_tables['events'], 
                array(
                    'totalAvailable'=>$available
                ),
                array('id'=> $id)
            );
        }
    }

    public function getMembers(){
        $members = $this->api->SpektrixGetAPIMembers();
        if($members===null || isset($members->errorCode)){
            return false;
        }
        foreach($members as $item):
            $insert = $this->insertMembers($item);
            if(!$insert){
                return false;
            }
        endforeach;	
        return true;
    }

    public function getFunds(){
        $funds = $this->api->SpektrixGetAPIFunds();
        if($funds===null || isset($funds->errorCode)){
            return false;
        }
        foreach($funds as $item):
            $insert = $this->insertFunds($item);
        
            if(!$insert){
                return false;
            }
        endforeach;	
        return true;
    }

    public function getTags(){
        $tags = $this->api->SpektrixGetAPITags();
        if($tags===null || isset($tags->errorCode)){
            return false;
        }
        foreach($tags as $item):
            $insertTypes = $this->insertTagTypes($item);
            if(!$insertTypes){
                return false;
            }
            foreach($item->tags as $tag):
                $insertTags = $this->insertTags($item->id,$tag);
                if(!$insertTags){
                    return false;
                }
            endforeach;
        endforeach;	
        return true;
    }

    public function UpdateTablesEventInstance($id,$chunk=0,$single = false){
        $eventsInstances=[];
        $chunkingFlag = false;
        $error=[];
        $count = 0;
        if(!$single){
            $events_table = $this->temp_tables['events'];
        }
        else{
            $events_table = $this->tables['events'];
        }
        $sql ="SELECT id,attribute_GetLiveInstances FROM ".$events_table." WHERE EventId='".$id."'";
        $event_details = $this->wpdb->get_results($sql,ARRAY_A);

        $event = $event_details[0]['id'];
        $available = 0;
        if($chunk!==0 && get_transient('spektrixEvent_'.$id)){
            $eventData = get_transient('spektrixEvent_'.$id);
        }
        else{
            $offset = $this->settings['refresh_months']?:'+ 180 days';
            $eventData = $this->api->SpektrixGetAPIEventData($event,$offset);
            if($eventData===null){
                $error[] = 'Error on getting Event Data';
                return;
            }
            set_transient('spektrixEvent_'.$id,$eventData,600);
        }
        $totalInstances = count($eventData);
        if($totalInstances>30){
            if($chunk + 30 < $totalInstances){
                $chunkingFlag = true;
                $totalInstances = $chunk + 30;
            }
        }
       
       

        for($chunk;$chunk<$totalInstances;$chunk++):
            $eventItem = $eventData[$chunk];
            $eventItemStatus = false;
            //if($event_details[0]['attribute_GetLiveInstances']||$single){
                $eventItemStatus = $this->api->SpektrixGetAPIEventDataStatus($eventItem->id);
            //}
            $insert = $this->insertEventData($event,$eventItem,$eventItemStatus,$single);
            if(!$insert){
                $error[] = 'Error on Insert Event Data for ID' . $event . 'and Instance ' .$eventItem->id;
            }
            $count++;
            $available+=$eventItemStatus->available;
            if($chunk<5){
                $eventDataPrices = $this->api->SpektrixGetAPIEventDataPriceList($eventItem->id);
                if($eventDataPrices){
                    $insert = $this->insertEventDataPrices($event,$eventItem->id,$eventDataPrices,$single);
                    if(!$insert){
                        $error[] = 'Error on Insert Event Data Prices for ID' . $event . 'and Instance ' .$eventItem->id;
                    }
                }
            }
        endfor;
        $this->wpdb->update($events_table, 
            array(
                'totalAvailable'=>$available
            ),
            array('id'=> $event)
        );
        if(count($error)==0){
            return array($id,$count,$chunk,$chunkingFlag);
        }
        else{
            $this->LogError(implode('; ',$error));
            return array($id,$count,$chunk,$chunkingFlag);
        }
    }

    public function ImportEvent($id = false, $eventID = false){
        //Checking if this is a full ID or shortID
        if($eventID && strlen($eventID)<10){
            $eventID = $this->getEventIDfromShortID($eventID);
            if(is_array($eventID)){
                return $eventID;
            }
        }
        $events = $this->tables['events'];
        $instances = $this->tables['events_data'];
        $price = $this->tables['events_data_prices'];
        $error=[];

        $event_exists = $this->wpdb->get_var("SELECT COUNT(*) FROM ".$events." WHERE id='".$eventID."'");
        if($event_exists){
            if ( defined( 'WP_CLI' ) && WP_CLI ) {
                WP_CLI::line( 'Aborting import of '. $eventID . 'it exists in DB');
            } 
            return ['error'=>'Event already exists in the database']; 
        }
        /*deleting instances and prices */
        $this->wpdb->delete($instances, array('EventParentID'=>$eventID));
        $this->wpdb->delete($price, array('EventPriceParentID'=>$eventID));
        $this->wpdb->delete($events, array('id'=>$eventID));

        /*hitting API for event data and updates db row*/
        $event = $this->api->SpektrixGetAPIEvent($eventID);
       
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            WP_CLI::line( 'Importing event for '. $eventID);
        } 
        if($this->settings['museum_mode']!=='regimental' && $event->attribute_EVENTTYPE=='Regimental Only'){
            return;
        }
        $insert = $this->insertEvent($event, true); 
        $eventData = $this->getEventData($event->id,true);
        if(isset($eventData['error'])){
            $error[] = $eventData['error'];
            if ( defined( 'WP_CLI' ) && WP_CLI ) {
                WP_CLI::line( 'Error on Insert Event Data for '. $event->name );
            }
            return ['error'=>'Error on Insert Event Data for '. $event->name];
        }

        $tax = $this->generateTaxData($event,$id);
        $meta = $this->generateMetaData($event,$id);
        $new_id = $this->createPosts($this->tables['relationships'], $event, $tax, 'event',$meta, $id);
        
        /*returning array with total count(always 1 for a single event) and db id, for use in UpdateTablesEventInstance*/
        $sql_get_event_db_id = "SELECT EventID FROM ".$events." WHERE id='".$eventID."'";
        
        $eventDBID = $this->wpdb->get_var($sql_get_event_db_id);
        
        return array(1,$eventDBID);
    }

    public function insertEvent(object $event, $importToLive = false){
            
            $description = '';
            /*Checking if there is actually a description. Spektrix returns empty div tags rather than null*/
            if($event->htmlDescription){
                $str = strip_tags($event->htmlDescription);
                $str = preg_replace('/\s+/', '', $str);
                if($str!==''){
                    $description = $event->htmlDescription;
                }
            }
            $validate = $this->validateEvents($event);
            if(isset($validate['error']) && $validate['error']){
                return ['error'=>'Error validating '.$event->id.': '.implode(" | ",$validate['error'])];
            }
            if($event->attribute_EVENTTYPE!=='Regimental'){
                return ['skipped'=>'Event Type is not Regimental'];
            }
            $eventsData = [
                "id"             				=>$event->id,
                "webEventId"             		=>$event->webEventId,
                "description"             		=>$description,
                "duration"             			=>$event->duration,
                "imageUrl"             			=>$event->imageUrl,
                "isOnSale"             			=>$event->isOnSale,
                "name"             				=>$event->name,
                "instanceDates"             	=>$event->instanceDates,
                "firstInstanceDateTime"         =>$event->firstInstanceDateTime,
                "lastInstanceDateTime"          =>$event->lastInstanceDateTime,
                "attribute_AGERANGE"            =>$event->attribute_AGERANGE,
                "attribute_EVENTSUBJECT"       =>$event->attribute_EVENTSUBJECT,
                "attribute_EVENTTYPE"          =>$event->attribute_EVENTTYPE,
                "attribute_ONLINE"             =>$event->attribute_ONLINE,
                "attribute_TRIGGERWARNINGTAG"  =>$event->attribute_TRIGGERWARNINGTAG,
                "attribute_VENUE"              =>$event->attribute_VENUE,
                "attribute_FAMILYFRIENDLY"     =>$event->attribute_FAMILYFRIENDLY,
                "attribute_FREEFORMEMBERS"     =>$event->attribute_FREEFORMEMBERS,
                "attribute_SCHOOLSKEYSTAGE"    =>$event->attribute_SCHOOLSKEYSTAGE,
                "attribute_KS1TOPICS"          =>$event->attribute_KS1TOPICS,
                "attribute_KS3TOPICS"          =>$event->attribute_KS3TOPICS,
                "updated"						=> date('Y-m-d h:i:s')
            ];
            $insert = $this->wpdb->insert($this->temp_tables['events'], $eventsData);
            if(!$insert){
                return ['error'=>'Error on inserting ID' . $event->id .' into the database' ];
            }
            return $insert;
    }

    public function validateEvents(object $event){
        $error = [];
        if(!$event->id){
            $error[] = 'Event ID is missing.';
        }
        if(!$event->name){
            $error[] = 'Event Name is missing.';
        }
        if(!$event->firstInstanceDateTime){
            $error[] = 'Event First Instance Date Time is missing.';
        }
        if(!$event->lastInstanceDateTime){
            $error[] = 'Event Last Instance Date Time is missing.';
        }
        return ['error'=>$error];
    }

    public function insertEventData(string $eventID,object $eventItem,$eventItemStatus,$single = false){
        if(isset($eventItem->errorCode)){
            return ['error'=>'Error on getting Event Data for '.$eventItem->id .' : '. $eventItem->message];
        }
        $validate = $this->validateEventsData($eventItem);
        if(isset($validate['error']) && $validate['error']){
            return ['error'=>'Error validating '.$eventItem->id.': '.implode(" | ",$validate['error'])];
        }
        $eventsInstances = [
            "EventParentID"             		=>$eventID,
            "isOnSale"             				=>$eventItem->isOnSale,				
            "planId"             				=>$eventItem->planId,
            "priceList_id"             			=>$eventItem->priceList->id,
            "event_id"             				=>$eventItem->event->id,
            "start"             				=>$eventItem->start,
            "startUtc"             				=>$eventItem->startUtc,
            "startSellingAtWeb"             	=>$eventItem->startSellingAtWeb,
            "startSellingAtWebUtc"             	=>$eventItem->startSellingAtWebUtc,
            "stopSellingAtWeb"             		=>$eventItem->stopSellingAtWeb,
            "stopSellingAtWebUtc"             	=>$eventItem->stopSellingAtWebUtc,
            "cancelled"             			=>$eventItem->cancelled,
            "id"             					=>$eventItem->id,
            "available"                         =>$eventItemStatus?$eventItemStatus->available:NULL,
        ];
        if(!$single){
            $events_data_table = $this->temp_tables['events_data'];
        }
        else{
            $events_data_table = $this->tables['events_data'];
        }
        $insert = $this->wpdb->replace($events_data_table, $eventsInstances);
        if(!$insert){
            return ['error'=>'Error on inserting ID' . $eventItem->id .' into the database' ];
        }
        return $insert;
    }

    public function validateEventsData(object $eventItem){
        $error = [];
        if(!$eventItem->id){
            $error[] = 'Event Instance ID is missing.';
        }
        if(!$eventItem->start){
            $error[] = 'Event Instance Start Date Time is missing.';
        }
        if(!$eventItem->priceList->id){
            $error[] = 'Event Instance Price List ID is missing.';
        }
        if(!$eventItem->event->id){
            $error[] = 'Event Instance Event ID is missing.';
        }

        return ['error'=>$error];
    }

    public function insertEventDataPrices(string $event_id,string $eventItem_id,object $eventDataPrices,$single=false){
        if(isset($eventDataPrices->errorCode)){
            return false;
        }
        
        if(!$eventDataPrices->prices || count($eventDataPrices->prices)==0 ){return false;}
        $query='';
        foreach($eventDataPrices->prices as $eventDataPrice){
            $price_arr=[$eventDataPrice->amount, $eventDataPrice->ticketType->name, $eventDataPrice->ticketType->id, $eventItem_id,$event_id];
            
            $query .= '("'.implode('","',$price_arr).'"),';
        }
        $query = substr_replace($query ,"", -1);
        if(!$single){
            $events_data_prices_table = $this->temp_tables['events_data_prices'];
        }
        else{
            $events_data_prices_table = $this->tables['events_data_prices'];
        }
        return $this->wpdb->query('INSERT INTO '.$events_data_prices_table.' (amount,ticketType_name,ticketType_id,EventInstanceID,EventPriceParentId) VALUES ' . $query);
    }

    public function insertMembers($item){
        $membersData  = [
            "id"             				=> $item->id,
            "description"             		=> $item->description,
            "imageUrl"             			=> $item->imageUrl,
            "htmlDescription"             	=> $item->htmlDescription,
            "name"             				=> $item->name,
            "price"                         => $item->price,
            "renewalPrice"                  => $item->renewalPrice,
            "updated"						=> date('Y-m-d h:i:s')
        ];
        return $this->wpdb->insert($this->temp_tables['members'], $membersData );
    }

    public function insertFunds($item){
        $fundData  = [
            "id"             				=> $item->id,
            "description"             		=> $item->description,
            "name"             			    => $item->name,
        ];
        return $this->wpdb->insert($this->temp_tables['funds'], $fundData );
    }

    public function insertTagTypes($item){
        $tagTypeData  = [
            "id"             				=> $item->id,
            "description"             		=> $item->description,
            "name"             			    => $item->name,
        ];
        return $this->wpdb->insert($this->temp_tables['tag_types'], $tagTypeData );
    }

    public function insertTags($item_id,$tag){
        $tagData = [
            "id"                    =>$tag->id,
            "name"                  =>$tag->name,
            "tag_type"              =>$item_id,
        ];
        return $this->wpdb->insert($this->temp_tables['tags'], $tagData );
    }

    public function UpdateSingleEvent($id, $eventID = false){
        $events = $this->tables['events'];
        $relations = $this->tables['relationships'];
        $instances = $this->tables['events_data'];
        $price = $this->tables['events_data_prices'];
        $count = 0;
        $available = 0;
        $error=[];
        if($id!=null&&!$eventID){
        /*geting event id from post id*/
            $sql_get_event = "SELECT spektrixID FROM ".$relations." WHERE postID='".$id."'";
            $eventID = $this->wpdb->get_var($sql_get_event);
        }
        elseif($id==null&&$eventID){
            $sql_get_event_id = "SELECT postID FROM ".$relations." WHERE spektrixID='".$eventID."'";
            $id = $this->wpdb->get_var($sql_get_event_id);
        }
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            WP_CLI::line( 'Updating ' .$eventID);
        }
        /*deleting instances and prices*/
        $sql_delete_instances = $this->wpdb->delete($instances, array('EventParentID'=>$eventID));
        $sql_delete_prices = $this->wpdb->delete($price, array('EventPriceParentID'=>$eventID));
       
        /*hitting API for event data and updates db row*/
        $event = $this->api->SpektrixGetAPIEvent($eventID);
        if(!$id || is_wp_error($id)){
            if ( defined( 'WP_CLI' ) && WP_CLI ) {
             WP_CLI::line( 'Creating new event for '. $eventID);
            }
        }
        else{
            if ( defined( 'WP_CLI' ) && WP_CLI ) {
                WP_CLI::line( 'Updating event for '. $eventID);
            } 
        }
        $eventsData = [
            "instanceDates"             	=> $event->instanceDates,
            "firstInstanceDateTime"         => $event->firstInstanceDateTime,
            "lastInstanceDateTime"          => $event->lastInstanceDateTime,
            "attribute_AGERANGE"            =>$event->attribute_AGERANGE,
            "attribute_EVENTSUBJECT"       =>$event->attribute_EVENTSUBJECT,
            "attribute_EVENTTYPE"          =>$event->attribute_EVENTTYPE,
            "attribute_ONLINE"             =>$event->attribute_ONLINE,
            "attribute_TRIGGERWARNINGTAG"  =>$event->attribute_TRIGGERWARNINGTAG,
            "attribute_VENUE"              =>$event->attribute_VENUE,
            "attribute_FAMILYFRIENDLY"     =>$event->attribute_FAMILYFRIENDLY,
            "attribute_FREEFORMEMBERS"     =>$event->attribute_FREEFORMEMBERS,
            "attribute_SCHOOLSKEYSTAGE"    =>$event->attribute_SCHOOLSKEYSTAGE,
            "attribute_KS1TOPICS"          =>$event->attribute_KS1TOPICS,
            "attribute_KS3TOPICS"          =>$event->attribute_KS3TOPICS,
        ];
        $sql_event_update=$this->wpdb->update( $events,$eventsData, array( 'id' => $eventID ));

        $tax = $this->generateTaxData($event,$id);
        $meta = $this->generateMetaData($event,$id);
        $new_id = $this->createPosts($this->tables['relationships'], $event, $tax, 'event',$meta, $id);
        
        /*Set Post to active*/
        //wp_set_object_terms($id,154,'event-category');
        /*Updating the dates in post meta*/
        
        /*returning array with total count(always 1 for a single event) and db id, for use in UpdateTablesEventInstance*/
        $sql_get_event_db_id = "SELECT EventID FROM ".$events." WHERE id='".$eventID."'";
        
        $eventDBID = $this->wpdb->get_var($sql_get_event_db_id);
        
        return array(1,$eventDBID);
    }

    public function UpdatePosts(){
        $events = $this->tables['events'];
        $relations = $this->tables['relationships'];
        $error=false;
        $countEvent=0;
        $countMerch=0;

        /*Checking if the Relationship table has rows, if not we need to recreate it from current post data*/
        $count = $this->wpdb->get_var("SELECT COUNT(*)
            FROM ".$relations."  WHERE relationshipID IS NOT NULL");
        if($count == 0){
           $this->rebuildRelations();
        }
        /*Checking again to see if the table has rows, if not this must be an initial import and we need to create posts for all events*/
        $count = $this->wpdb->get_var("SELECT COUNT(*)
            FROM ".$relations."  WHERE relationshipID IS NOT NULL");
        if($count == 0){
            $sql_event_rel ="SELECT ".$events.".* FROM ".$events;
            $data_event_rel = $this->wpdb->get_results($sql_event_rel);
        }
        else{
            $sql_event_rel ="SELECT ".$events.".*, ".$relations.".postID FROM ".$events." LEFT JOIN ".$relations." ON ".$relations.".spektrixID = ".$events.".id";
            $data_event_rel = $this->wpdb->get_results($sql_event_rel);
        }
        //$attributes = $this->wpdb->get_results('SELECT attributeID, attributeName FROM '.$this->tables['events_attributes'], ARRAY_A);
        /*Creates Post and Updates attributes and relations table.*/
        foreach($data_event_rel as $datum){
            $id = isset($datum->postID)&&$datum->postID?$datum->postID:false;
            $tax_input = $this->generateTaxData($datum,$id);
            $meta_input = $this->generateMetaData($datum,$id);
            $create_post = $this->createPosts($relations, $datum, $tax_input, 'event',$meta_input,$id);
            if(!$create_post){
                $error = true;
            }
            else{
                $countEvent++;
            }
        }
        
        
        /*Gets any posts in the relations table that are deleted or missing from the spektrix data*/
        $sql_rel_event ="SELECT ".$relations.".postID FROM ".$relations." LEFT JOIN ".$events." ON ".$events.".id = ".$relations.".spektrixID WHERE ".$events.".id IS NULL";
        $data_rel_event = $this->wpdb->get_results($sql_rel_event, ARRAY_A);
        /*sets the term to not-active and updates the relations table*/
        foreach($data_rel_event as $datum){
            wp_remove_object_terms($datum['postID'],154,'event-category');
            $this->wpdb->delete($relations,array('postID'=>$datum['postID']));
        }

        if(!$error){
            //$countMeta = $this->UpdatePostsMeta();
            $message = $countEvent .' events added.';           
        }
        else{
            $message='Something went wrong while inserting the records';
        }

        return $message;
    }

    public function UpdatePostsMeta(){
        $events = $this->tables['events'];
        $relations = $this->tables['relationships'];
        $count=0;
        /*Gets post ids and instance dates for all events*/
        $sql_events="SELECT firstinstanceDateTime, lastinstanceDateTime, postID FROM ".$events." LEFT JOIN ".$relations." ON ".$relations.".spektrixID = ".$events.".id";
        $data_events = $this->wpdb->get_results($sql_events, ARRAY_A);
        foreach($data_events as $datum){
            $first_instance = update_post_meta($datum['postID'],'start_date',date('Y-m-d H:i:s',strtotime($datum['firstinstanceDateTime'])));
            $last_instance = update_post_meta($datum['postID'],'end_date',date('Y-m-d H:i:s',strtotime($datum['lastinstanceDateTime'])));
            $on_sale = update_post_meta($datum['postID'],'event_has_tickets',$datum['isOnSale']);
            if($first_instance){
                $count++;
            }
            if($last_instance){
                $count++;
            }
        }
        return $count;
    }

    public function createPosts($relations, $datum, $tax_input, $type, $meta_input = false, $id = false){
        //Before we create a new post, we need to check if the post already exists and isn't in the relationship table
        
        $args = array(
            'post_content'=>$datum->description,
            'post_title'=>$datum->name,
            'post_type'=>$type,
            'meta_input'=>$meta_input,
        );
        if(!$id){
            $_args = [
                'post_type'=>$type,
                'post_status'=>[ 'publish','draft','private'],
                'meta_query' => array(
                    array(
                        'key' => 'ap-event-spektrix-id',
                        'value' => $datum->id,
                        'compare' => 'LIKE'
                    )
                )
            ]; 
            $_query = new WP_Query($_args);
            if($_query->have_posts()){
                $posts = $_query->posts;
            }
            if($posts){
                $_id = $posts[0]->ID;
                $args['ID'] = $_id;
                $status = $posts[0]->status?:'draft';
                $args['post_status']=$status;
            }
        }
        if($id){
            $args['ID']=$id;
            $status = get_post_status($id)?:'draft';
            $args['post_status']=$status;
        }
        $postId = wp_insert_post($args);
        foreach($tax_input as $tax=>$terms){
            if($terms){
                wp_set_object_terms($postId,$terms,$tax,true);
            }
        }
        if(!$postId){
            return false;
        }
        if(!$id && $datum->imageUrl){
            $image = media_sideload_image( $datum->imageUrl, $postId, $datum->name,'id' );
            set_post_thumbnail( $postId, $image );
        }
        /*Sets the short ID. Used for Spektrix iFrames*/
        if(!$id){
            $shortID = preg_split("/[a-zA-Z]/",$datum->id,2);
            $this->wpdb->insert($relations,array('spektrixID'=>$datum->id,'shortID'=>$shortID[0],'postID'=>$postId,'updated'=>date('Y-m-d')));
        }
        return true;
    }


    public function generateTaxData($event,$id){
        $h_terms = [
            'event_audience' => $event->attribute_AGERANGE,
            'event_type' => $event->attribute_EVENTTYPE,
            'event_schools_key_stage' => $event->attribute_SCHOOLSKEYSTAGE,
        ];
        $nh_terms = [
            'event_ks1_topics' => $event->attribute_KS1TOPICS,
            'event_ks3_topics' => $event->attribute_KS3TOPICS,
            'event_subject' => $event->attribute_EVENTSUBJECT,
        ];

        foreach($h_terms as $key=>$value){
            $term = term_exists($value,$key);
            if(!$term){
                $term = wp_insert_term(ucwords(str_replace('-',' ',$value)),$key);
            }
            else{
                $term = (int) $term['term_id'];
            }
        }       


        $tax_input=array_merge($h_terms,$nh_terms);
        return $tax_input;
    }

    public function generateMetaData($event,$id){
        $next_instance_date = $this->getNextInstanceDate($event->id);
        $start = date('Y-m-d H:i:s',strtotime($event->firstInstanceDateTime));
        $end = date('Y-m-d H:i:s',strtotime($event->lastInstanceDateTime));
        $meta_input=array(
            'spektrix_ticketing'=>1,
            'event_has_tickets'=>$event->isOnSale?1:0,
            'start_date'=>$start,
            'end_date'=>$end,
            'spektrix_id'=>$event->id,
            'duration'=>$event->duration,
            'next_instance_date'=>$next_instance_date?date('Y-m-d H:i:s',strtotime($next_instance_date)):$start,
            'trigger_warning_tag'=>$event->attribute_TRIGGERWARNINGTAG,
            'online'=>$event->attribute_ONLINE?1:0,
            'family_friendly'=>$event->attribute_FAMILYFRIENDLY?1:0,
            'free_for_members'=>$event->attribute_FREEFORMEMBERS?1:0,
            'location'=>$event->attribute_VENUE,
        );
        return $meta_input;
    }

    public function getNextInstanceDate($id){
        $instances = $this->tables['events_data'];
        $sql = "SELECT start FROM ".$instances." WHERE EventParentID='".$id."' ORDER BY start ASC LIMIT 1";
        $date = $this->wpdb->get_var($sql);
        return $date;
    }

    public function rebuildRelations(){
        $relations = $this->tables['relationships'];
        $posts = get_posts([
            'post_type'=>'event',
            'numberposts'=>-1,
            'post_status'=>'any',
            'meta_query'=>[
                [
                    'key'=>'spektrix_id',
                    'compare'=>''
                ]
            ]
        ]);
        if($posts){
            foreach($posts as $post){
                $spektrix_id = get_post_meta($post->ID,'spektrix_id',true);
                if($spektrix_id){
                 $shortID = preg_split("/[a-zA-Z]/",$spektrix_id,2);
                 $this->wpdb->insert($relations,array('spektrixID'=>$spektrix_id,'shortID'=>$shortID[0],'postID'=>$post->ID,'updated'=>date('Y-m-d')));
                }
            }
        }
    }

    public function spektrixIDstoMeta(){
        $posts = get_posts([
            'post_type'=>'event',
            'numberposts'=>-1,
            'post_status' => 'any'
        ]);
        $spektrix = new HdKSpektrix;
        
        if($posts){
            foreach($posts as $post){
                $sp_id = $spektrix->get_event_id($post->ID);
                if($sp_id){
                    $p = update_post_meta($post->ID,'spektrix_id',$sp_id);
                }
            }
        }
    }

    public function getEventIDfromShortID($eventID){
        $allEvents = $this->api->SpektrixGetAPIEvents();
        $event = array_filter($allEvents,function($n) use($eventID){
            return strpos($n->id,$eventID)===0;
        });
        if($event){
            $event = array_values($event);
            if(count($event)>1){
                return [
                    'error'=>'There is more than one event with this ID'
                ];
            }
            if(!$event[0]->id){
                return [
                    'error'=>'Event ID not found'
                ];
            }
            return $event[0]->id;
        }
        else{
            return [
                'error'=>'Event ID not found'
            ];
        }
    }

    
    public function CreateTempTables(){
        return $this->builder->CreateTempTables();
    }
    
    public function DropTables(){
        return $this->builder->DropTables();
    }
    public function DropBackupTables(){
        return $this->builder->DropBackupTables();
    }
    public function RenameTempTables(){
        return $this->builder->RenameTempTables();
    }
    
    public function DropTempTables(){
         return $this->builder->DropTempTables();
    }

    public function ValidateTempTablesCount(){
        $counts = [];
       
        $counts[$this->temp_tables['events']] = intval($this->tableCounter($this->temp_tables['events']));
        $counts[$this->temp_tables['events_data']] = intval($this->tableCounter($this->temp_tables['events_data']));
        $counts[$this->temp_tables['events_data_prices']] = intval($this->tableCounter($this->temp_tables['events_data_prices']));
        $counts[$this->temp_tables['events_attributes_terms_temp']] = intval($this->tableCounter($this->temp_tables['events_attributes_terms_temp']));
        /* if($this->settings['is_merch_active']){
            $counts[$this->temp_tables['merch']] = intval($this->tableCounter($this->temp_tables['merch']));
            $counts[$this->temp_tables['merch_attributes_terms_temp']] = intval($this->tableCounter($this->temp_tables['merch_attributes_terms_temp']));
        } */
        if($this->settings['is_members_active']){
            $counts[$this->temp_tables['members']] = intval($this->tableCounter($this->temp_tables['members']));
        }
        if($this->settings['is_funds_active']){
            $counts[$this->temp_tables['funds']] = intval($this->tableCounter($this->temp_tables['funds']));
        }
        if($this->settings['is_tags_active']){
            $counts[$this->temp_tables['tag_types']] = intval($this->tableCounter($this->temp_tables['tag_types']));
            $counts[$this->temp_tables['tags']] = intval($this->tableCounter($this->temp_tables['tags']));
        }
        return $counts;   
    }

    public function ValidateTempTables(){
        $newRelationships = $this->checkNewRelationships();
        $tempEvents = $this->getTempEventsWithInstanceCount();
        return ['newRelations'=>$newRelationships,'newEvents'=>$tempEvents];
    }


    public function tableCounter($table){
        $sql ="SELECT COUNT(*) FROM ".$table;
        return $this->wpdb->get_var($sql);
    }

    public function checkNewRelationships(){
        $events = $this->temp_tables['events'];
        $relations = $this->tables['relationships'];
        $sql_event_rel ="SELECT ".$events.".name FROM ".$events." LEFT JOIN ".$relations." ON ".$relations.".spektrixID = ".$events.".id WHERE ".$relations.".spektrixID IS NULL";
        $data_event_rel = $this->wpdb->get_results($sql_event_rel, ARRAY_A);
        $data_event_rel = array_map(function($n){return $n['name'];},$data_event_rel);
        return $data_event_rel;
    }

    public function getTempEventsWithInstanceCount(){
        $events = $this->temp_tables['events'];
        $instances = $this->temp_tables['events_data'];
        $sql = "SELECT ".$events.".name, COUNT(".$instances.".EventInstanceID) AS instances FROM ".$events." LEFT JOIN ".$instances." ON ".$instances.".event_id = ".$events.".id GROUP BY ". $events .".name";
        $data = $this->wpdb->get_results($sql, ARRAY_A);
        $data = array_map(function($n){return $n['name'].': '.$n['instances'];},$data);
        return $data;
    }

    public function LogError($error){
        $error_log = plugin_dir_path( __FILE__ ).'error.txt';
        $log = fopen($error_log, 'c');
        fseek($log,-1,SEEK_END);
		fwrite($log, wp_date('YmdH:i:s').' '.$error. PHP_EOL.PHP_EOL);
		fclose($log);
    }
}
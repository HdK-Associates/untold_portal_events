<?php
class HdKSpBuild {
    private array $settings;
    private $wpdb;
    private $charset_collate;
    private $events_table;
    private $events_data_table;
    private $events_data_prices_table;
    private $attributes_table;
    private $attributes_terms_table;
    private $members_table;
    private $relationship_table;
    private $funds_table;
    private $tags_types_table;
    private $tags_table;
    private $events_table_temp;
    private $events_data_table_temp;
    private $events_data_prices_table_temp;
    private $attributes_terms_table_temp;
    private $merch_table_temp;
    private $attributes_merch_terms_table_temp;
    private $members_table_temp;
    private $funds_table_temp;
    private $tags_types_table_temp;
    private $tags_table_temp;
    
    public function __construct() {
        $this->settings = HdKSpUtilities::get_spektrix_settings();
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->charset_collate = $this->wpdb->get_charset_collate();
        $this->events_table = $this->wpdb->prefix . 'spektrix_events';
        $this->events_data_table = $this->wpdb->prefix . 'spektrix_events_data';
        $this->events_data_prices_table = $this->wpdb->prefix . 'spektrix_events_data_prices';
        $this->attributes_table = $this->wpdb->prefix . 'spektrix_event_attributes';
        $this->attributes_terms_table = $this->wpdb->prefix . 'spektrix_event_terms';

        $this->members_table = $this->wpdb->prefix . 'spektrix_members';
        $this->relationship_table = $this->wpdb->prefix . 'spektrix_relationships';
        $this->funds_table = $this->wpdb->prefix . 'spektrix_funds';
        $this->tags_types_table = $this->wpdb->prefix . 'spektrix_newsletter_tags_types';
        $this->tags_table = $this->wpdb->prefix . 'spektrix_newsletter_tags';
        
        $this->events_table_temp = $this->wpdb->prefix . 'spektrix_events_temp';
        $this->events_data_table_temp = $this->wpdb->prefix . 'spektrix_events_data_temp';
        $this->events_data_prices_table_temp = $this->wpdb->prefix . 'spektrix_events_data_prices_temp';
        $this->attributes_terms_table_temp = $this->wpdb->prefix . 'spektrix_event_terms_temp';
        $this->members_table_temp = $this->wpdb->prefix . 'spektrix_members_temp';
        $this->funds_table_temp = $this->wpdb->prefix . 'spektrix_funds_temp';
        $this->tags_types_table_temp = $this->wpdb->prefix . 'spektrix_newsletter_tags_types_temp';
        $this->tags_table_temp = $this->wpdb->prefix . 'spektrix_newsletter_tags_temp';
    }

    public function createTables(){
        if(get_transient('spektrix_update_running')) return;
        $sql=array();
        if($this->wpdb->get_var("SHOW TABLES LIKE '$this->events_table'") != $this->events_table) {
            $sql[] = "CREATE TABLE " .$this->events_table."(
                EventID int(20) NOT NULL AUTO_INCREMENT,
                id longtext NULL,
                webEventId int(20) NULL,
                description longtext NULL,
                duration int(5) NULL,
                imageUrl varchar(255) NULL,
                isOnSale varchar(255) NULL,
                relatedStockItems longtext NULL,
                name varchar(255) NULL,
                instanceDates varchar(255) NULL,
                firstInstanceDateTime varchar(255) NULL,
                lastInstanceDateTime varchar(255) NULL,
                attribute_24hrsPreSale varchar(255) NULL, 
                attribute_GetLiveInstances varchar(255) NULL,
                attribute_Website varchar(255) NULL,
                attribute_Music varchar(255) NULL,
                attribute_Theatre varchar(255) NULL,
                attribute_Christmas varchar(255) NULL,
                attribute_Comedy varchar(255) NULL,
                attribute_CreativeLearning varchar(255) NULL,
                attribute_IceHockey varchar(255) NULL,
                attribute_IceRink varchar(255) NULL,
                attribute_IceRinkEvents varchar(255) NULL,
                attribute_Sport varchar(255) NULL,
                attribute_SummerSeries varchar(255) NULL,
                attribute_TheTerrace varchar(255) NULL,
                attribute_ToursAndTalks varchar(255) NULL,
                attribute_Festivals varchar(255) NULL,
                attribute_WireAndSky varchar(255) NULL,
                attribute_PitchAndPutt varchar(255) NULL,
                attribute_LifestyleExhibitions varchar(255) NULL,
                attribute_TradeExhibitions varchar(255) NULL,
                attribute_Accessible varchar(255) NULL,
                attribute_SoldOut varchar(255) NULL,
                attribute_Cancelled varchar(255) NULL,
                attribute_Postponed varchar(255) NULL,
                attribute_WaitingList varchar(255) NULL,
                attribute_SellingFast varchar(255) NULL,
                attribute_Free varchar(255) NULL,
                attribute_WebsiteSupplementaryItem varchar(255) NULL,
                attribute_WebsiteSupplementaryItemOrder varchar(255) NULL,
                attribute_SubHeading varchar(255) NULL,
                totalAvailable int(11),
                updated datetime NULL,
                PRIMARY KEY  (EventID)
            ) ". $this->charset_collate .";";
        }
        if($this->wpdb->get_var("SHOW TABLES LIKE '$this->events_data_table'") != $this->events_data_table) {
            $sql[] = "CREATE TABLE ".$this->events_data_table."(
                EventInstanceID int(20) NOT NULL AUTO_INCREMENT,
                EventParentID varchar(255) NOT NULL,
                isOnSale int(11) NULL,
                planId varchar(255) NULL,
                priceList_id varchar(255) NULL,
                event_id varchar(255) NULL,
                start varchar(255) NULL,
                startUtc varchar(255) NULL,
                startSellingAtWeb varchar(255) NULL,
                startSellingAtWebUtc varchar(255) NULL,
                stopSellingAtWeb varchar(255) NULL,
                stopSellingAtWebUtc varchar(255) NULL,
                webInstanceId int(11) NULL,
                cancelled varchar(255) NULL,
                id varchar(255) NULL,
                attribute_AutoDistancingSeatGap varchar(255) NULL,
                attribute_AudioDescribed varchar(255) NULL,
                attribute_Captioned varchar(255) NULL,
                attribute_DementiaFriendly varchar(255) NULL,
                attribute_PressNight varchar(255) NULL,
                attribute_Preview varchar(255) NULL,
                attribute_RelaxedPerformance varchar(255) NULL,
                attribute_SignedPerformance varchar(255) NULL,
                attribute_TouchTour varchar(255) NULL,
                attribute_SchoolsPerformance varchar(255) NULL,
                attribute_SoldOut varchar(255) NULL,
                attribute_Cancelled varchar(255) NULL,
                attribute_Postponed varchar(255) NULL,
                attribute_SellingFast varchar(255) NULL,
                attribute_WaitingList varchar(255) NULL,
                attribute_Free varchar(255) NULL,
                available int(5) NULL,
                updated datetime NULL,
                PRIMARY KEY  (EventInstanceID)
                ) ". $this->charset_collate .";";
        }
        if($this->wpdb->get_var("SHOW TABLES LIKE '$this->events_data_prices_table'") != $this->events_data_prices_table) {
            $sql[] = "CREATE TABLE ".$this->events_data_prices_table."(
                EventPriceListID int(20) NOT NULL AUTO_INCREMENT,
                amount decimal(10,2) NULL,
                ticketType_name varchar(255) NULL,
                ticketType_id varchar(255) NULL,
                EventInstanceID varchar(255) NULL,
                EventPriceParentID varchar(255) NULL,
                updated datetime NULL,
                PRIMARY KEY  (EventPriceListID)
                ) ". $this->charset_collate .";";
        }
        if($this->wpdb->get_var("SHOW TABLES LIKE '$this->relationship_table'") != $this->relationship_table) {
            $sql[] = "CREATE TABLE ".$this->relationship_table."(
                relationshipID int(20) NOT NULL AUTO_INCREMENT,
                spektrixID longtext NULL,
                shortID int(11) NULL,
                postID int(11) NULL,
                updated datetime NULL,
                PRIMARY KEY  (relationshipID)
                ) ". $this->charset_collate .";";
        }
        if($this->wpdb->get_var("SHOW TABLES LIKE '$this->attributes_table'") != $this->attributes_table) {
            $sql[] = "CREATE TABLE ".$this->attributes_table."(
                attributeID int(20) NOT NULL AUTO_INCREMENT,
                attributeName longtext NULL,
                attributeType longtext NULL,
                updated datetime NULL,
                PRIMARY KEY  (attributeID)
                ) ". $this->charset_collate .";";
        }
        if($this->wpdb->get_var("SHOW TABLES LIKE '$this->attributes_terms_table'") != $this->attributes_terms_table) {
            $sql[] = "CREATE TABLE ".$this->attributes_terms_table."(
                termID int(20) NOT NULL AUTO_INCREMENT,
                attributeID int(20) NULL,
                eventID longtext NULL,
                term longtext NULL,
                updated datetime NULL,
                PRIMARY KEY  (termID)
                ) ". $this->charset_collate .";";
        }

        if(1==$this->settings['is_members_active']){
            if($this->wpdb->get_var("SHOW TABLES LIKE '$this->members_table'") != $this->members_table) {
                $sql[] = "CREATE TABLE " .$this->members_table."(
                    memberID int(20) NOT NULL AUTO_INCREMENT,
                    id longtext NOT NULL,
                    description longtext NULL,
                    htmlDescription longtext NULL,
                    imageUrl varchar(255) NULL,
                    name varchar(255) NULL,
                    updated datetime NULL,
                    price float(24) NULL,
                    renewalPrice float(24) NULL,
                    attribute_Frequency varchar(255) NULL,
                    PRIMARY KEY  (memberID)
                    ) ". $this->charset_collate .";";
            }
        }

        if(1==$this->settings['is_funds_active']){
            if($this->wpdb->get_var("SHOW TABLES LIKE '$this->funds_table'") != $this->funds_table) {
                $sql[] = "CREATE TABLE " .$this->funds_table."(
                    fundID int(20) NOT NULL AUTO_INCREMENT,
                    id longtext NOT NULL,
                    description longtext NULL,
                    name longtext NULL,
                    PRIMARY KEY  (fundID)
                    ) ". $this->charset_collate .";";
            }
        }

        if(1==$this->settings['is_tags_active']){
            if($this->wpdb->get_var("SHOW TABLES LIKE '$this->tags_types_table'") != $this->tags_types_table) {
                $sql[] = "CREATE TABLE " .$this->tags_types_table."(
                    tagID int(20) NOT NULL AUTO_INCREMENT,
                    id longtext NOT NULL,
                    description longtext NULL,
                    name longtext NULL,
                    PRIMARY KEY  (tagID)
                    ) ". $this->charset_collate .";";
            }
            if($this->wpdb->get_var("SHOW TABLES LIKE '$this->tags_table'") != $this->tags_table) {
                $sql[] = "CREATE TABLE " .$this->tags_table."(
                    tagID int(20) NOT NULL AUTO_INCREMENT,
                    id longtext NOT NULL,
                    tag_type longtext NOT NULL,
                    name longtext NULL,
                    PRIMARY KEY  (tagID)
                    ) ". $this->charset_collate .";";
                }
        }
        if($sql){
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta( $sql );
        }
    }

    public function createTempTables(){
        $sql=array();
        $sql[] = "CREATE TABLE " .$this->events_table_temp."(
            EventID int(20) NOT NULL AUTO_INCREMENT,
            id longtext NULL,
            webEventId int(20) NULL,
            description longtext NULL,
            duration int(5) NULL,
            imageUrl varchar(255) NULL,
            isOnSale varchar(255) NULL,
            relatedStockItems longtext NULL,
            name varchar(255) NULL,
            instanceDates varchar(255) NULL,
            firstInstanceDateTime varchar(255) NULL,
            lastInstanceDateTime varchar(255) NULL,
            attribute_24hrsPreSale varchar(255) NULL, 
            attribute_GetLiveInstances varchar(255) NULL,
            attribute_Website varchar(255) NULL,
            attribute_Music varchar(255) NULL,
            attribute_Theatre varchar(255) NULL,
            attribute_Christmas varchar(255) NULL,
            attribute_Comedy varchar(255) NULL,
            attribute_CreativeLearning varchar(255) NULL,
            attribute_IceHockey varchar(255) NULL,
            attribute_IceRink varchar(255) NULL,
            attribute_IceRinkEvents varchar(255) NULL,
            attribute_Sport varchar(255) NULL,
            attribute_SummerSeries varchar(255) NULL,
            attribute_TheTerrace varchar(255) NULL,
            attribute_ToursAndTalks varchar(255) NULL,
            attribute_Festivals varchar(255) NULL,
            attribute_WireAndSky varchar(255) NULL,
            attribute_PitchAndPutt varchar(255) NULL,
            attribute_LifestyleExhibitions varchar(255) NULL,
            attribute_TradeExhibitions varchar(255) NULL,
            attribute_Accessible varchar(255) NULL,
            attribute_SoldOut varchar(255) NULL,
            attribute_Cancelled varchar(255) NULL,
            attribute_Postponed varchar(255) NULL,
            attribute_WaitingList varchar(255) NULL,
            attribute_SellingFast varchar(255) NULL,
            attribute_Free varchar(255) NULL,
            attribute_WebsiteSupplementaryItem varchar(255) NULL,
            attribute_WebsiteSupplementaryItemOrder varchar(255) NULL,
            attribute_SubHeading varchar(255) NULL,
            totalAvailable int(11),
            updated datetime NULL,
            PRIMARY KEY  (EventID)
        ) ". $this->charset_collate .";";
        $sql[] = "CREATE TABLE ".$this->events_data_table_temp."(
            EventInstanceID int(20) NOT NULL AUTO_INCREMENT,
                EventParentID varchar(255) NOT NULL,
                isOnSale int(11) NULL,
                planId varchar(255) NULL,
                priceList_id varchar(255) NULL,
                event_id varchar(255) NULL,
                start varchar(255) NULL,
                startUtc varchar(255) NULL,
                startSellingAtWeb varchar(255) NULL,
                startSellingAtWebUtc varchar(255) NULL,
                stopSellingAtWeb varchar(255) NULL,
                stopSellingAtWebUtc varchar(255) NULL,
                webInstanceId int(11) NULL,
                cancelled varchar(255) NULL,
                id varchar(255) NULL,
                attribute_AutoDistancingSeatGap varchar(255) NULL,
                attribute_AudioDescribed varchar(255) NULL,
                attribute_Captioned varchar(255) NULL,
                attribute_DementiaFriendly varchar(255) NULL,
                attribute_PressNight varchar(255) NULL,
                attribute_Preview varchar(255) NULL,
                attribute_RelaxedPerformance varchar(255) NULL,
                attribute_SignedPerformance varchar(255) NULL,
                attribute_TouchTour varchar(255) NULL,
                attribute_SchoolsPerformance varchar(255) NULL,
                attribute_SoldOut varchar(255) NULL,
                attribute_Cancelled varchar(255) NULL,
                attribute_Postponed varchar(255) NULL,
                attribute_SellingFast varchar(255) NULL,
                attribute_WaitingList varchar(255) NULL,
                attribute_Free varchar(255) NULL,
                available int(5) NULL,
                updated datetime NULL,
                PRIMARY KEY  (EventInstanceID)
            ) ". $this->charset_collate .";";
        $sql[] = "CREATE TABLE ".$this->events_data_prices_table_temp."(
            EventPriceListID int(20) NOT NULL AUTO_INCREMENT,
            amount decimal(10,2) NULL,
            ticketType_name varchar(255) NULL,
            ticketType_id varchar(255) NULL,
            EventInstanceID varchar(255) NULL,
            EventPriceParentID varchar(255) NULL,
            updated datetime NULL,
            PRIMARY KEY  (EventPriceListID)
            ) ". $this->charset_collate .";";
        $sql[] = "CREATE TABLE ".$this->attributes_terms_table_temp."(
            termID int(20) NOT NULL AUTO_INCREMENT,
            attributeID int(20) NULL,
            eventID longtext NULL,
            term longtext NULL,
            updated datetime NULL,
            PRIMARY KEY  (termID)
            )". $this->charset_collate .";";
        if(1==$this->settings['is_merch_active']){  
            $sql[] = "CREATE TABLE " .$this->merch_table_temp."(
                merchID int(20) NOT NULL AUTO_INCREMENT,
                id longtext NOT NULL,
                description longtext NULL,
                htmlDescription longtext NULL,
                imageUrl varchar(255) NULL,
                name varchar(255) NULL,
                postageAndPackaging float(24) NULL,
                thumbnailUrl varchar(255) NULL,
                price float(24) NULL,
                stockLevel int(11) NULL,
                updated datetime NULL,
                PRIMARY KEY  (merchID)
                )". $this->charset_collate .";";
            $sql[] = "CREATE TABLE ".$this->attributes_merch_terms_table_temp."(
                termID int(20) NOT NULL AUTO_INCREMENT,
                attributeID int(20) NULL,
                merchID longtext NULL,
                term longtext NULL,
                updated datetime NULL,
                PRIMARY KEY  (termID)
                )". $this->charset_collate .";";
        }
        if(1==$this->settings['is_members_active']){
            $sql[] = "CREATE TABLE " .$this->members_table_temp."(
                memberID int(20) NOT NULL AUTO_INCREMENT,
                id longtext NOT NULL,
                description longtext NULL,
                htmlDescription longtext NULL,
                imageUrl varchar(255) NULL,
                name varchar(255) NULL,
                updated datetime NULL,
                price float(24) NULL,
                renewalPrice float(24) NULL,
                attribute_Frequency varchar(255) NULL,
                PRIMARY KEY  (memberID)
                ) ". $this->charset_collate .";";
        }
        if(1==$this->settings['is_funds_active']){
            $sql[] = "CREATE TABLE " .$this->funds_table_temp."(
                fundID int(20) NOT NULL AUTO_INCREMENT,
                id longtext NOT NULL,
                description longtext NULL,
                name longtext NULL,
                PRIMARY KEY  (fundID)
                ) ". $this->charset_collate .";";
        }
        if(1==$this->settings['is_tags_active']){
            $sql[] = "CREATE TABLE " .$this->tags_types_table_temp."(
                tagID int(20) NOT NULL AUTO_INCREMENT,
                id longtext NOT NULL,
                description longtext NULL,
                name longtext NULL,
                PRIMARY KEY  (tagID)
                ) ". $this->charset_collate .";";
            $sql[] = "CREATE TABLE " .$this->tags_table_temp."(
                tagID int(20) NOT NULL AUTO_INCREMENT,
                id longtext NOT NULL,
                tag_type longtext NOT NULL,
                name longtext NULL,
                PRIMARY KEY  (tagID)
                ) ". $this->charset_collate .";";
        }
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta( $sql );
    }

    public function DropTables(){
        $delete = [];
        $delete[] = $this->AlterIfExists($this->events_table,$this->events_table.'_backup');
        $delete[] = $this->AlterIfExists($this->events_data_table,$this->events_data_table.'_backup');
        $delete[] = $this->AlterIfExists($this->events_data_prices_table,$this->events_data_prices_table.'_backup');
        $delete[] = $this->AlterIfExists($this->attributes_terms_table,$this->attributes_terms_table.'_backup');
        $delete[] = $this->AlterIfExists($this->members_table,$this->members_table.'_backup');
        $delete[] = $this->AlterIfExists($this->funds_table,$this->funds_table.'_backup');
        $delete[] = $this->AlterIfExists($this->tags_types_table,$this->tags_types_table.'_backup');
        $delete[] = $this->AlterIfExists($this->tags_table,$this->tags_table.'_backup');
        return $delete;
    }

    public function DropBackupTables(){
        $delete = [];
        $delete[] = $this->wpdb->query("DROP TABLE IF EXISTS ".$this->events_table."_backup");
        $delete[] = $this->wpdb->query("DROP TABLE IF EXISTS ".$this->events_data_table."_backup");
        $delete[] = $this->wpdb->query("DROP TABLE IF EXISTS ".$this->events_data_prices_table."_backup");
        $delete[] = $this->wpdb->query("DROP TABLE IF EXISTS ".$this->attributes_terms_table."_backup");
        $delete[] = $this->wpdb->query("DROP TABLE IF EXISTS ".$this->members_table."_backup");
        $delete[] = $this->wpdb->query("DROP TABLE IF EXISTS ".$this->funds_table."_backup");
        $delete[] = $this->wpdb->query("DROP TABLE IF EXISTS ".$this->tags_types_table."_backup");
        $delete[] = $this->wpdb->query("DROP TABLE IF EXISTS ".$this->tags_table."_backup");
        return $delete;
    }
    
    public function RestoreBackUpTables(){
        $alter=[];
        $alter[] = $this->ReplaceTables($this->events_table.'_backup',$this->events_table);
        $alter[] = $this->ReplaceTables($this->events_data_table.'_backup',$this->events_data_table);
        $alter[] = $this->ReplaceTables($this->events_data_prices_table.'_backup',$this->events_data_prices_table);
        $alter[] = $this->ReplaceTables($this->attributes_terms_table.'_backup',$this->attributes_terms_table);
        $alter[] = $this->ReplaceTables($this->members_table.'_backup',$this->members_table);
        $alter[] = $this->ReplaceTables($this->funds_table.'_backup',$this->funds_table);
        $alter[] = $this->ReplaceTables($this->tags_types_table.'_backup',$this->tags_types_table);
        $alter[] = $this->ReplaceTables($this->tags_table.'_backup',$this->tags_table);
        return $alter;
    }

    public function DropTempTables(){
        $delete = [];
        $delete[] = $this->wpdb->query("DROP TABLE IF EXISTS {$this->events_table_temp}");
        $delete[] = $this->wpdb->query("DROP TABLE IF EXISTS {$this->events_data_table_temp}");
        $delete[] = $this->wpdb->query("DROP TABLE IF EXISTS {$this->events_data_prices_table_temp}");
        $delete[] = $this->wpdb->query("DROP TABLE IF EXISTS {$this->attributes_terms_table_temp}");
        $delete[] = $this->wpdb->query("DROP TABLE IF EXISTS {$this->members_table_temp}");
        $delete[] = $this->wpdb->query("DROP TABLE IF EXISTS {$this->funds_table_temp}");
        $delete[] = $this->wpdb->query("DROP TABLE IF EXISTS {$this->tags_types_table_temp}");
        $delete[] = $this->wpdb->query("DROP TABLE IF EXISTS {$this->tags_table_temp}");
        return $delete;
    }
    
    public function RenameTempTables(){
        $alter=[];
        
        $alter[] = $this->AlterIfExists($this->events_table_temp,$this->events_table);
        $alter[] = $this->AlterIfExists($this->events_data_table_temp,$this->events_data_table);
        $alter[] = $this->AlterIfExists($this->events_data_prices_table_temp,$this->events_data_prices_table);
        $alter[] = $this->AlterIfExists($this->attributes_terms_table_temp,$this->attributes_terms_table);
        $alter[] = $this->AlterIfExists($this->members_table_temp,$this->members_table);
        $alter[] = $this->AlterIfExists($this->funds_table_temp,$this->funds_table);
        $alter[] = $this->AlterIfExists($this->tags_types_table_temp,$this->tags_types_table);
        $alter[] = $this->AlterIfExists($this->tags_table_temp,$this->tags_table);
        return $alter;
    }

    public function AlterIfExists($table_from,$table_new){
        if($this->wpdb->query("SHOW TABLES LIKE '{$table_from}'")) {
            return $this->wpdb->query("ALTER TABLE {$table_from} RENAME TO {$table_new}");
        }
    }

    public function ReplaceTables($table_from,$table_new){
        $this->wpdb->query("DROP TABLE IF EXISTS {$table_new}");
        return $this->AlterIfExists($table_from,$table_new);
    }

    public function createPostTypes(){
        add_action( 'init', array($this,'customPostTypes'));
    }

    public function customPostTypes(){
        $cpt_array=[];
        if('default'==$this->settings['event_cpt']){
            $labels = array(
                'name'               => __( 'Events', 'hdkspektrix' ),
                'singular_name'      => __( 'Event', 'hdkspektrix' ),
                'menu_name'          => __( 'Events', 'hdkspektrix' ),
                'name_admin_bar'     => __( 'Events', 'hdkspektrix' ),
                'add_new'            => __( 'Add New', 'hdkspektrix' ),
                'add_new_item'       => __( 'Add New Event', 'hdkspektrix' ),
                'new_item'           => __( 'New Event', 'hdkspektrix' ),
                'edit_item'          => __( 'Edit Event', 'hdkspektrix' ),
                'view_item'          => __( 'View Event', 'hdkspektrix' ),
                'all_items'          => __( 'All Events', 'hdkspektrix' ),
                'search_items'       => __( 'Search Events', 'hdkspektrix' ),
                'parent_item_colon'  => __( 'Parent Events:', 'hdkspektrix' ),
                'not_found'          => __( 'No Events found.', 'hdkspektrix' ),
                'not_found_in_trash' => __( 'No Events found in Trash.', 'hdkspektrix' )
            );
            $args = array(
                'labels'             => $labels,
                'description'        => __( 'A post type for Spektrix Events.', 'hdkspektrix' ),
                'public'             => true,
                'publicly_queryable' => true,
                'show_ui'            => true,
                'show_in_menu'       => true,
                'show_in_rest'       => true,
                'query_var'          => true,
                'rewrite' => array('slug' => 'whats-on', 'with_front' => false),
                'capability_type'    => 'post',
                'has_archive'        => false,
                'hierarchical'       => false,
                'menu_position'      => 4,
                'menu_icon'           =>'dashicons-tickets-alt',
                'supports'           => array( 'title', 'editor', 'thumbnail' ),
                
            );
        
            
            register_post_type( 'event', $args );

        }

        $event_cpt='default'==$this->settings['event_cpt']?'event':$this->settings['event_cpt'];
        $cpt_array[]=$event_cpt;
        /* if($attributes){
            $attributes = explode(',',$attributes);
            foreach($attributes as $attribute){
                $this->registerAttribute($attribute,$event_cpt);
            }
          }
        $secondary_attributes = get_option('hdk-secondary-attributes');
        if($secondary_attributes){
            $this->registerAttribute('Event Feature', $event_cpt);
        } */

        if(isset($this->settings['is_merch_active'] ) && 1==$this->settings['is_merch_active'] && 'default'==$this->settings['merch_cpt']){
            $labels_0 = array(
                'name'               => __( 'Merchandise', 'hdkspektrix' ),
                'singular_name'      => __( 'Merchandise', 'hdkspektrix' ),
                'menu_name'          => __( 'Merchandise', 'hdkspektrix' ),
                'name_admin_bar'     => __( 'Merchandise', 'hdkspektrix' ),
                'add_new'            => __( 'Add New', 'hdkspektrix' ),
                'add_new_item'       => __( 'Add New Merchandise', 'hdkspektrix' ),
                'new_item'           => __( 'New Merchandise', 'hdkspektrix' ),
                'edit_item'          => __( 'Edit Merchandise', 'hdkspektrix' ),
                'view_item'          => __( 'View Merchandise', 'hdkspektrix' ),
                'all_items'          => __( 'All Merchandise', 'hdkspektrix' ),
                'search_items'       => __( 'Search Merchandise', 'hdkspektrix' ),
                'parent_item_colon'  => __( 'Parent Merchandise:', 'hdkspektrix' ),
                'not_found'          => __( 'No Merchandise found.', 'hdkspektrix' ),
                'not_found_in_trash' => __( 'No Merchandise found in Trash.', 'hdkspektrix' )
            );
            $args_0 = array(
                'labels'             => $labels_0,
                'description'        => __( 'A post type for Spektrix Merchandise.', 'hdkspektrix' ),
                'public'             => true,
                'publicly_queryable' => true,
                'show_ui'            => true,
                'show_in_menu'       => true,
                'show_in_rest'       => true,
                'query_var'          => true,
                'rewrite'            => array( 'slug' => 'merchandise' ),
                'capability_type'    => 'post',
                'has_archive'        => false,
                'hierarchical'       => false,
                'menu_position'      => 4,
                'menu_icon'           =>'dashicons-cart',
                'supports'           => array( 'title', 'editor', 'thumbnail','custom-fields' ),
                
            );

            register_post_type( 'merchandise', $args_0 );
        }
        if(isset($this->settings['is_merch_active'])&&1==$this->settings['is_merch_active']){
          $merch_cpt='default'==$this->settings['merch_cpt']?'merchandise':$this->settings['merch_cpt'];
          $cpt_array[]=$merch_cpt;
          $attributes = get_option('hdk-attributes-merch')?:[];
          if($attributes && count($attributes>0)){
            foreach($attributes as $attribute){
                $this->registerAttribute($attribute,$merch_cpt);
            }
          }
          $secondary_attributes = get_option('hdk-secondary-attributes-merch');
            if($secondary_attributes){
                $this->registerAttribute('merch Feature', $merch_cpt);
            }
        }
         /*  $labels_tax_1 = array(
            'name' => _x( 'Active on Spektrix', 'taxonomy general name' ),
          ); */ 

         /*  register_taxonomy('active-spektrix',$cpt_array, array(
            'hierarchical' => false,
            'labels' => $labels_tax_1,
            'show_ui' => false,
            'show_admin_column' => true,
            'query_var' =>false,
            'default_term' => array('name'=>'Not Active','slug'=>'not-active','description'=>''),
          ));   */

        if( !term_exists( 'Active', 'active-spektrix') ) {
            wp_insert_term(
                'Active',
                'active-spektrix',
                array(
                'description' => '',
                'slug'        => 'active'
                )
            );
        }
        	
    }

    public function getTables(){
        $tables = array(
            'events' => $this->events_table,
            'events_data' => $this->events_data_table,
            'events_data_prices' => $this->events_data_prices_table,
            'events_attributes'  => $this->attributes_table,
            'events_attributes_terms'  => $this->attributes_terms_table,
            'members' => $this->members_table,
            'relationships' => $this->relationship_table,
            'funds' => $this->funds_table,
            'tag_types' => $this->tags_types_table,
            'tags' => $this->tags_table,
            'acf_key'=>'field_6172c08b0aed0',
        );
        return $tables;
    }
    
    public function getTempTables(){
        $tables = array(
            'events' => $this->events_table_temp,
            'events_data' => $this->events_data_table_temp,
            'events_data_prices' => $this->events_data_prices_table_temp,
            'events_attributes_terms_temp'  => $this->attributes_terms_table_temp,
            'members' => $this->members_table_temp,
            'funds' => $this->funds_table_temp,
            'tag_types' => $this->tags_types_table_temp,
            'tags' => $this->tags_table_temp,
        );
        return $tables;
    }
    public function registerAttribute($attribute,$cpt){
        $name = $attribute;
        $slug = strtolower('attribute_'.preg_replace('/\s+/', '', $attribute));
        $labels_tax= array(
            'name' => $name,
            'singular_name' => $name,
            'search_items' => 'Search '. $name ,
            'all_items' => 'All ' .$name,
            'edit_item' => 'Edit '.$name, 
            'update_item' => 'Update' .$name,
            'add_new_item' => 'Add New '.$name,
            'menu_name' => $name,
        ); 
        
        register_taxonomy($slug,array($cpt), array(
            'hierarchical' => true,
            'labels' => $labels_tax,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array( 'slug' =>  $slug),
        )); 
    }
}

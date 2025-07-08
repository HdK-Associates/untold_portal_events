<?php
class HdKSpActions{
    private array $settings;
    private HdKSpPopulate $populate;
    private HdKSpForms $form;

    public function  init(){
        add_action('admin_menu', array($this,'PluginMenu'));

        /*Adding custom cron schedules*/
        add_filter('cron_schedules',array($this,'AddCronSchedules'));

        /*Adding default cron job*/
        add_action('spektrix_cron_hook', array($this, 'UpdateTables'));
        if(!wp_next_scheduled('spektrix_cron_hook')){
            wp_schedule_event(strtotime("+4 hours"),'fourhours','spektrix_cron_hook');
        }

        /*Updating cron job if options are changed*/
        add_action('update_option_hdk-cron', array($this, 'UpdateCronJob'),10,3);

        add_action( 'rest_api_init', function () {
            register_rest_route( 'spektrix/v1', '/update', array(
              'methods' => 'GET',
              'callback' => array($this,'UpdateTablesNow'),
              'permission_callback' => function () {
                return current_user_can( 'edit_others_posts' );
              }
            ) );
            register_rest_route( 'spektrix/v1', '/updateinstances', array(
              'methods' => 'GET',
              'callback' => array($this,'UpdateTablesInstances'),
              'permission_callback' => function () {
                return current_user_can( 'edit_others_posts' );
              }
            ) );
            register_rest_route( 'spektrix/v1', '/updatewordpressdata', array(
                'methods' => 'GET',
                'callback' => array($this,'UpdatePosts'),
                'permission_callback' => function () {
                    return current_user_can( 'edit_others_posts' );
                  }
              ) );
            register_rest_route( 'spektrix/v1', '/initialpopulate', array(
                'methods' => 'GET',
                'callback' => array($this,'InitialPopulateComplete'),
                'permission_callback' => function () {
                    return current_user_can( 'edit_others_posts' );
                  }
              ) );
            register_rest_route( 'spektrix/v1', '/populatecomplete', array(
                'methods' => 'GET',
                'callback' => array($this,'PopulateComplete'),
                'permission_callback' => function () {
                    return current_user_can( 'edit_others_posts' );
                  }
              ) );
            register_rest_route( 'spektrix/v1', '/insertpopulatecomplete', array(
                'methods' => 'GET',
                'callback' => array($this,'InsertPopulateComplete'),
                'permission_callback' => function () {
                    return current_user_can( 'edit_others_posts' );
                  }
              ) );
            register_rest_route( 'spektrix/v1', '/rejectpopulatecomplete', array(
                'methods' => 'GET',
                'callback' => array($this,'DropTempTables'),
                'permission_callback' => function () {
                    return current_user_can( 'edit_others_posts' );
                  }
              ) );
            register_rest_route( 'spektrix/v1', '/singlecomplete', array(
                'methods' => 'GET',
                'callback' => array($this,'SingleComplete'),
                'permission_callback' => function () {
                    return current_user_can( 'edit_others_posts' );
                  }
              ) );
            register_rest_route( 'spektrix/v1', '/updatepostmeta', array(
                'methods' => 'GET',
                'callback' => array($this,'UpdatePostsMeta'),
                'permission_callback' => function () {
                    return current_user_can( 'edit_others_posts' );
                  }
              ) );
              register_rest_route( 'spektrix/v1', '/error', array(
                'methods' => 'GET',
                'callback' => array($this,'DropTempTables'),
                'permission_callback' => function () {
                    return current_user_can( 'edit_others_posts' );
                  }
              ) );
            register_rest_route( 'spektrix/v1', '/updatesingleevent/(?P<id>\d+)', array(
                'methods' => 'GET',
                'callback' => array($this,'UpdateSingleEvent'),
                'permission_callback' => function () {
                    return current_user_can( 'edit_others_posts' );
                  },
                'args' => array(
                    'id' => array(
                      'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                      }
                    ),
                  ),
            ) );

            register_rest_route( 'spektrix/v1', '/importevent/(?P<id>\d+)', array(
                'methods' => 'GET',
                'callback' => array($this,'ImportEvent'),
                'permission_callback' => function () {
                    return current_user_can( 'edit_others_posts' );
                  },
                  'args' => array(
                    'id' => array(
                      'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                      }
                    ),
                  ),
            ) );

            register_rest_route( 'spektrix/v1', '/submitnewsletter', array(
                'methods' => 'POST',
                'callback' => array($this,'SubmitNewsletter'),
                'permission_callback' => '__return_true',
              ) );
            register_rest_route( 'spektrix/v1', '/form_tags', array(
                'methods' => 'POST',
                'callback' => array($this,'ProcessFormTags'),
                'permission_callback' => '__return_true'
            ) );
        } );
        add_action( 'admin_enqueue_scripts', array($this,'EnqueueSpektrixScripts') );  
        
    }

    public function __construct($settings){
        $this->settings = $settings;    
        $this->populate = new HdKSpPopulate($this->settings);
        $this->form = new HdKSpForms($this->settings);
        $this->init();
    }

    public function PluginMenu() {

        add_menu_page('Spektrix', 
            'Spektrix',
            'edit_others_posts', 
            'hdk_spektrix', 
            array($this,'displaySpektrixOption'), 
            "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyNy42LjEsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiDQoJIHZpZXdCb3g9IjAgMCAyMjUgMjI3IiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCAyMjUgMjI3OyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+DQo8cGF0aCBkPSJNMTEyLjUsOC41Yy01OCwwLTEwNSw0Ny0xMDUsMTA1czQ3LDEwNSwxMDUsMTA1czEwNS00NywxMDUtMTA1UzE3MC41LDguNSwxMTIuNSw4LjV6IE04NS45LDEzOS4ySDcyLjZ2LTIxSDUxLjN2MjFIMzguMQ0KCXYtNTJoMTMuM3YyMC4yaDIxLjJWODcuMmgxMy4zVjEzOS4yeiBNMTMwLjYsMTM5LjJoLTEyLjl2LTQuOWMtMSwxLjctMi41LDMuMS00LjUsNC4xcy00LjIsMS41LTYuNiwxLjVjLTMuMSwwLTUuOS0wLjgtOC4zLTIuNA0KCWMtMi40LTEuNi00LjMtMy44LTUuNy02LjdjLTEuNC0yLjktMi4xLTYuMi0yLjEtMTBjMC0zLjcsMC43LTcsMi05LjhjMS40LTIuOCwzLjItNSw1LjctNi41YzIuNC0xLjUsNS4yLTIuMyw4LjQtMi4zDQoJYzIuMywwLDQuNCwwLjUsNi40LDEuNGMxLjksMC45LDMuNSwyLjIsNC41LDMuOHYtMjBoMTMuMVYxMzkuMnogTTE3MC44LDEzOS4ybC0yMC45LTIzLjV2MjMuNWgtMTMuNnYtNTJoMTMuNnYyMi4zbDIwLjQtMjIuM2gxNS45DQoJbC0yMi45LDI0LjdsMjMuNiwyNy4zSDE3MC44eiIvPg0KPGc+DQoJPHBhdGggZD0iTTExNS45LDEyNy42YzEuMi0xLjYsMS44LTMuOCwxLjgtNi44YzAtMi45LTAuNi01LjEtMS44LTYuN2MtMS4yLTEuNS0zLTIuMy01LjMtMi4zYy0yLjMsMC00LDAuOC01LjIsMi4zDQoJCWMtMS4yLDEuNS0xLjgsMy43LTEuOCw2LjZjMCwyLjksMC42LDUuMiwxLjksNi45YzEuMywxLjYsMywyLjQsNS4yLDIuNFMxMTQuNiwxMjkuMiwxMTUuOSwxMjcuNnoiLz4NCjwvZz4NCjwvc3ZnPg0K"
        );
     
     }

     public function EnqueueSpektrixScripts() {
        
        wp_enqueue_script( 'awesomplete', plugin_dir_url( __DIR__ ). 'js/awesomplete.min.js', array(), HDK_SPEKTRIX_VERSION, true );
        wp_enqueue_script( 'spektrix', plugin_dir_url( __DIR__ ). 'js/spektrix.js', array(), HDK_SPEKTRIX_VERSION, true );
        wp_enqueue_script( 'event_admin',plugin_dir_url( __DIR__ ). 'js/event_admin.js', array('jquery'), HDK_SPEKTRIX_VERSION, true );
        wp_enqueue_style( 'spektrix', plugin_dir_url( __DIR__ ). 'css/spektrix.css',HDK_SPEKTRIX_VERSION);
       
    }

    public function UpdateTables(){
        wp_mail('chad@wearehdk.com','Spektrix Cron Started',wp_date('YmdH:i:s'));
        try{
            return $this->populate->UpdateTables();
        }
        catch(Exception $e){
            wp_mail('chad@wearehdk.com','Spektrix Cron Error',$e->getMessage());
        }
    }

    public function UpdateTablesNow(){
        return $this->populate->UpdateTablesEvents();
    }
    
    public function UpdateSingleEvent($data){
        return $this->populate->UpdateSingleEvent($data['id']);
    }

    public function importEvent($data){
        return $this->populate->ImportEvent(null,$data['id']);
    }

    public function InitialPopulateComplete(){
        update_option('hdk-populate-tables',true);
        return $this->UpdatePosts();
    }

    public function PopulateComplete(){
        return json_encode($this->populate->ValidateTempTables());
    }

    public function InsertPopulateComplete(){
        delete_transient('spektrix_update_running');
        $this->populate->DropBackupTables();
        $drop_tables = $this->populate->DropTables();
        $rename_tables = $this->populate->RenameTempTables();
        $refresh = $this->RefreshCacheHook();
        return array($drop_tables,$rename_tables,$refresh);
    }
    
    public function SingleComplete(){
        $refresh = $this->RefreshCacheHook();
        return 'success';
    }

    
    public function DropTempTables(){
        $this->populate->DropTempTables();
        return 'Something went wrong. Please try again';
    }

    /*This hook is for LiteSpeed cache, which will fire Purge All if you add the hook to the plugin settings see /wp-admin/admin.php?page=litespeed-cache#purge */
    public function RefreshCacheHook(){
        do_action('spektrix_refresh_complete');
        return 'called spektrix refresh complete';
    }
    
    public function UpdateCronJob( $old_value, $value ) {
        $timestamp = wp_next_scheduled( 'spektrix_cron_hook' );
        wp_unschedule_event( $timestamp, 'spektrix_cron_hook' );
        wp_schedule_event(time(),$value,'spektrix_cron_hook');
    }

    public function AddCronSchedules($schedules){
        $schedules['fourhours'] = array(
            'interval' => 4 * HOUR_IN_SECONDS,
            'display' => __( 'Every Four Hours' )
        );
        $schedules['thricedaily'] = array(
            'interval' => 8 * HOUR_IN_SECONDS,
            'display' => __( 'Every Eight Hours' )
        );
        return $schedules;
    }

    public function UpdateTablesInstances($data){
        if($count = $data->get_param('count')){
            $count=intval($count);
        }
        if($chunk = $data->get_param('chunk')){
            $chunk=intval($chunk);
        }
        $single = $data->get_param('single');
        if($single=='true'){
            $single=true;
        }
        else{
            $single=false;
        }
         
        return $this->populate->UpdateTablesEventInstance($count,$chunk,$single);
    }

    public function UpdatePosts(){
        try{
            return $this->populate->UpdatePosts();
        }
        catch(Exception $e){
          wp_mail('chad@wearehdk.com','Spektrix Update Posts Error',$e->getMessage());
        }

    }
    
    public function UpdatePostsMeta(){
        return $this->populate->UpdatePostsMeta();
    }
  
    public function SubmitNewsletter($request){
        /*$email = $data->get_param('email');
        $firstName = $data->get_param('firstName');
        $lastName = $data->get_param('lastName');
        $Tags = $data->get_param('tags');
        $body = [
            'email'=>$email,
            'firstName'=>$firstName,
            'lastName'=>$lastName,
            'Tags'=>$Tags,
        ];*/
        return $this->form->SubmitNewsletter($request->get_json_params());
    }

    public function ProcessFormTags($request){
        return $this->form->ProcessFormTags($request);
    }

    public function DisplaySpektrixOption(){
        /*Uses REST API for Refresh Spektrix Data so we can process in chunks. See spektrix.js */
        wp_add_inline_script( 'spektrix', 'const spektrixnonce = ' . json_encode( array(
            'nonce' => wp_create_nonce( 'wp_rest' )
        ) ), 'before' );
            
        if( current_user_can( 'edit_users' ) ) { 
            //$this->populate->spektrixIDstoMeta();
            $message=get_transient('spektrix_refresh_message');
            $error=get_transient('spektrix_refresh_error');
            
                ?>
                <div id="hdk_spektrix">
                    <div class="hdk_spektrix_header_bar">
                        <h1>HdK Spektrix Manager</h1>
                        <a href="https://wearehdk.com" target="_blank" title="Powered by HdK">
                            <img src="<?php echo plugin_dir_url(HDK_SPEKTRIX_FILE) ?>image/HdK-Round-White.png" alt="HdK Logo">
                            <span class="screen-reader-text">Powered by HdK</span>
                        </a>
                    </div>
                    <?php
                    include HDK_SPEKTRIX_DIR . 'inc/partials/form-refresh-data.php';
                    //Adding these directly into code, forms not needed anymore
                    //include HDK_SPEKTRIX_DIR . 'inc/partials/form-settings.php';
                    //include HDK_SPEKTRIX_DIR . 'inc/partials/form-attributes.php';
                    //include HDK_SPEKTRIX_DIR . 'inc/partials/form-components.php';
                    if(file_exists(plugin_dir_path( __FILE__ ).'/error.txt')){
                        include HDK_SPEKTRIX_DIR . 'inc/partials/form-errors.php';
                    } ?>
                </div>
        <?php }
        else{ ?>
            <p> <?php __("You are not authorized to perform this operation.") ?> </p>
        <?php }
    }  
    
}
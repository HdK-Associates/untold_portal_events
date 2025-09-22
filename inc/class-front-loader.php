<?php
class HdKSpFrontLoader{
    public function __construct(){
        add_action('wp_footer',array($this,'LoadWebComponents'));
        add_action('wp_enqueue_scripts', array($this,'enqueue_newsletter_script'));
        add_action('wp_enqueue_scripts', array($this,'enqueue_event_scripts'));
    }

    public function LoadWebComponents(){
        $components = [
            'spektrix-donate',
            'spektrix-merchandise',
            'spektrix-memberships',
            'spektrix-gift-vouchers',
            'spektrix-login-status',
            'spektrix-basket-summary'
        ];
        $components = implode(',',$components);
        ?>
        <script src="https://webcomponents.spektrix.com/stable/webcomponents-loader.js"></script>
        <script src="https://webcomponents.spektrix.com/stable/spektrix-component-loader.js" data-components="<?php echo $components; ?>" async></script>
        <?php
        $codes = HdkSpUtilities:: get_client_codes()
        ?>
        <script
            type="text/javascript"
            src="<?php echo $codes['subdomain'].'/'.$codes['client_code']; ?>/website/scripts/integrate.js"
        ></script>
        <?php
    }

    public function enqueue_newsletter_script() {
        wp_enqueue_script( 'newsletter', plugin_dir_url( __DIR__ ). 'js/newsletter.js', array(), HDK_SPEKTRIX_VERSION, true );
        wp_enqueue_script('form_tags', plugin_dir_url( __DIR__ ). 'js/ap-sign-up.js',[], HDK_SPEKTRIX_VERSION, true);
    }

    public function enqueue_event_scripts(){
        global $post;
        if(get_post_type($post)=='event'){
            $flow = get_field('spektrix_booking_flow',$post->ID);
            if($flow=='api'){
                wp_enqueue_script('color-calendar', plugin_dir_url( __DIR__ ). 'js/color-calendar.js', [], '1.4.2', true );
                wp_enqueue_script('spektrix-calendar', plugin_dir_url( __DIR__ ) . 'js/spektrix-calendar.js', ['color-calendar'], HDK_SPEKTRIX_VERSION, true );
            }
            elseif($flow == 'seating_area_selector' ){
                wp_enqueue_script('seating-area-selector', plugin_dir_url( __DIR__ ) . 'js/seating-area-selector.js', [], HDK_SPEKTRIX_VERSION, true );
            }
            if(get_field('force_login',$post->ID)){
                wp_enqueue_script('ap-customer', plugin_dir_url( __DIR__ ) . 'js/ap-customer.js', [], HDK_SPEKTRIX_VERSION, true );
            }
        }
        if(get_post_type($post)=='page' && get_page_template_slug($post)=='admission.php'){
            wp_enqueue_script('color-calendar', plugin_dir_url( __DIR__ ). 'js/color-calendar.js', [], '1.4.2', true );
            wp_enqueue_script('spektrix-calendar', plugin_dir_url( __DIR__ ) . 'js/spektrix-calendar.js', ['color-calendar'], HDK_SPEKTRIX_VERSION, true );
        }
    }
}
<?php
class HdKSpSettings{
    private string $client_code;
    private string $stylesheet;
    private string $subdomain;
    private bool $members;
    private bool $funds;
    private bool $tags;
    private string $event_cpt;
    private int $refresh_months;
    private $attributes;

    public function __construct(){ 
        if(!isset($_ENV['SPEKTRIX_ACCOUNT_ID']) || !isset($_ENV['SPEKTRIX_ENDPOINT'])){
            throw new Exception('SPEKTRIX_ACCOUNT_ID and SPEKTRIX_ENDPOINT must be set in .env');
        }
       // add_action('admin_init',array($this,'RegisterSettings'));
        $this->client_code 		= $_ENV['SPEKTRIX_ACCOUNT_ID'];
        $this->stylesheet 		= 'spektrix-iframe-styles.css';	
        $this->subdomain		= $_ENV['SPEKTRIX_ENDPOINT'];
        $this->members 		    = true;
        $this->funds            = true;
        $this->tags    		    = true;
        $this->event_cpt        = 'event';
        $this->refresh_months   = 12;
        //Adding custom handling for attributes, so using null here
        $this->attributes       = null;
    }

    public function getSettings(){
        $settings = array(
            'client_code'           => $this->client_code,
            'stylesheet'            => $this->stylesheet,
            'subdomain'             => $this->subdomain,
            'is_members_active'     => $this->members,
            'is_funds_active'        => $this->funds,
            'is_tags_active'        => $this->tags,
            'event_cpt'             => $this->event_cpt,
            'refresh_months'        => $this->refresh_months,
            'attributes'            => $this->attributes,
        );
        return $settings;
    }

    
}
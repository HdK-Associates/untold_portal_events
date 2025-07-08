<?php 
class HdKSpInit{
    private $settings;
    private $tables;
    private HdKSpBuild $build;
    private HdKSpFrontLoader $front_loader;
    private HdKSpSettings $settingsInit;
    private HdKSpEventAdmin $event_admin;
    private HdKSpActions $actions;

    public function __construct() {

        $this->build = new HdKSpBuild;
        $this->build->createPostTypes();
        $this->build->createTables();
        $this->front_loader = new HdKSpFrontLoader;
        $this->settingsInit = new HdKSpSettings();
        $this->settings = $this->settingsInit->getSettings();
        $this->event_admin = new HdKSpEventAdmin;
        $this->actions = new HdKSpActions($this->settings);
        add_action( 'cli_init', [$this,'cli_register_commands'] );
    }

    public function cli_register_commands(){
        WP_CLI::add_command('ap_spektrix','HdKSpektrixCli');
    }
}
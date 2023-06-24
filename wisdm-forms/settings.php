<?php
if(!defined('WPINC')) {
    exit;
}


class WisdmFormSettings{
    private static $instance;
    private $settings_api;
    public static function getInstance(){
        if(self::$instance === null){
            self::$instance = new self;
            self::$instance->actions();
            self::$instance->settings_api = new WisdmForm_SettingsAPI;
        }
        return self::$instance;
    }
    
    private function actions(){
        add_action( 'admin_init', array($this, 'admin_init') );
        add_action( 'admin_menu', array($this, 'admin_menu') );
        
        //test 
        add_action('settings_form_top_eden_campaign_basics',array($this,'eden_campaign_basics'),10,1);
    }

    function admin_init() {
        //set the settings
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );

        //initialize settings
        $this->settings_api->admin_init();
    }

    function admin_menu() {
        add_submenu_page('edit.php?post_type=form', __('Settings',''), __('Settings',''), 'manage_options', 'wisdmform_settings', array($this,'settings'));
    }
    
    function settings(){
        echo '<div class="wrap w3eden">';
        echo '<div class="panel panel-primary panel-settings" style="max-width: 1000px;overflow-x:hidden;">';
        echo '<div class="panel-heading"><h3 class="panel-title">Settings</h3></div><div class="panel-body-np">';
        echo '<div class="contailer-fluid">';
        echo '<div class="row">';
        echo '<div class="col-md-3 col-sm-12 col-xs-12">';
        echo '<ul class="nav nav-pills nav-stacked">';
        $this->settings_api->section_tabs();
        echo "</ul>";
        echo '</div>';
        echo '<div class="col-md-9 col-sm-12 col-xs-12">';
        $this->settings_api->show_forms();
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    public static function get($option, $section, $default = ''){
        $sapi = new WisdmForm_SettingsAPI();
        return $sapi->get_option($option, $section, $default);
    }

    
    private function get_settings_sections() {
        $sections[] = array(
            'id' => 'wisdmform_general_settings',
            'title' => __( 'General Settings', '' )
        );
        $sections = apply_filters('wisdmform_setting_sections',$sections);
        return $sections;
    }
    
    private function get_settings_fields() {
        
        $settings_fields['wisdmform_general_settings'] = array(
            array(
                 'name' => 'google-site-key',
                 'label' => __( 'Google Site Key', '' ),
                 'desc' => __( 'Site key after registration for google captcha', '' ),
                 'type' => 'text',
                 'default' => ''
            ),
            array(
                 'name' => 'google-secret-key',
                 'label' => __( 'Google Secret Key', '' ),
                 'desc' => __( 'Secret key after registration for google captcha', '' ),
                 'type' => 'text',
                 'default' => ''
            ),
            array(
                 'name' => 'google-error-msg',
                 'label' => __( 'Captcha Error Message', '' ),
                 'desc' => __( 'Error message to be displayed in case of error', '' ),
                 'type' => 'text',
                 'default' => 'Could not be verified by captcha'
            )
        );
        
        $settings_fields = apply_filters('wisdmform_setting_fields',$settings_fields);

        return $settings_fields;
    }
    
    
    //
    
    public function eden_campaign_basics($args){
        //print_r($args);
    }
    
    
}


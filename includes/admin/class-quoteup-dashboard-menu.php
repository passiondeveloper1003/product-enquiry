<?php

namespace Includes\Admin;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/**
* This class is to create dashboard menu for quoteup.
* It also creates the screen options
 * @static $instance Object of class
*/

class QuoteUpDashboardMenu
{
    /**
     * @var Singleton The reference to *Singleton* instance of this class
     */
    private static $instance;

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
    * Action for creating dashboard menu
    * Add filters for screen options.
    */
    protected function __construct()
    {
        add_action('admin_menu', array($this, 'dashboardMenu'), 1);
        add_filter('set-screen-option', array($this, 'wdmSetScreenOption'), 10, 3);
        add_action('quoteup_pep_backend_page', array($this, 'add_beacon_helpscout_script'), 1000);
        add_action( 'admin_init', array($this, 'load_beacon_helpscout_cpt_form_edit'));
    }

    public function load_beacon_helpscout_cpt_form_edit() {
        global $pagenow, $typenow;

        // If CPT 'form' and edit.php page.
        if( 'form' == $typenow && 'edit.php' == $pagenow ) {
            /**
             * Use the action to execute some code on the PEP backend page.
             *
             * @hooked add_beacon_helpscout_script - 10
             */
        do_action('quoteup_pep_backend_page');
        }
    }

    /**
     * Add the Helpscout Beacon script on the PEP backend pages.
     * Callback to action hook 'quoteup_pep_backend_page'.
     */
    public function add_beacon_helpscout_script(){
        ?>
        <script type="text/javascript">!function(e,t,n){function a(){var e=t.getElementsByTagName("script")[0],n=t.createElement("script");n.type="text/javascript",n.async=!0,n.src="https://beacon-v2.helpscout.net",e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],"complete"===t.readyState)return a();e.attachEvent?e.attachEvent("onload",a):e.addEventListener("load",a,!1)}(window,document,window.Beacon||function(){});</script>
        <script type="text/javascript">window.Beacon('init', '941c74af-8deb-44cc-858d-d7adbd5e044e')</script>
        <?php
    }

    /**
     * Include required files and create a menu for QuoteUp.
     * Gets the unread enquiry count for displaying on the menu of PEP.
     * Add the submenus based on the condition if Quotation system is enabled or not.
     * If yes show create quote submenu also.
     * Otherwise only two submenus i.e, Enquiry details and settings.
     * @global array $quoteup_plugin_data
     */
    public function dashboardMenu()
    {
        global $quoteup;
        require_once QUOTEUP_PLUGIN_DIR.'/includes/admin/listing/class-quoteup-quotes-list.php';
        require_once QUOTEUP_PLUGIN_DIR.'/includes/admin/listing/class-quoteup-enquiries-list.php';
        // $getDataFromDb = \Licensing\WdmLicense::checkLicenseAvailiblity('pep', false);
        $optionData = quoteupSettings();
        $cap_manage_woocommerce = 'manage_woocommerce';
        $capablity = apply_filters('quoteup_quote_enquiry_capablity', $cap_manage_woocommerce);
        $unreadEnquiryCount = $this->getUnreadEnquiryCount();

        if ($unreadEnquiryCount > 0) {
            $menuName = sprintf(__('Product Enquiry Pro %s %s %s', QUOTEUP_TEXT_DOMAIN), '<span class="unread-enquiry-count update-plugins"><span class="enquiry-count">', $unreadEnquiryCount, '</span></span>');
        } else {
            $menuName = __('Product Enquiry Pro', QUOTEUP_TEXT_DOMAIN);
            if (!$this->isWhatsNewTabVisited()) {
                $menuName .= '<span class="pep-update-dot"></span>';
            }
        }
        add_menu_page(__('QuoteUp', QUOTEUP_TEXT_DOMAIN), $menuName, $capablity, 'quoteup-details-new', array($quoteup->quoteDetails, 'displayQuoteDetails'), QUOTEUP_PLUGIN_URL.'/images/pep.png', '56');
        add_submenu_page('admin.php?page=quoteup-details-edit', __('Edit Enquiry', QUOTEUP_TEXT_DOMAIN), __('Quote Details', QUOTEUP_TEXT_DOMAIN), $capablity, 'quoteup-details-edit', array($quoteup->quoteDetailsEdit, 'editQuoteDetails'));
        if (isset($optionData['enable_disable_quote']) && $optionData['enable_disable_quote'] == 1) {
            $menu = add_submenu_page('quoteup-details-new', __('Enquiry Details', QUOTEUP_TEXT_DOMAIN), __('Enquiry Details', QUOTEUP_TEXT_DOMAIN), $capablity, 'quoteup-details-new', array($quoteup->quoteDetails, 'displayQuoteDetails'));
        } else {
            $menu = add_submenu_page('quoteup-details-new', __('Enquiry & Quote Details', QUOTEUP_TEXT_DOMAIN), __('Enquiry & Quote Details', QUOTEUP_TEXT_DOMAIN), $capablity, 'quoteup-details-new', array($quoteup->quoteDetails, 'displayQuoteDetails'));
            $capablity = apply_filters('quoteup_create_new_quote_capabilities', $cap_manage_woocommerce);
            add_submenu_page('quoteup-details-new', __('Create New Quote', QUOTEUP_TEXT_DOMAIN), __('Create New Quote', QUOTEUP_TEXT_DOMAIN), $capablity, 'quoteup-create-quote', array($quoteup->quoteCreateQuotation, 'createDashboardQuotation'));
        }

        $capablity = apply_filters('quoteup_form_capabilities', $cap_manage_woocommerce);
        add_submenu_page('quoteup-details-new', __('Forms', QUOTEUP_TEXT_DOMAIN), __('Forms', QUOTEUP_TEXT_DOMAIN), $capablity, 'edit.php?post_type=form', null);
        add_action("load-{$menu}", array($this, 'menuActionLoadHook'));
        /**
         * Add custom menus after PEP 'Forms' menu.
         */
        do_action('quoteup_dashboard_menu');

        $capablity = apply_filters('quoteup_settings_capabilities', $cap_manage_woocommerce);
        $settingsMenu = __('Settings', QUOTEUP_TEXT_DOMAIN);
        if (!$this->isWhatsNewTabVisited()) {
            $settingsMenu .= '<span class="pep-update-dot"></span>';
        }
        add_submenu_page('quoteup-details-new', __('QuoteUp Settings', QUOTEUP_TEXT_DOMAIN), $settingsMenu, $capablity, 'quoteup-for-woocommerce', array($quoteup->displaySettingsPage, 'displaySettings'));
        add_action('admin_enqueue_scripts', array($quoteup->displaySettingsPage, 'enqueueScripts'));
    }
    /**
    * This function gets the unread enquiry count from meta table.
    * @return the count of unread enquiries
    */
    public function getUnreadEnquiryCount()
    {
        global $wpdb;
        $metaTbl = getEnquiryMetaTable();
        $sql     = "SELECT COUNT(enquiry_id) FROM $metaTbl WHERE meta_key = '_unread_enquiry' AND meta_value = 'yes'";
        $sql     = apply_filters('quoteup_unread_enquiry_count_query', $sql);
        $result  = $wpdb->get_var($sql);

        return $result;
    }

    /**
     * This function is used to load data for quotes and enquiries.
     * Adds screen option on the page.
     * @return [type] [description]
     */
    public function menuActionLoadHook()
    {
        global $quoteupQuotesList,$quoteupEnquiriesList;
        $optionData = quoteupSettings();
        if (isset($optionData['enable_disable_quote']) && $optionData['enable_disable_quote'] == 1) {
            $quoteupEnquiriesList = new QuoteupEnquiriesList();
        } else {
            $quoteupQuotesList = new QuoteupQuotesList();
        }
        $option = 'per_page';
 
        $args = array(
            'label' => __('Number of items per page : ', QUOTEUP_TEXT_DOMAIN),
            'default' => 10,
            'option' => 'request_per_page'
        );
         
        add_screen_option($option, $args);
    }
 
    /**
    * Setting the Screen option for the page.
    * @param string $status
    * @param string $option screen option
    * @param $value
    */
    public function wdmSetScreenOption($status, $option, $value)
    {
        if ('request_per_page' == $option) {
            return $value;
        }
     
        return $status;
    }

    /**
     * Check whether What's New tab has been visited or not.
     * Return true if What's New tab has been visited, false otherwise.
     *
     * @return bool     Return true if What's New tab has been visited,
     *                  false otherwise.
     */
    public function isWhatsNewTabVisited()
    {
        $isWhatsNewTabVisited = false;
        $get_plugin_version   = get_option('wdm_quoteup_version', false);

        if (false == $get_plugin_version || QUOTEUP_VERSION != $get_plugin_version) {
            $isWhatsNewTabVisited = false;
            delete_option('is_whats_new_tab_visited');
        } elseif ('yes' == get_option('is_whats_new_tab_visited', false)) {
            $isWhatsNewTabVisited = true;
        }

        return $isWhatsNewTabVisited;
    }
}
QuoteUpDashboardMenu::getInstance();

<?php

namespace Includes\Settings;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
* This class handles the settings of QuoteUp.
* Registers and displays the settings
*/
class QuoteupSettings
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
    * Action for register setting
    */
    protected function __construct()
    {
        add_action('admin_init', array($this, 'registerSetting'));
    }
    /**
    * Quoteup new settings for the form
    * @param array $newValue new value of settings
    */
    public function quoteupSanitizeSettings($newValue)
    {
        global $quoteup_settings_status;

        // get the old value
        $oldValue = get_option('wdm_form_data');

        if ('custom' == $newValue['enquiry_form'] && (!isset($newValue['custom_formId']) || empty($newValue['custom_formId']))) {
            $quoteup_settings_status = false;
            add_settings_error('wdm_form_data', 'default', 'Please select custom form', 'error');
            return $oldValue;
        } else {
            /*
             * Remove all quoteup sessions from options table, if MPE was
             * enabled earlier and now it is disabled.
             */
            if (quoteupIsMPEEnabled($oldValue) && !quoteupIsMPEEnabled($newValue)) {
                global $quoteup;
                $quoteup->wcCartSession->unsetAllSessions();
            }
 
            $quoteup_settings_status = true;

            /**
             * Use the filter to modify the settings if required.
             *
             * @since 6.5.0
             *
             * @param   array   $newValue   New settings which will be saved in
             *                              the database.
             * @param   array   $oldValue   Previous old settings which will be
             *                              replaced with the new settings in database.
             */
            $newValue = apply_filters('quoteup_sanitize_settings', $newValue, $oldValue);
            return $newValue;
        }
    }
    /**
    * Register the setting having some handle
    */
    public function registerSetting()
    {
        global $wp_version;
        if (version_compare($wp_version, '4.7', '<')) {
            register_setting('wdm_form_options', 'wdm_form_data');
        } else {
            register_setting('wdm_form_options', 'wdm_form_data', array('sanitize_callback' => array($this, 'quoteupSanitizeSettings')));
        }
    }

    /**
    * Enqueue scripts for the specific hook
    * @param string $hook specific hook
    */
    public function enqueueScripts($hook)
    {
        if (strpos($hook, '_page_quoteup-for-woocommerce') === false) {
            return;
        }

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        //Default CSS Of woocommerce
        wp_enqueue_style('woocommerce_admin_styles', plugin_dir_url(dirname(dirname(dirname(__FILE__)))).'woocommerce/assets/css/admin.css', array(), filemtime(QUOTEUP_WC_PLUGIN_DIR.'/assets/css/admin.css'));
        wp_enqueue_style('quoteup_db_style', QUOTEUP_PLUGIN_URL.'/css/admin/settings.css', array(), filemtime(QUOTEUP_PLUGIN_DIR.'/css/admin/settings.css'));
        wp_enqueue_style('quoteup-whats-new-tab-css', QUOTEUP_PLUGIN_URL.'/css/admin/whats-new-tab.css', array(), filemtime(QUOTEUP_PLUGIN_DIR.'/css/admin/whats-new-tab.css'));
        wp_enqueue_style('quoteup-feedback-css', QUOTEUP_PLUGIN_URL.'/css/admin/feedback.css', array(), filemtime(QUOTEUP_PLUGIN_DIR.'/css/admin/feedback.css'));
        wp_enqueue_script('tabs', QUOTEUP_PLUGIN_URL.'/js/admin/jquery.easytabs.min.js', array('jquery'), filemtime(QUOTEUP_PLUGIN_DIR.'/js/admin/jquery.easytabs.min.js'));
        wp_enqueue_script('jquery-tiptip');
        wp_enqueue_script('quoteup-settings', QUOTEUP_PLUGIN_URL.'/js/admin/settings.js', array('jquery', 'wp-color-picker'), filemtime(QUOTEUP_PLUGIN_DIR.'/js/admin/settings.js'));

        wp_localize_script(
            'quoteup-settings',
            'data',
            array(
            'name_req' => __('Please enter email address', QUOTEUP_TEXT_DOMAIN),
            'valid_name' => __('Please enter valid email address. Multiple email addresses must be comma separated.', QUOTEUP_TEXT_DOMAIN),
            'ajax_admin_url' => admin_url('admin-ajax.php'),
            'could_not_migrate_enquiries' => __('Could not migrate enquiries due to security issue.', QUOTEUP_TEXT_DOMAIN),
            'enable' => __('Enable', QUOTEUP_TEXT_DOMAIN),
            'disable' => __('Disable', QUOTEUP_TEXT_DOMAIN),
            'quoteup_for_all_products' => __('enquiry system for all products', QUOTEUP_TEXT_DOMAIN),
            'add_to_cart_for_all_products' => __('add to cart for all products', QUOTEUP_TEXT_DOMAIN),
            'price_for_all_products' => __('price for all products', QUOTEUP_TEXT_DOMAIN),
            'show' => __('Show', QUOTEUP_TEXT_DOMAIN),
            'hide' => __('Hide', QUOTEUP_TEXT_DOMAIN),
            'applying_global_settings' => __('Applying Global Settings will do following things', QUOTEUP_TEXT_DOMAIN),
            'vendor_compatibility_msg' => __('PEP will work with Vendors Plugin, only when it is set to Single Product Enquiry Mode. Would you like to change the Multi Product Enquiry Mode to Single Product Enquiry Mode?', QUOTEUP_TEXT_DOMAIN),
            'show_yes' => __('Yes', QUOTEUP_TEXT_DOMAIN),
            'show_no' => __('No', QUOTEUP_TEXT_DOMAIN),
            )
        );

        // This function loads in the required media files for the media manager.
        wp_enqueue_media();

        // Register, localize and enqueue our custom JS.
        wp_register_script('tgm-nmp-media', QUOTEUP_PLUGIN_URL.'/js/admin/media-uploader.js', array('jquery'), filemtime(QUOTEUP_PLUGIN_DIR.'/js/admin/media-uploader.js'));
        wp_localize_script(
            'tgm-nmp-media',
            'tgm_nmp_media',
            array(
            'title' => __('Upload or Choose Your Custom Image File', QUOTEUP_TEXT_DOMAIN), // This will be used as the default title
            'button' => __('Insert Image into Input Field', QUOTEUP_TEXT_DOMAIN),   // This will be used as the default button text
            )
        );
        wp_enqueue_script('tgm-nmp-media');

        wp_register_style('extensions-promotion', QUOTEUP_PLUGIN_URL.'/css/admin/extension.css', array(), filemtime(QUOTEUP_PLUGIN_DIR.'/css/admin/extension.css'));

        // Enqueue admin styles
        wp_enqueue_style('extensions-promotion');
        ;
    }

    /*
    * This function is used to display Settings
    */
    public function displaySettings()
    {
        global $quoteup_settings_status;
        settings_errors();
        include_once 'quoteup-general.php';
        include_once 'quoteup-email.php';
        include_once 'quoteup-display.php';
        include_once 'settings-tabs.php';
        include_once 'quoteup-quote.php';
        include_once 'quoteup-mini-cart.php';
        include_once 'quoteup-form.php';
        include_once 'quoteup-other-extensions.php';
        include_once 'quoteup-whats-new.php';
        include_once 'quoteup-feedback.php';
        ?>
        <!--dashboard settings design-->
        <div class='wrap'>

        <?php
        if ($_SERVER[ 'QUERY_STRING' ] == 'page=quoteup-for-woocommerce&settings-updated=true' && $quoteup_settings_status) {
            echo '<div id="save_notice" class="updated settings-error notice is-dismissible">';
            echo '<p><strong>'.__('Settings Saved', QUOTEUP_TEXT_DOMAIN).'</strong></p>';
            echo '</div>';
        }
        ?>
            <form name="ask_product_form" id="ask_product_form" class="form-table" method="POST" action="options.php">
                <?php
                settings_fields('wdm_form_options');
                $default_vals = array('after_add_cart' => 1,
                    'button_CSS' => 'theme_css',
                    'pos_radio' => 'after_add_cart',
                    'show_powered_by_link' => 0,
                    'enable_send_mail_copy' => 0,
                    'only_if_out_of_stock' => 0,
                );
                $form_data = '';
                $form_data = get_option('wdm_form_data', $default_vals);
                ?>
                <div id="tab-container" class="tab-container">
                    <?php
                    settingsTabs($form_data);
                    generalSettings($form_data);
                    emailSettings($form_data);
                    displaySettings($form_data);
                    quoteSettings($form_data);
                    miniCartSettings($form_data);
                    formSettings($form_data);
                    otherExtensionsSettings($form_data);
                    quoteupWhatsNewTab($form_data);
                    quoteupShowSideBarFeedback($form_data);
                    do_action('quoteup_add_product_enquiry_tab_content', $form_data);
                    do_action('wdm_pep_add_product_enquiry_tab_content', $form_data);
                    ?>
                </div>
                <input type="submit" class="wdm_wpi_input button-primary" value="<?php _e('Save changes', QUOTEUP_TEXT_DOMAIN) ?>" id="wdm_ask_button" />

            </form>
        </div>
            <?php
    }
}

$this->displaySettingsPage = QuoteupSettings::getInstance();

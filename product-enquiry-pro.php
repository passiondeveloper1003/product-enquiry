<?php

namespace Quoteup;

/*
* Plugin Name:    Product Enquiry Pro for WooCommerce (A.K.A QuoteUp)
* Description:    Allows prospective customers to make enquiry about a WooCommerce product.        Analyze product demands right from your dashboard.
* Version:        6.5.4
* Author:         WisdmLabs
* Author URI:     https://wisdmlabs.com/
* Plugin URI:     https://wisdmlabs.com/woocommerce-product-enquiry-pro/
* License:        GPL
* Text Domain:    quoteup
* WC requires at least: 3.0.0
* WC tested up to: 7.6.1
*/

/**
 * This file's name has underscore because making it (-) spaced will create an issue for existing users.
 * Plugin won't be activated automatically for them.
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once 'quoteup-functions.php';

/**
 * https://developer.wordpress.org/reference/functions/load_plugin_textdomain/
 * load_plugin_textdomain() should be loaded on 'init' hook. It shouldn't be
 * loaded early.
 *
 * file is required for the general settings to be fetched which is set by the user.
 * checks if the WPML is active to get current lamguage and change it if required by user.
 * This is done with help of WPML.
 */
// add_action('init', function(){
//     load_plugin_textdomain('quoteup', false, dirname(plugin_basename(__FILE__)).'/languages/');
// });

if (!defined('QUOTEUP_PLUGIN_DIR')) {
    define('QUOTEUP_PLUGIN_DIR', quoteupPluginDir());
}

if (!defined('PEP_PLUGIN_BASENAME')) {
    define('PEP_PLUGIN_BASENAME', plugin_basename(__FILE__));
}

if (!defined('QUOTEUP_WC_PLUGIN_DIR')) {
    define('QUOTEUP_WC_PLUGIN_DIR', quoteupWcPluginDir());
}

if (!defined('QUOTEUP_PLUGIN_URL')) {
    define('QUOTEUP_PLUGIN_URL', quoteupPluginUrl());
}

if (!defined('QUOTEUP_VENDOR_DIR')) {
    define('QUOTEUP_VENDOR_DIR', quoteupVendorDir());
}

if (!defined('MPDF_ALL_FONTS_URL')) {
    define('MPDF_ALL_FONTS_URL', 'https://wisdmlabs.com/all-fonts/');
}

if (!defined('QUOTEUP_VERSION')) {
    define('QUOTEUP_VERSION', '6.5.4');
}

if (!defined('EDD_WPEP_STORE_URL')) {
    define('EDD_WPEP_STORE_URL', 'https://wisdmlabs.com/license-check/');
}
if (!defined('EDD_WPEP_ITEM_NAME')) {
    define('EDD_WPEP_ITEM_NAME', 'Product Enquiry Pro');
}

if (!defined('QUOTEUP_TEXT_DOMAIN')) {
    define('QUOTEUP_TEXT_DOMAIN', 'quoteup');
}

/*
* This file manages the function related to product functions , variation, searching product, etc.
*/
require_once 'quoteup-products-functions.php';
require_once 'wisdm-forms/wisdmforms.php';

if (!defined('REPLACE_QUOTE')) {
    $form_data = quoteupSettings();
    define('REPLACE_QUOTE', isset($form_data['replace_quote']) ? $form_data['replace_quote'] : '');
}

/*
 * @global array Plugin data used throughout the plugin for various purposes
 */
$quoteup_plugin_data = array();

/*
*create the necessary tables required.
*first checks if the table already exists or not and then creates the database in the WordPress.
*provides an option (through filters)for adding custom columns for the quoteup db tables and create the tables accordingly.
*provides a do_action with some hooks so that some action can be added before and after creating tables if required in future.
*sets the transient for the licensing of the Plugin with a definite expiration time.
*sets the Cron job for deleting the old pdfs and expired quotes.
*/
require_once QUOTEUP_PLUGIN_DIR.'/install.php';
/**
 * This file deals with deleting PDF created before more than 1 hour using cron job.
 */
require_once QUOTEUP_PLUGIN_DIR.'/delete-old-pdfs.php';
/**
 * This file sets quote expired who meet the expiration date.
 */
require_once QUOTEUP_PLUGIN_DIR.'/expire-quotes.php';
/*
* Handles all the ajax calls in admin and front-end side.
*/
require_once QUOTEUP_PLUGIN_DIR.'/ajax.php';
/*
* Redirects to new admin links
*/
require_once QUOTEUP_PLUGIN_DIR.'/quoteup-redirect-admin-links.php';

/**
 * @global boolean $quoteup_enough_stock After approving the quote by customer,
 * if any of the product does not have enough stock, then this variable is set
 * to false. When this is set to false, no products are added in the WooCommerce's cart
 * after approving the Quote.
 */
$quoteup_enough_stock = true;

/**
 * @global int $quoteup_enough_stock_product_id Holds the product id which does
 * not have enough stock
 */
$quoteup_enough_stock_product_id = 0;

/**
 * @global string $quoteup_enough_stock_variation_details Holds the variation details
 * of the product which is out of stock
 */
$quoteup_enough_stock_variation_details = '';

/**
 * @global boolean $decideDisplayQuoteButton before displaying the quote button if
 */
$decideDisplayQuoteButton = true;

/**
* This class final QuoteUp so that it cannot be extended and its method do not get overridden.
* This class is responsible for the Checking the Dependancies.
* It triggers the installation of the QuoteUp.
* It checks the License key and redirects to settings if valid.
*/
if (!class_exists('QuoteUp')) {
    final class QuoteUp
    {
        /**
         * Set to false when dependencies required to run plugin are not fulfilled.
         *
         * @var bool
         */
        private $dependenciesFullfilled = true;
        protected static $instance = null;

        /**
         * Function to create a singleton instance of class and return the same.
         *
         * @return [Object] Singleton instance of QuoteUp class.
         */
        public static function getInstance()
        {
            if (!self::$instance) {
                self::$instance = new self();
            }

            return self::$instance;
        }
        /**
         * This function checks the dependancies for PEP Plugin.
         * If fulfilled triggers installation.
         * checks the license and checks the update of PEP.
         * Loads the WPML Compatability ,text domain.
         * Includes the files needed.
         * Destroys session if logout
         * Enqueue styles for dashboard.
         * Deactivates the free plugin for woocommerce enquiry.
         */
        private function __construct()
        {
            $this->dependenciesFullfilled = $this->checkDependencies();

            if ($this->dependenciesFullfilled) {
                $this->triggerInstallation();
                $this->bootstrapEmailSender();
                $this->includeSetupWizardFiles();
                add_action('plugins_loaded', array($this, 'loadLicense'), 5);
                add_action('init', array($this, 'loadTextDomain'));
                add_action('init', array($this, 'loadWPMLCompatibility'), 9);
                add_action('init', array($this, 'includeFiles'));
                add_action('wp_logout', array($this, 'destroySession'));
                add_action('wp_login', array($this, 'destroySession'));
            }

            add_action('admin_init', array($this, 'deactivatePlugins'));
            add_action('admin_enqueue_scripts', array( $this, 'adminMenuStyles' ));
        }

        public function loadLicense()
        {
            global $quoteup_plugin_data;
            $quoteup_plugin_data = include_once 'license.config.php';
            require_once QUOTEUP_PLUGIN_DIR.'/licensing/class-wdm-license.php';
            $wdmLicense = new \Licensing\WdmLicense($quoteup_plugin_data);
            unset($wdmLicense);
        }

        /**
         * Check if dependencies are fullfilled.
         *
         * @since 6.5.1
         *
         * @return  bool  True if dependencies are fullfilled, false otherwise.
         */
        public function getDependenciesFullfilledStatus() {
            return $this->dependenciesFullfilled;
        }

        /**
         * Enqueue styles for dashboard menu
         */
        public function adminMenuStyles()
        {
            wp_enqueue_style('pep-menu-css', QUOTEUP_PLUGIN_URL.'/css/admin/menu.css', array(), filemtime(QUOTEUP_PLUGIN_DIR.'/css/admin/menu.css'));
        }


        /**
         * This function is used to check dependencies
         * Dependencies : Woocommerce active or not.
         * : Php Version more than 5.3.0
         *
         * @return boolean true if dependancies fulfilled.
         */
        private function checkDependencies()
        {
            $dependenciesFullfilled = true;

            if (!function_exists('is_plugin_active')) {
                require_once(ABSPATH . 'wp-admin/includes/plugin.php');
            }

            if (function_exists('phpversion')) {
                $phpVersion = phpversion();
            } elseif (defined('PHP_VERSION')) {
                $phpVersion = PHP_VERSION;
            }

            if (version_compare($phpVersion, '5.3.0', '<')) {
                $dependenciesFullfilled = false;
            }

            if (!is_plugin_active('woocommerce/woocommerce.php')) {
                $dependenciesFullfilled = false;
            }

            return $dependenciesFullfilled;
        }

        private function triggerInstallation()
        {
            /*
             * ***********************************
             *
             * Flow is like this after installation:
             *  1.  Create these tables: wp_enquiry_detail_new, wp_enquiry_thread, wp_enquiry_quotation, wp_enquiry_history,
             *  2.  Create folder QuoteUp_PDF in uploads directory
             *  3.  Convert Multiple Product Enquiry Settings Dropdown to Checkbox because in versions before 4.0 'Enable Multiproduct Enquiry and Quote Request' setting was dropdown
             *  4.  Set option quoteup_settings_convert_to_checkbox to 1 because conversion of dropdown to checkbox of settings is done.
             *  5.  Convert dropdown 'Enable Enquiry Button' shown on single product (which were there before 4.0) to checkbox for all existing products
             *  6.  Set 'quoteup_convert_per_product_pep_dropdown' to 1 which confirms 'Enable Enquiry Button' dropdown is converted to checkbox
             *  7.  Convert dropdown 'Show Add to Cart button' shown on single product to checkbox for all existing products.
             *  8.  Set 'quoteup_convert_per_product_add_to_cart_dropdown' to 1 which confirms 'Enable Enquiry Button' dropdown is converted to checkbox
             *  9.  Set _enable_add_to_cart, _enable_price, _enable_pep meta fields to all those products who does not have those meta fields set. On fresh install, these meta fields are set to all products.
             *  10. Change order history messages from present tense to past tense.
             *  11. Set quoteup_convert_history_status to 1 which confirms that all history statuses have been changed
             *  12. Change all checkboxes on settings page to 1/0. They are 1/unavailable before this transformation
             *  13. Set default settings to newly introduced configuration.
             *  14. Add new QuoteUp version number in database
             *
             * Steps to be performed on new installation: 1, 2, 9, 12, 13
             *
             * *************************************
             */
            //Hooks which creates all the necessary tables.
            register_activation_hook(__FILE__, 'quoteupCreateTables');

            //Hooks which updates MPE Settings from yes to 1
            register_activation_hook(__FILE__, 'quoteupConvertMpeSettings');

            //Hook which toggles _disable_quoteup to _enable_quoteup.
            register_activation_hook(__FILE__, 'quoteupTogglePerProductDisablePepSettings');

            //Hook which toggles _disable_quoteup to _enable_quoteup.
            register_activation_hook(__FILE__, 'quoteupConvertPerProductAddToCart');

            //Hook which sets the per-product 'Add To Cart' settings on activation.
            register_activation_hook(__FILE__, 'quoteupSetAddToCartPepPriceOnActivation');

            //Hooks which updated history of previous enquiries.
            register_activation_hook(__FILE__, 'quoteupUpdateHistoryStatus');

            //Hooks converts checkboxes from 1/unavailable to 1/0.
            register_activation_hook(__FILE__, 'quoteupConvertOldCheckboxes');

            //Set Default settings
            register_activation_hook(__FILE__, 'quoteupSetDefaultSettings');

            // Hook for migrating oldProduct details in new enquiry_products table
            register_activation_hook(__FILE__, 'quoteupMigrateProductDetails');

            // Hook for cron job to delete PDF
            register_activation_hook(__FILE__, 'quoteupCreateCronJobs');

            // Sets the default privacy anonymize settings.
            register_activation_hook(__FILE__, 'quoteupSetPrivacyAnonymizeSettings');

            //Updates new database version in database
            register_activation_hook(__FILE__, 'quoteupUpdateVersionInDb');
        }

        /**
         * This function includes file to send mail
         */
        public function bootstrapEmailSender()
        {
            //Defines a class QuoteupEmail which is used to trigger to send emails
            require_once QUOTEUP_PLUGIN_DIR.'/includes/class-quoteup-email.php';
        }

        /**
         * Include Setup Wizard files.
         *
         * @since 6.5.0
         */
        public function includeSetupWizardFiles()
        {
            // Include Setup Wizard file.
            require_once QUOTEUP_PLUGIN_DIR.'/includes/admin/setup-wizard/wisdm-setup/class-wisdm-setup-wizard.php';

            // Include Quoteup Setup Wizard file.
            require_once QUOTEUP_PLUGIN_DIR.'/includes/admin/setup-wizard/class-quoteup-setup-wizard.php';
        }

        /**
         * This file includes file for WPML compatiblity
         */
        public function loadWPMLCompatibility()
        {
            //Includes all required files for plugin
            if (file_exists(QUOTEUP_PLUGIN_DIR.'/includes/class-quoteup-wpml-compatibility.php')) {
                require_once QUOTEUP_PLUGIN_DIR.'/includes/class-quoteup-wpml-compatibility.php';
            }
        }

        /**
         * Used to load text domain
         */
        public function loadTextDomain()
        {
            load_plugin_textdomain('quoteup', false, dirname(plugin_basename(__FILE__)).'/languages/');
        }

        /**
         * If WooCommerce is not active, deactivates itself.
         * Also checks if Free plugin is active and if it is, deactivates the free plugin.
         */
        public function deactivatePlugins()
        {
            if (!is_plugin_active('woocommerce/woocommerce.php')) {
                deactivate_plugins(plugin_basename(__FILE__));
                unset($_GET['activate']);
                //Display notice that plugin is deactivated because WooCommerce is not activated.
                add_action(
                    'admin_notices',
                    array(
                    $this,
                        'wcNotActiveNotice',
                    )
                );

                return;
            }

            if (is_plugin_active('product-enquiry-for-woocommerce/product-enquiry-for-woocommerce.php')) {
                deactivate_plugins('product-enquiry-for-woocommerce/product-enquiry-for-woocommerce.php');
            }
        }

        /**
         * Notice if woocommerce is not active
         */
        public function wcNotActiveNotice()
        {
            ?>
            <div class='error'>
                <p>
                    <?php echo __('WooCommerce plugin is not active. In order to make the Product Enquiry Pro for WooCommerce (A.K.A QuoteUp) plugin work, you need to install and activate WooCommerce first.', QUOTEUP_TEXT_DOMAIN); ?>
                </p>
            </div>
            <?php
        }

        /*
         * This function includes required files
         */
        public function includeFiles()
        {
            //Includes required files
            require_once QUOTEUP_PLUGIN_DIR.'/file-includes.php';
        }

        /**
         * This function destroys session
         */
        public function destroySession()
        {
            if (!isset($_SESSION)) {
                @session_start();
            }
            @session_destroy();
        }
    }
}
$GLOBALS['quoteup'] = QuoteUp::getInstance();

add_action('admin_enqueue_scripts', 'quoteup\quoteupEnqueueScriptsStyles', 10, 1);

/**
 * This function Enqueue styles and scripts on individual product create/edit screen
 * Js file is to check If enable price is checked then only provide option of Add to Cart.
 *
 * @param string $hook specific hook for enqueueing script
 */
function quoteupEnqueueScriptsStyles($hook)
{
    $screen = get_current_screen();
    if (($hook == 'post.php' || $hook == 'post-new.php') && $screen->id == 'product') {
        wp_enqueue_script(
            'price-add-to-cart-relation',
            QUOTEUP_PLUGIN_URL . '/js/admin/single-product.js',
            array(
                'jquery',
            ),
            filemtime(QUOTEUP_PLUGIN_DIR . '/js/admin/single-product.js')
        );
        wp_enqueue_style('wdm_style_for_individual_product_screen', QUOTEUP_PLUGIN_URL.'/css/admin/dashboard-single-product.css', array(), filemtime(QUOTEUP_PLUGIN_DIR . '/css/admin/dashboard-single-product.css'), false);
    }
}

//Action when user do not have admin access.
// add_action('admin_page_access_denied', 'Quoteup\pluginSettingsLinkError');

// Display the admin notification
// add_action('admin_notices', 'Quoteup\showNoticePEPActivation');
/**
 * This function is to check License when PEP is activated.
 * If license is not valid or expired then give the error message.
 */
function showNoticePEPActivation()
{
    $activeFlag = is_plugin_active('product-enquiry-pro/product-enquiry-pro.php');
    if (!$activeFlag || strpos($_SERVER['REQUEST_URI'], 'admin.php?page=wisdmlabs-licenses')) {
        return;
    }
    $quoteup_lic_stat = get_option('edd_pep_license_status');

    if ($quoteup_lic_stat != 'valid' && $quoteup_lic_stat != 'expired') {
            $message = '<div class="error">';
            $message .= '<p>';
            $message .= sprintf(__('Please enter license key %1$s here %2$s to activate Product Enquiry Pro.', QUOTEUP_TEXT_DOMAIN), '<a href="admin.php?page=wisdmlabs-licenses">', '</a>');
            $message .= '</p>';
            $message .= '</div><!-- /.error -->';
            echo $message;

            return;
    }

    if ($quoteup_lic_stat != 'valid' && $quoteup_lic_stat != 'expired') {
        return;
    }
}

<?php

namespace Includes\MailChimp;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * MailChimp Integration
 *
 * Include files required for MailChimp integration.
 *
 * @since 6.5.0
 */
class MailChimpIntegration
{
    /**
     * The reference to *Singleton* instance of this class.
     *
     * @var Singleton
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
     * Include the files if MailChimp is configured.
     */
    private function __construct()
    {
        if (mailchimp_is_configured()) {
            $this->includeFiles();
        }
    }

    /**
     * Include the files.
     *
     * @since 6.5.0
     *
     * @return  void
     */
    function includeFiles()
    {
        require_once QUOTEUP_PLUGIN_DIR.'/includes/mailchimp/quoteup-mailchimp-functions.php';

        if (!is_admin() || defined('DOING_AJAX')) {
            // Include Frontend and Ajax handling file
            require_once QUOTEUP_PLUGIN_DIR.'/includes/mailchimp/public/class-quoteup-mailchimp-checkbox.php';
        } else {
            // Include backend file
            global $pagenow;

            if ('admin.php' == $pagenow && 'quoteup-for-woocommerce' == $_GET['page']) {
                require_once QUOTEUP_PLUGIN_DIR.'/includes/mailchimp/admin/mailchimp-options.php';
            }
        }
    }
}

MailChimpIntegration::getInstance();

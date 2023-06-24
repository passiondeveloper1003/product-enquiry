<?php

namespace Includes\Frontend;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/**
 * Handles the cart activities on frontend part.
* @static instance Object of class
 */
class QuoteupManageSession
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
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     * requires library files of wordpress for managing session
     * Adds filter for setting expiration time.
     */
    protected function __construct()
    {
        if (!defined('QUOTEUP_SESSION_COOKIE')) {
            define('QUOTEUP_SESSION_COOKIE', 'quoteup_wp_session');
        }

        if (!class_exists('\\Includes\\Frontend\\Libraries\\RecursiveArrayAccess')) {
            require_once QUOTEUP_PLUGIN_DIR.'/includes/public/libraries/class-recursive-arrayaccess.php';
        }
        if (!class_exists('\\Includes\\Frontend\\Libraries\\WpSession')) {
            require_once QUOTEUP_PLUGIN_DIR.'/includes/public/libraries/class-wp-session.php';
            require_once QUOTEUP_PLUGIN_DIR.'/includes/public/libraries/wp-session.php';
        }
        add_filter('quoteup_session_expiration_variant', array($this, 'setExpirationVariantTime'), 99999);
        add_filter('quoteup_session_expiration', array($this, 'setExpirationTime'), 99999);
        add_filter('quoteup_create_enquiry_session', array($this, 'shouldQuoteupWpSessionBeCreated'));
        add_action('init', array($this, 'init'), 15);
        add_action('quoteup_remove_enq_session', array($this, 'removeQuoteupWpSession'));
        add_action('send_headers', array($this, 'checkAndRemoveSession'), 999);
    }

    /**
     * Setup the WpSession instance.
     *
     * @since 1.5
     * @return object current session
     */
    public function init()
    {
        $this->session = \Includes\Frontend\Libraries\WpSession::get_instance();

        return $this->session;
    }

    /**
     * Retrieve session ID.
     *
     * @since 1.6
     *
     * @return string Session ID
     */
    public function getID()
    {
        return $this->session->session_id;
    }
    /**
     * Retrieve a session variable.
     *
     * @since 1.5
     *
     * @param string $key Session key
     *
     * @return string Session variable
     */
    public function get($key)
    {
        $key = sanitize_key($key);

        return isset($this->session[ $key ]) ? maybe_unserialize($this->session[ $key ]) : '';
    }
    /**
     * Set a session variable.
     *
     * @since 1.5
     *
     * @param string $key   Session key
     * @param int    $value Session variable
     *
     * @return string Session variable
     */
    public function set($key, $value)
    {
        $key = sanitize_key($key);
        if (is_array($value)) {
            $this->session[ $key ] = serialize($value);
        } else {
            $this->session[ $key ] = $value;
        }

        return $this->session[ $key ];
    }

    /*
     * This function is used to unset session
     */
    public function unsetSession()
    {
        \Includes\Frontend\Libraries\wp_session_regenerate_id(true);
    }

    /**
     * This function is used to unset/ remove all quoteup sessions from options table.
     */
    public function unsetAllSessions()
    {
        global $wpdb;
        do_action('quoteup_before_removing_sessions_options_table');
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%_wp_session_quoteup_%'");
        do_action('quoteup_after_removing_sessions_options_table');
    }

    /**
     * Force the cookie expiration variant time to 1 hour.
     *
     * @since 2.0
     *
     * @param int $exp Default expiration (1 hour)
     *
     * @return int time for expiration
     */
    public function setExpirationVariantTime($exp)
    {
        unset($exp);

        return 3600;
    }
    /**
     * Force the cookie expiration time to 1 hour.
     *
     * @since 1.9
     *
     * @param int $exp Default expiration (1 hour)
     *
     * @return int time for expiration
     */
    public function setExpirationTime($exp)
    {
        return $this->setExpirationVariantTime($exp);
    }

    /**
     * Decides whether the 'quoteup_wp_session' should be created.
     *
     * @return  bool    Returns true if 'quoteup_wp_session' session should be created,
     *                  false otherwise.
     */
    public function shouldQuoteupWpSessionBeCreated()
    {
        $isAddToEnquiryCartReq  = $isParamSet = false;

        $isAddToEnquiryCartReq  = defined('DOING_AJAX') && DOING_AJAX && isset($_POST['action']) && 'wdm_trigger_add_to_enq_cart' == $_POST['action'] ? true : false;

        if (class_exists('\Includes\Frontend\QuoteupHandleQuoteApprovalRejection')) {
            $approvalRejHandle      = QuoteupHandleQuoteApprovalRejection::getInstance();
            $isParamSet             = $approvalRejHandle->isApprovalRejectionParamSet();
            $result                 = \Includes\QuoteupOrderQuoteMapping::getOrderIdOfQuote(0);
        }
 

        if ($isAddToEnquiryCartReq || ($isParamSet && null === $result)) {
            // Allow to create 'quoteup_wp_session'.
            return true;
        }

        // Don't allow to create 'quoteup_wp_session'.
        return false;
    }

    /**
     * Remove 'quoteup_wp_session' cookie from client's browser.
     */
    public function removeQuoteupWpSession()
    {
        \Includes\Frontend\Libraries\wp_session_remove_id_from_browser(QUOTEUP_SESSION_COOKIE);
    }

    /**
     * Check whether the 'wdm_product_count' and 'quotationProducts' are empty.
     * If yes, remove the 'quoteup_wp_session'.
     */
    public function checkAndRemoveSession()
    {
        global $quoteup;
        $productCount       = $quoteup->wcCartSession->get('wdm_product_count');
        $quotationProducts  = $quoteup->wcCartSession->get('quotationProducts');

        if (empty($productCount) && empty($quotationProducts)) {
            $this->removeQuoteupWpSession();
        }
    }
}

$this->wcCartSession = QuoteupManageSession::getInstance();

<?php

namespace Includes;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
* This class is for making QuoteUp WPML Compatible.
* Changing and reseting language according to choice.
* Switching language before sending mail or craeting PDF.
* @static instance Object of class
* $current_language Current language of site.
* $locale Current locale
*/
class QuoteUpWPMLCompatibility
{
    protected static $instance = null;

    public $current_language;

    public $locale;

    /**
     * Function to create a singleton instance of class and return the same.
     *
     * @return object -Object of the class
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
    * Action to change language.
    * Action to reset language.
    * Action to switch language before sending mails.
    * Action for switch language before creating pdf.
    * Action for preserving hash and other paameters
    * Action for setting products in order.
    */
    public function __construct()
    {
        // If devs want to change lang, they can call do_action in their code
        add_action('quoteup_change_lang', array($this, 'changeLang'));
        // If devs want to reset lang, they can call do_action in their code
        add_action('quoteup_reset_lang', array($this, 'resetLang'));
        add_action('wdm_before_send_admin_email', array($this, 'wdmBeforeSendAdminEmail'));
        add_action('wdm_before_create_pdf', array($this, 'wdmBeforeQuotePDF'));
        add_action('wdm_before_send_mail', array($this, 'wdmBeforeQuotePDF'));
        add_action('wdm_after_send_admin_email', array($this, 'resetLang'));
        add_action('wdm_after_create_pdf', array($this, 'resetLang'));
        add_action('wdm_after_send_mail', array($this, 'resetLang'));
        add_filter('icl_lang_sel_copy_parameters', array($this, 'preserveGetParameters'), 10, 1);
        add_filter('woocommerce_order_get_items', array($this, 'setEnquiryProductsInOrder'), 1, 2);
        add_filter('translate_object_id', array($this, 'setEnquiryObjectId'), 100, 2);
    }

    /**
    * This function is used to change Language if multilingual plugin is active for ex,WPML.
    * It changes the sites current language and locale.
    * @param string $lang site current language.
    */
    public function changeLang($lang)
    {
        if (!defined('ICL_SITEPRESS_VERSION') || ICL_PLUGIN_INACTIVE) {
            return;
        }

        global $sitepress;

        if (!$this->current_language) {
            $this->current_language = $sitepress->get_current_language();
        }

        if ($lang) {
            $this->wdmSwitchLocale($lang);
        } else {
            global $sitepress_settings;
            $this->wdmSwitchLocale($sitepress_settings[ 'admin_default_language' ]);
        }
    }
    /**
     * This function is used to switch language before creating PDF
     * @param  [string] $lang [Language to be selected]
     */
    public function wdmBeforeQuotePDF($lang)
    {
        /**
         * Use the action hook to switch the language if WPML plugin is active.
         *
         * @param  string  $currentLanguage  The language to which you want to switch.
         */
        do_action('quoteup_change_lang', $lang);
    }

    /**
     * This function is used to switch language before Sending admin mail
     * @param  [string] $lang [Language to be selected]
     */
    public function wdmBeforeSendAdminEmail($email)
    {
        if (!defined('ICL_SITEPRESS_VERSION') || ICL_PLUGIN_INACTIVE) {
            return;
        }
        global $sitepress, $sitepress_settings;
        $user = get_user_by('email', $email);
        if ($user) {
            $lang = $sitepress->get_user_admin_language($user->ID);
        } else {
            $lang = $sitepress_settings[ 'admin_default_language' ];
        }
        /**
         * This action is documented in /product-enquiry-pro/includes/class-quoteup-wpml-compatibility.php.
         */
        do_action('quoteup_change_lang', $lang);
    }

    /**
     * This function is used to switch language to orignal once mail is sent
     */
    public function resetLang()
    {
        if (!defined('ICL_SITEPRESS_VERSION') || ICL_PLUGIN_INACTIVE) {
            return;
        }

        if ($this->current_language) {
            $this->wdmSwitchLocale($this->current_language);
        }
    }

    /**
    * This function is used to switch language or locale.
    * Change the site's locale and the text domain of the plugin.
    * @param string $lang site's current language.
    */
    public function wdmSwitchLocale($lang)
    {
        global $sitepress, $wp_locale;
        $sitepress->switch_lang($lang, true);
        $this->locale = $sitepress->get_locale($lang);
        unload_textdomain('quoteup');
        unload_textdomain('default');
        $wp_locale = new \WP_Locale();
        $this->switchLang();
        load_default_textdomain();
    }
    /**
    * This function adds filters to change the plugin_locale and modify text-domain.
    * @global $quoteup Global variable for quoteup
    */
    public function switchLang()
    {
        global $quoteup;
        add_filter('plugin_locale', array($this, 'setLocale'), 10);
        $quoteup->loadTextDomain();
        remove_filter('plugin_locale', array($this, 'setLocale'), 10);
    }

    /**
    * This function sets the locale.
    * @return string Locale
    */
    public function setLocale()
    {
        return $this->locale;
    }

    /**
    * Preserve the get parameters inspite of language change.
    * @param array $parameters Parameters
     * @return array $parameters Parameters which do not change
    */
    public function preserveGetParameters($parameters)
    {
        $parameters[] = 'quoteupHash';
        $parameters[] = 'emailAddressSubmit';
        $parameters[] = 'enquiryEmail';

        return $parameters;
    }

    /**
    * Set the enquiry products in order.
    * @param array $items Enquiry item details.
    * @param object $orderObject Order object
    * @return array $items Enquiry item details

    */
    public function setEnquiryProductsInOrder($items, $orderObject)
    {
        if (!quoteupIsWpmlActive()) {
            return $items;
        }

        if (version_compare(WC_VERSION, '3.0.0', '<')) {
            $orderId = $orderObject->id;
        } else {
            $orderId = $orderObject->get_id();
        }

        $enquiryId = get_post_meta($orderId, 'quoteup_enquiry_id', true);
        
        if ($enquiryId == null || empty($enquiryId)) {
            return $items;
        }

        $this->quoteProductIds = getProductIdsInQuote($enquiryId);

        return $items;
    }

    /**
    * Sets the enquiry object Id.
    * @param int $productId Product Id.
    * @param string $item_type Type
    * @return int $productId Product Id.
    */
    public function setEnquiryObjectId($productId, $item_type)
    {
        global $pagenow;
        $isOrderEditPage = false;

        if (is_admin() && 'post.php' == $pagenow && 'shop_order' == get_post_type()) {
            $isOrderEditPage = true;
        }
        
        if ($isOrderEditPage || !isset($this->quoteProductIds) || empty($this->quoteProductIds) || is_null($this->quoteProductIds)) {
            return $productId;
        }

        if ($item_type == 'product') {
            global $sitepress;
            $trid = $sitepress->get_element_trid($productId, 'post_' . $item_type);
            $translations = $sitepress->get_element_translations($trid, 'post_' . $item_type);
            if ($translations) {
                foreach ($translations as $singleTranslation) {
                    if (in_array($singleTranslation->element_id, $this->quoteProductIds)) {
                        return $singleTranslation->element_id;
                    }
                }
            }
        }

        return $productId;
    }
}

QuoteUpWPMLCompatibility::getInstance();

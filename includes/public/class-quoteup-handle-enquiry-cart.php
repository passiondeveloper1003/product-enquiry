<?php

namespace Includes\Frontend;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
* Adds shortcode for enquiry cart
 * @static instance Object of class
*/
class QuoteupHandleEnquiryCart
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

    protected function __construct()
    {
    }

    /**
     * Callback for Enquiry cart shortcode.
     * @return string html $getContent enquiry cart
     */
    public static function quoteupEnquiryCartShortcodeCallback()
    {
        ob_start();
        /**
         * Display the content for the enquiry cart page.s
         */
        do_action('quoteup_enquiry_cart_content');
        $getContent = ob_get_contents();
        ob_end_clean();

        return $getContent;
    }
}

QuoteupHandleEnquiryCart::getInstance();

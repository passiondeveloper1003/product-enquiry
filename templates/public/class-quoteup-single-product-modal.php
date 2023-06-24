<?php

namespace Templates\Frontend;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
* Display the Single product button .
* Handles the modal view of the enquiry form on single product enquiry.
 * @static $instance object of class
*/
class QuoteupSingleProductModal
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
}

$quoteupSingleProductModal = QuoteupSingleProductModal::getInstance();

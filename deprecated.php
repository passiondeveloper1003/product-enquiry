<?php

namespace WisdmLabs;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$deprecatedFilters = array(
    'quoteup_add_custom_field_admin_email' => array(
        'filterName' => 'pep_add_custom_field_admin_email',
        'args' => '2',
        'version' => '6.1.0',
    ),
    'quoteup_add_custom_field_customer_email' => array(
        'filterName' => 'pep_add_custom_field_customer_email',
        'args' => '2',
        'version' => '6.1.0',
    ),
    'quoteup_before_product_name_in_admin_email' => array(
        'filterName' => 'pep_before_product_name_in_admin_email',
        'args' => '2',
        'version' => '6.1.0',
    ),
    'quoteup_before_price_in_admin_email' => array(
        'filterName' => 'pep_before_price_in_admin_email',
        'args' => '2',
        'version' => '6.1.0',
    ),
    'quoteup_after_price_in_admin_email' => array(
        'filterName' => 'pep_after_price_in_admin_email',
        'args' => '2',
        'version' => '6.1.0',
    ),
);

/*
 * Handle renamed filters
 * @var array
 */
global $quoteup_map_deprecated_filters;

/**
 * This class is used to display notice for deprecated hooks
 * and execute program without breaking.
 */
class WisdmDepricatedHooks
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
    public static function getInstance($depricatedFilters)
    {
        if (null === static::$instance) {
            static::$instance = new static($depricatedFilters);
        }

        return static::$instance;
    }

    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     * Action to update status of enquiry requested
     * Action to update status of Quote created.
     */
    protected function __construct($depricatedFilters)
    {
        global $quoteup_map_deprecated_filters;
        $quoteup_map_deprecated_filters = $depricatedFilters;

        add_action('init', array($this, 'mapFilters'));
    }

    public function mapFilters()
    {
        global $quoteup_map_deprecated_filters;

        foreach ($quoteup_map_deprecated_filters as $new => $oldArray) {
            add_filter($new, array($this, 'quoteupDeprecatedFilterMapping'), 1, $oldArray['args']);
        }
    }

    public function quoteupDeprecatedFilterMapping($data, $arg_1 = '', $arg_2 = '', $arg_3 = '')
    {
        global $quoteup_map_deprecated_filters;
        $filter = current_filter();
        if (isset($quoteup_map_deprecated_filters[ $filter ]) && has_filter($quoteup_map_deprecated_filters[ $filter ]['filterName'])) {
            $data = apply_filters($quoteup_map_deprecated_filters[ $filter ]['filterName'], $data, $arg_1, $arg_2, $arg_3);
            _deprecated_function('The '.$quoteup_map_deprecated_filters[ $filter ]['filterName'].' filter', $quoteup_map_deprecated_filters[ $filter ]['version'], $filter);
        }

        return $data;
    }
}

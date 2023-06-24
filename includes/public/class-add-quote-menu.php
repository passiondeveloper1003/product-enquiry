<?php
namespace Includes\Frontend;

/**
 * This class adds endpoint for quotation at frontend.
 *
 * Assigns a title for that endpoint and positions that menu in the menu list.
 *
 * Displays quote endpoint content.
 *
 * Enqueues script and styles.
 */
class QuoteUpAddQuoteMenu
{
    public static $endpoint='quote';
    public static $title='Quote';

    public function __construct()
    {
        // flush_rewrite_rules();
        add_action('init', array($this,'addEndpoints'));
        add_filter('woocommerce_account_menu_items', array($this,'accountAddQuoteMenu'));
        add_action('woocommerce_account_' . self::$endpoint .  '_endpoint', array( $this, 'getEndPointContent' ));
        add_filter('woocommerce_endpoint_' . self::$endpoint .  '_title', array( $this, 'setQuoteTitle' ), 10, 2);
        add_filter('woocommerce_get_query_vars', array($this, 'addQueryVars'));
        add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'));
    }
    
    /**
     * This function is used to add title for quotation.
     *
     * @param [String] $title    [Title for endpoint]
     *
     * @param [String] $endpoint [Endpoint]
     */
    public function setQuoteTitle($title, $endpoint)
    {
        if ($endpoint === self::$endpoint) {
            return __("Quote", QUOTEUP_TEXT_DOMAIN);
        }
        return $title;
    }
    
    /**
     * This function is used to add new endpoint in frontend for quotations.
     */
    public function addEndpoints()
    {
        add_rewrite_endpoint(self::$endpoint, EP_ROOT | EP_PAGES);
    }

    /**
     * This function is used to update query vars.
     *
     * @param [Array]    [returns array of query vars].
     */
    public function addQueryVars($vars)
    {
        $vars[self::$endpoint] = self::$endpoint;
        return $vars;
    }

    /**
     * This function is used to add Quotations menu and update menu links.
     *
     * @param [Array]    [list of all menu links on my-account page].
     */
    public function accountAddQuoteMenu($menu_links)
    {
        flush_rewrite_rules();

        $formData = quoteupSettings();
        if (isset($formData['enable_disable_quote']) && $formData['enable_disable_quote'] == 1) {
            $label = __('Enquiries', QUOTEUP_TEXT_DOMAIN);
        } else {
            $label = __('Enquiries and Quotations', QUOTEUP_TEXT_DOMAIN);
        }
        $new = array(
            self::$endpoint => $label
        );

        //add Quote menu in middle
        $menu_links = array_slice($menu_links, 0, 1, true)+
        $new +
        array_slice($menu_links, 1, null, true);
        return $menu_links;
    }

    /**
     * This function is used to set Endpoint content.
     */
    public function getEndPointContent()
    {
        $formData = get_option('wdm_form_data'); // PEP settings
        $quotationModActive = isset($formData['enable_disable_quote']) ? $formData['enable_disable_quote'] : 0;
        $args = array(
            'quotationModActive' => $quotationModActive,
        );

        /**
         * quoteup_quote_end_point_content hook.
         *
         * @hooked quoteupDisplayQuoteEndPointContent - 10 (Displays quote end point content)
         */
        do_action('quoteup_quote_end_point_content', $args);
    }

    /**
     * This function is used to enqueue scripts and styles.
     */
    public function enqueueScripts()
    {
        // If current page is quote page.
        if (is_wc_endpoint_url(self::$endpoint)) {
            // enqueue scripts
            wp_enqueue_script("quoteup-popup", QUOTEUP_PLUGIN_URL."/js/public/popupmodal.js");
            wp_enqueue_script("quoteup-quote", QUOTEUP_PLUGIN_URL."/js/public/quotemenu.js");
            wp_enqueue_script("quoteup-modal", QUOTEUP_PLUGIN_URL."/js/public/jquery.modal.min.js");
            wp_enqueue_script("quoteup-moment", QUOTEUP_PLUGIN_URL."/js/public/moment.js");
            wp_enqueue_script("quoteup-datatablejs", QUOTEUP_PLUGIN_URL."/js/public/jquery.dataTables.min.js", array('jquery'));
            wp_enqueue_script("quoteup-datatablerespjs", QUOTEUP_PLUGIN_URL."/js/public/datatables.responsive.min.js", array('jquery'));
            wp_enqueue_script('quoteup-quote-menu-js', QUOTEUP_PLUGIN_URL.'/js/public/paginatetable.js', array('jquery','quoteup-datatablejs'));

            // localize scripts
            $tableData             = $this->getLocalizedTableData();
            $tableData['ajax_url'] = admin_url('admin-ajax.php');
            wp_localize_script('quoteup-quote-menu-js', 'quoteupTableData', $tableData);

            // enqueue styles
            wp_enqueue_style("quoteup-datatablecss", QUOTEUP_PLUGIN_URL."/css/public/jquery.dataTables.min.css");
            wp_enqueue_style("quoteup-datatablerespcss", QUOTEUP_PLUGIN_URL."/css/public/datatables.responsive.min.css");
            wp_enqueue_style("quoteup-modalcss", QUOTEUP_PLUGIN_URL."/css/public/jquery.modal.min.css");
            wp_enqueue_style("quoteup-css", QUOTEUP_PLUGIN_URL."/css/public/quotestyle.css");
        }
    }

    /**
     * Returns the data to be localized for the enquiry and quote listing data
     * table on the my account page having '/quote' endpoint.
     *
     * @since 6.3.4
     * @return array Returns data to be localized.
     */
    public function getLocalizedTableData()
    {
        $tableData = array(
            'sEmptyTable'        => __('No data available in table', QUOTEUP_TEXT_DOMAIN),
            'sInfo'              => sprintf(__('Showing %1$s to %2$s of %3$s entries', QUOTEUP_TEXT_DOMAIN), '_START_', '_END_', '_TOTAL_'),
            'sInfoEmpty'         => __('Showing 0 to 0 of 0 entries', QUOTEUP_TEXT_DOMAIN),
            'sInfoFiltered'      => '',
            'sInfoPostFix'       => '',
            'sInfoThousands'     => ',',
            'sLengthMenu'        => sprintf(__('Show %s entries', QUOTEUP_TEXT_DOMAIN), '_MENU_'),
            'sLoadingRecords'    => __('Loading...', QUOTEUP_TEXT_DOMAIN),
            'sProcessing'        => __('Processing...', QUOTEUP_TEXT_DOMAIN),
            'sSearch'            => __('Search:', QUOTEUP_TEXT_DOMAIN),
            'sZeroRecords'       => __('No matching record found', QUOTEUP_TEXT_DOMAIN),
            'oPaginate'          => array(
                'sFirst'       => __('First', QUOTEUP_TEXT_DOMAIN),
                'sLast'        => __('Last', QUOTEUP_TEXT_DOMAIN),
                'sNext'        => __('Next', QUOTEUP_TEXT_DOMAIN),
                'sPrevious'    => __('Previous', QUOTEUP_TEXT_DOMAIN),
            ),
            'oAria'              => array(
                'sSortAscending'   => __(': activate to sort column ascending', QUOTEUP_TEXT_DOMAIN),
                'sSortDescending'  => __(': activate to sort column descending', QUOTEUP_TEXT_DOMAIN),
            )
        );

        $tableData = apply_filters('quoteup_quote_menu_localized_table_data', $tableData);
        return $tableData;
    }
}

new QuoteUpAddQuoteMenu();

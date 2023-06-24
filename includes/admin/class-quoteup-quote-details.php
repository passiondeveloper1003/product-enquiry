<?php

namespace Includes\Admin;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
* This class is for displaying quote details
* @static instance Object of class
*/
class QuoteupQuoteDetails
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
    * Enqueue required scripts
    */
    protected function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'), 1, 1);
    }
    /**
    * Enqueues scripts and styles for specific hook(admin)
    * Scripts for bulk action, responsive site
    * @param string $hook admin hook or any other

    */
    public function enqueueScripts($hook)
    {
        if (isset($_GET['status'])) {
            $status = filter_var($_GET['status'], FILTER_SANITIZE_STRING);
        } else {
            $status = 'all';
        }
        if ('toplevel_page_quoteup-details-new' == $hook) {
            wp_enqueue_style('table_css', QUOTEUP_PLUGIN_URL.'/css/admin/dashboard-quotes-listing.css', '', filemtime(QUOTEUP_PLUGIN_DIR.'/css/admin/dashboard-quotes-listing.css'));
            wp_enqueue_style('table_css_responsive', QUOTEUP_PLUGIN_URL.'/css/admin/dashboard-quotes-listing-responsive.css', '', filemtime(QUOTEUP_PLUGIN_DIR.'/css/admin/dashboard-quotes-listing-responsive.css'));
            wp_enqueue_script('dashboard-quotes-listing-bulk-actions', QUOTEUP_PLUGIN_URL.'/js/admin/dashboard-quotes-listing-bulk-actions.js', array('jquery'), filemtime(QUOTEUP_PLUGIN_DIR.'/js/admin/dashboard-quotes-listing-bulk-actions.js'));

            // in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
            wp_localize_script(
                'dashboard-quotes-listing-bulk-actions',
                'dashboard_quote_listing_bulk_actions',
                array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'export_nonce' => wp_create_nonce('quoteup-nonce'),
                'could_not_create_csv' => __('Could not create CSV file because of Security issues', QUOTEUP_TEXT_DOMAIN),
                'select_one_enquiry' => __('Select atleast one enquiry to export', QUOTEUP_TEXT_DOMAIN),
                'status' => $status,
                )
            );
        }
    }
    /**
    * This function is for displaying quotes and enquiry details.
    * Gets the settings about the quoatation system.
    * Display the items and search boxes likewise.
    * Includes the feature of generating csv in the bulk actions.
    */
    public function displayQuoteDetails()
    {
        global $quoteupQuotesList,$quoteupEnquiriesList;
        $path = QUOTEUP_PLUGIN_URL.'/generate-csv.php';
        $page = $_GET['page'];
        if (!class_exists('WP_List_Table')) {
            require_once ABSPATH.'wp-admin/includes/class-wp-list-table.php';
        }
        $optionData = quoteupSettings();
        ?>
        <div class="wrap">
        <h2>
        <?php
        if (isset($optionData['enable_disable_quote']) && 1 == $optionData['enable_disable_quote']) {
            _e('Enquiry Details', QUOTEUP_TEXT_DOMAIN);
        } else {
            _e('Enquiry & Quote Details', QUOTEUP_TEXT_DOMAIN);
        }
        ?>
        </h2>
            <div id="poststuff">
                <div id="post-body" class="metabox-holder">
                    <div id="post-body-content">
                        <div class="meta-box-sortables ui-sortable">
                            <form method='post' id='csv_form' action='<?php echo $path ?>'>
                                <input type='hidden' name='data' id='data'>
                            </form>
                            <form method="post" id='wdm_list'>
                                <input type="hidden" name='csv_path' id='csv_path' value="<?php echo $path; ?>" />
                                <input type="hidden" name="page" value="<?php echo $page;?>" />
                                <input type="hidden" name="isPageVisited" id="isPageVisited" value="no">

                                <?php
                                if (isset($optionData['enable_disable_quote']) && $optionData['enable_disable_quote'] == 1) {
                                    $quoteupEnquiriesList->prepare_items();
                                    $quoteupEnquiriesList->search_box(__('Search', QUOTEUP_TEXT_DOMAIN), 'search');
                                    $quoteupEnquiriesList->renderEnqSearchByParamDropdown();
                                    $quoteupEnquiriesList->display();
                                } else {
                                    $quoteupQuotesList->prepare_items();
                                    $quoteupQuotesList->search_box(__('Search', QUOTEUP_TEXT_DOMAIN), 'search');
                                    $quoteupQuotesList->renderEnqSearchByParamDropdown();
                                    $quoteupQuotesList->display();
                                }
                                ?>
                            </form>
                        </div>
                    </div>
                </div>
                <br class="clear">
            </div>
        </div>
        <?php
        //This hook display beacon icon on bottom right. 
        do_action('quoteup_pep_backend_page');
    }
}

$this->quoteDetails = QuoteupQuoteDetails::getInstance();

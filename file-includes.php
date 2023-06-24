<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

//This abstract class for privacy functionality
require_once QUOTEUP_PLUGIN_DIR.'/includes/privacy/class-abstract-quoteup-privacy.php';

//This file adds privacy functionality
require_once QUOTEUP_PLUGIN_DIR.'/includes/privacy/class-quoteup-privacy.php';

//This file inits the quote cart session
require_once QUOTEUP_PLUGIN_DIR.'/deprecated.php';

//This file adds hooks for templates
require_once QUOTEUP_PLUGIN_DIR.'/includes/quoteup-template-hooks.php';

//This file add functions for templates
require_once QUOTEUP_PLUGIN_DIR.'/includes/quoteup-template-functions.php';

//This file inits the quote cart session
require_once QUOTEUP_PLUGIN_DIR.'/init-quote-cart-session.php';

//This file adds the fields for enquiry form specified in settings.
require_once QUOTEUP_PLUGIN_DIR.'/includes/class-quoteup-add-custom-field.php';

//This file manages the quote history table
require_once QUOTEUP_PLUGIN_DIR.'/includes/class-quoteup-manage-history.php';

//This file manages expiration of quotes.
require_once QUOTEUP_PLUGIN_DIR.'/includes/class-quoteup-manage-expiration.php';

//Include file which handles Database Operations related to mapping between Order Id and Quote/Enquiry Id
require_once QUOTEUP_PLUGIN_DIR.'/includes/class-quoteup-order-quote-mapping.php';

//Create and Manage Session
require_once QUOTEUP_PLUGIN_DIR.'/includes/public/class-quoteup-manage-session.php';

//Include file which handles approval and rejecton of quote
require_once QUOTEUP_PLUGIN_DIR.'/includes/public/class-quoteup-handle-cart.php';

// file for sending enquiry mail
require_once QUOTEUP_PLUGIN_DIR.'/includes/public/class-quoteup-send-enquiry-mail.php';

//Include file which handles Ajax File Upload
require_once QUOTEUP_PLUGIN_DIR.'/includes/public/class-quoteup-upload-attach-files.php';

//Include file which handles view of enquiry cart
require_once QUOTEUP_PLUGIN_DIR.'/templates/public/class-quoteup-handle-enquiry-cart-view.php';

/*
 * Frontend Files
 */
/**
 * Return a boolean value to determine whether to include the frontend files.
 *
 * This filter can be used to include the frontend files when these files are not included.
 * For example, in the case of Ajax requests, these frontend files are not included.
 *
 * @since 6.4.2
 *
 * @param bool      Whether to include the frontend files. Default false.
 *
 * @return bool     Return boolean value. If true is returned, frontend files are included.
 */
$shouldIncFrontendFiles = apply_filters('quoteup_include_frontend_files', false);

if ((!is_admin() && !defined('DOING_CRON')) || $shouldIncFrontendFiles) {
    
    // Include file which handles view of approval and rejecton of quote
    require_once QUOTEUP_PLUGIN_DIR.'/templates/public/class-quoteup-handle-quote-approval-rejection-view.php';

    // Include file which displays add to quote button
    require_once QUOTEUP_PLUGIN_DIR.'/includes/public/class-quoteup-display-quote-button.php';

    // Include file which displays bubble on the frontend after adding product into the cart
    require_once QUOTEUP_PLUGIN_DIR.'/includes/public/class-quoteup-display-enquiry-cart-bubble.php';

    // Include file which handles approval and rejecton of quote
    require_once QUOTEUP_PLUGIN_DIR.'/includes/public/class-quoteup-handle-quote-approval-rejection.php';
    
    // File for sending quote rejected email
    require_once QUOTEUP_PLUGIN_DIR.'/includes/public/class-quoteup-customer-rejected-email.php';

    // Include file which handles enquiry cart
    require_once QUOTEUP_PLUGIN_DIR.'/includes/public/class-quoteup-handle-enquiry-cart.php';

    // Include file which handles enquiry button shortcode
    require_once QUOTEUP_PLUGIN_DIR.'/includes/public/class-quoteup-enquiry-button-shortcode.php';

    // Include shortcode file.
    require_once QUOTEUP_PLUGIN_DIR.'/shortcodes.php';

    // Include file to display the quote menu on the myaccount page.
    require_once QUOTEUP_PLUGIN_DIR.'/includes/public/class-add-quote-menu.php';
}
    // File to add history
    require_once QUOTEUP_PLUGIN_DIR.'/includes/class-quoteup-display-history.php';

if (is_admin()) {
    // Display links such as Settings, documentation, support on plugin list page.
    require_once QUOTEUP_PLUGIN_DIR.'/includes/admin/class-quoteup-admin-plugin-links.php';
    
    //Display meta box on single product page to Enable/Disable Enquiry/Quote button
    require_once QUOTEUP_PLUGIN_DIR.'/includes/admin/class-quoteup-enable-disable-quoteup-button.php';

    //Display meta box on single product page to Show/hide price on frontend
    require_once QUOTEUP_PLUGIN_DIR.'/includes/admin/class-quoteup-enable-disable-price.php';

    //Display meta box on single product page to Enable/Disable Add to cart
    require_once QUOTEUP_PLUGIN_DIR.'/includes/admin/class-quoteup-enable-disable-add-to-cart-button.php';

    //Products Table with extended bulk actions
    require_once QUOTEUP_PLUGIN_DIR.'/includes/admin/class-quoteup-products-table.php';

    // List Quote Details
    require_once QUOTEUP_PLUGIN_DIR.'/includes/admin/class-quoteup-quote-details.php';

    // Load Settings Page
    require_once QUOTEUP_PLUGIN_DIR.'/includes/settings/class-quoteup-settings.php';

    // Load Privacy Settings
    require_once QUOTEUP_PLUGIN_DIR.'/includes/privacy/class-quoteup-privacy-settings.php';
    // Load Dashboard Menu
    require_once QUOTEUP_PLUGIN_DIR.'/includes/admin/class-quoteup-dashboard-menu.php';
    
    // Include file for edit enquiry details feature
    require_once QUOTEUP_PLUGIN_DIR.'/includes/admin/edit/class-quoteup-enquiries-edit.php';
    $this->enquiryDetailsEdit = Includes\Admin\QuoteupEnquiriesEdit::getInstance();
    // Include file for edit quote details feature
    require_once QUOTEUP_PLUGIN_DIR.'/includes/admin/edit/class-quoteup-quotes-edit.php';
    $this->quoteDetailsEdit = Includes\Admin\QuoteupQuotesEdit::getInstance();

    //include file for Create Quote from Dashboard feature
    require_once QUOTEUP_PLUGIN_DIR.'/includes/admin/class-quoteup-create-quotation-from-dashboard.php';

    //file for pdf generation
    require_once QUOTEUP_PLUGIN_DIR.'/includes/admin/class-quoteup-generate-pdf.php';

    // file for sending mail with quotation
    require_once QUOTEUP_PLUGIN_DIR.'/includes/admin/class-quoteup-send-quote-mail.php';


    //file to add main enquiry meta box
    require_once QUOTEUP_PLUGIN_DIR.'/includes/admin/class-quoteup-display-main-enquiry-details.php';

        //file to add Versions meta box
    require_once QUOTEUP_PLUGIN_DIR.'/includes/admin/class-quoteup-display-versions.php';

    //include file for hover feature
    require_once QUOTEUP_PLUGIN_DIR.'/includes/admin/class-quoteup-tooltip-on-hover.php';

    //Include file which handles approval and rejecton of quote
    require_once QUOTEUP_PLUGIN_DIR.'/includes/admin/class-quoteup-handle-quote-approval-rejection.php';

    //Include file which handles Admin Notices for MPE Cart Page
    require_once QUOTEUP_PLUGIN_DIR.'/includes/admin/class-quoteup-admin-notices.php';

    // Include file to create enquiry cart page.
    require_once QUOTEUP_PLUGIN_DIR.'/includes/admin/default-page/class-quoteup-enquiry-cart-page.php';

    // Include file to create quote approval/ rejection page.
    require_once QUOTEUP_PLUGIN_DIR.'/includes/admin/default-page/class-quoteup-approval-rejection-page.php';

    // Include file if CSP plugin is active and its version is >= 4.5.0.
    if (quoteupIsCSPActive()) {
        require_once QUOTEUP_PLUGIN_DIR.'/includes/csp/class-quoteup-csp-pricing.php';
    }
}

if (defined('DOING_AJAX')) {
    // Include file to get the quote data.
    require_once QUOTEUP_PLUGIN_DIR.'/includes/public/class-get-quote-data.php';

    // Include file to get the particular quote data to be added in the modal.
    require_once QUOTEUP_PLUGIN_DIR.'/includes/public/class-add-popup-modal.php';

    // Include file if CSP plugin is active and its version is >= 4.5.0.
    if (quoteupIsCSPActive()) {
        require_once QUOTEUP_PLUGIN_DIR.'/includes/csp/class-quoteup-csp.php';
    }
}

// Include files for compatibility with other extensions
if (is_plugin_active('mailchimp-for-woocommerce/mailchimp-woocommerce.php')) {
    require_once QUOTEUP_PLUGIN_DIR.'/includes/mailchimp/class-mailchimp-integration.php';
}


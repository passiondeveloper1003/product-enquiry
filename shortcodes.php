<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Shortcode for Approval Rejection Page on front-end.
add_shortcode(
    'APPROVAL_REJECTION_CHOICE',
    array('Includes\Frontend\QuoteupHandleQuoteApprovalRejection', 'approvalRejectionShortcodeCallback')
);

// Shortcode for Enquiry-Quote cart
add_shortcode(
    'ENQUIRY_CART',
    array('Includes\Frontend\QuoteupHandleEnquiryCart', 'quoteupEnquiryCartShortcodeCallback')
);

// Shortcode for Enquiry button with the need of 'product_id' attribute.
add_shortcode(
    'ENQUIRY_BUTTON',
    array('Includes\Frontend\QuoteupEnquiryButtonShortcode', 'quoteupEnquiryButtonShortcodeCallback')
);

// Shortcode for Enquiry button without the need of 'product_id' attribute.
add_shortcode(
    'ENQUIRY_BUTTON_DYNAMIC',
    array('Includes\Frontend\QuoteupEnquiryButtonShortcode', 'quoteupEnquiryButtonDynamicShortcodeCalback')
);

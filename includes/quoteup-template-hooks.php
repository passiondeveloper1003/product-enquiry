<?php

/**
 * This filter adds arguments for table row
 */
add_filter('quoteup_enquiry_cart_row_arguments', 'generateCartRowArguments', 10, 2);

/**
 * This filter adds arguments for default enquiry form
 */
add_filter('quoteup_enquiry_cart_default_form_arguments', 'generateDefaultFormArguments', 10, 1);

/**
 * This filter adds arguments for empty enquiry cart
 */
add_filter('quoteup_empty_enquiry_cart_arguments', 'generateEmptyEnquiryCartArguments', 10, 1);

/**
 * This filter returns boolean value to display send me a copy
 */
add_filter('quoteup_enquiry_cart_send_copy', 'checkEnquiryCartSendCopy', 10, 1);

/**
 * This action renders title on the form
 */
add_action('quoteup_before_form_enquiry_cart', 'displayTitle', 10, 1);

/**
 * This action renders hidden fields on the form
 */
add_action('quoteup_before_fields_enquiry_cart', 'renderHiddenFields', 10, 1);

/**
 * This action renders nonce error div on the form
 */
add_action('quoteup_before_fields_enquiry_cart', 'renderNonceErrorDiv', 15, 1);

/**
 * This action renders captcha on the form
 */
add_action('quoteup_after_fields_in_form', 'displayCaptcha', 5, 1);

/**
 * This action renders send me a copy checkbox on the form
 */
add_action('quoteup_after_fields_in_form', 'displaySendMeCopy', 10, 1);

/**
 * This action renders send button on the form
 */
add_action('quoteup_after_fields_in_form', 'displaysendButton', 15, 1);

/**
 * This action renders error div on the form
 */
add_action('quoteup_after_fields_in_form', 'displayErrorDiv', 20, 1);

/**
 * This action renders success div on the form
 */
add_action('quoteup_after_enquiry_form', 'pepSuccessMessage', 10, 1);

/**
 * This action renders enquiry cart table on enquiry cart page
 */
add_action('quoteup_enquiry_cart_table', 'enquiryCartTable', 10, 1);

/**
 * This action renders enquiry cart empty message div on the form
 */
add_action('quoteup_enquiry_cart_empty', 'enquriyCartEmpty', 10, 1);

/**
 * Single product enquiry modal hooks and filters
 */

/**
 * This action initiates modal render
 */
add_action('quoteup_display_enquiry_modal', 'displayModal', 10, 1);

/**
 * This action starts rendering modal
 */
add_action('quoteup_display_modal_dialog', 'displayModalDialog', 10, 1);

/**
 * This action starts rendering modal
 */
add_action('quoteup_after_single_product_form', 'displaySuccessDiv', 10, 1);

/**
 * This action rendering modal header
 */
add_action('quoteup_wdm_modal_content', 'enquiryModalFormHeader', 10, 1);

/**
 * This action rendering modal body
 */
add_action('quoteup_wdm_modal_content', 'enquiryModalFormBody', 20, 1);

/**
 * This action rendering modal footer
 */
add_action('quoteup_wdm_modal_content', 'enquiryModalFormFooter', 30, 1);

/**
 * This action renders enquiry button for single product enquiry
 */
add_action('quoteup_after_wdm_modal', 'enquiryButton', 10, 1);

/**
 * End of Single product enquiry modal hooks and filters.
 * Start of Approval rejection hooks and filters.
 */

/**
 * This action renders approval rejection view page.
 */
add_action('quoteup_approval_rejection_view', 'approvalRejectionView', 10, 1);

/**
 * This action renders approval rejection view page.
 */
add_action('quoteup_approval_rejection_validation', 'approvalRejectionValidation', 10, 1);

/**
 * This action clears cart and ends session.
 */
add_action('quoteup_not_enough_stock', 'quoteNotEnoughStock', 10);

/**
 * This action handles quote rejection
 */
add_action('quoteup_quote_rejected', 'quoteRejected', 10, 1);

/**
 * //Show form which shows Approve and Reject button.
 */
add_action('quoteup_approval_rejection_form', 'displayApprovalRejectionForm', 10, 1);

/**
 * //Show form which shows Approve and Reject button.
 */
add_action('quoteup_quote_table_row', 'displayQuoteTableRow', 10, 1);

/**
 * This action renders quote end point content.
 */
add_action('quoteup_quote_end_point_content', 'quoteupDisplayQuoteEndPointContent', 10, 1);

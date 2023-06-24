<?php
/**
 * Quote Rejected Mail Heading
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/quote-rejected-mail/heading.php.
 *
 * HOWEVER, on occasion QuoteUp will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author  WisdmLabs
 * @version 6.5.0
 */

/**
 * Perform action before quote rejected email header.
 *
 * @since 6.5.0
 *
 * @param  array  $args {
 *     Array containing enquiry data and rejected message.
 *
 *     @type int     $enquiry_id        Enquiry Id.
 *     @type string  $customer_name     Customer name associated with the enquiry.
 *     @type string  $customer_email    Customer email associated with the enquiry.
 *     @type string  $rejected_message  Quote rejection message entered by customer.
 *     @type array   $quotation_details Array containg quote products data.
 * }
 */
do_action('before_quote_rejected_mail_header', $args);

$heading = "<b>" . __('Quotation Rejected', QUOTEUP_TEXT_DOMAIN) . "</b>";

/**
 * 
 *
 * @since 6.5.0
 *
 * @param   string  $heading    Heading for quote rejected email.
 * @param  array  $args {
 *     Array containing enquiry data and rejected message.
 *
 *     @type int     $enquiry_id        Enquiry Id.
 *     @type string  $customer_name     Customer name associated with the enquiry.
 *     @type string  $customer_email    Customer email associated with the enquiry.
 *     @type string  $rejected_message  Quote rejection message entered by customer.
 *     @type array   $products_details  Array containg products data.
 * }
 */
$heading = apply_filters('quote_rejected_email_heading', $heading, $args);

// Using this action woocommerce adds heading to mail.
do_action('woocommerce_email_header', $heading);

/**
 * Perform action after quote rejected email header.
 *
 * @since 6.5.0
 *
 * @param  array  $args {
 *     Array containing enquiry data and rejected message.
 *
 *     @type int     $enquiry_id        Enquiry Id.
 *     @type string  $customer_name     Customer name associated with the enquiry.
 *     @type string  $customer_email    Customer email associated with the enquiry.
 *     @type string  $rejected_message  Quote rejection message entered by customer.
 *     @type array   $quotation_details Array containg quote products data.
 * }
 */
do_action('before_quote_rejected_mail_header', $args);

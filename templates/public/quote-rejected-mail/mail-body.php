<?php
/**
 * Quote Rejected Mail Body.
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/quote-rejected-mail/mail-body.php.
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
 * Perform action before products table in Quote Rejected Email.
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
do_action('before_product_table_in_quote_rejected_mail', $args);
?>
<div>
    <?php
    // This loads the template for quote rejection message
    quoteupGetPublicTemplatePart('quote-rejected-mail/quote-rejection-message', '', $args);

    // This loads the template for product table
    quoteupGetPublicTemplatePart('quote-rejected-mail/products-table', '', $args);
    ?>
</div>
<?php
/**
 * Perform action after products table in Quote Rejected Email.
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
do_action('after_product_table_in_quote_rejected_mail', $args);

<?php
/**
 * Enquiry mail body.
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/enquiry-mail/mail-body.php.
 *
 * HOWEVER, on occasion QuoteUp will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author  WisdmLabs
 *
 * @version 6.1.0
 */
?>

<div style='background-color:#ddd'>
    <table class="wdm-enquiry-table" style='width: 100%;background: #F7F7F7;border-bottom: 1px solid #ddd;margin-bottom: 0px;' cellspacing='10px'>

        <?php
        if ($source == 'admin' && $optionData['enquiry_form'] != 'custom') {
            // Display form field details for default form in admin mail
            /*
             * quoteup_add_custom_field_admin_email hook.
             *
             * @hooked addCustomFieldsAdminEmail - 10 (Adds custom fields of default form in admin email)
             */
            do_action('quoteup_add_custom_field_admin_email', $data_obtained_from_form, $source);
        } elseif ($optionData['enquiry_form'] != 'custom') {
            // Display form field details for default form in customer mail
            /*
             * quoteup_add_custom_field_customer_email hook.
             *
             * @hooked addCustomFieldsCustomerEmail - 10 (Adds custom fields of default form in customer email)
             */
            do_action('quoteup_add_custom_field_customer_email', $data_obtained_from_form, $source);
        } else {
            // Display form field details of custom form
            /*
             * quoteup_add_custom_form_field_in_email hook.
             *
             * @hooked addCustomFormFieldsAdminEmail - 10 (Adds custom fields of custom form in email)
             */
            do_action('quoteup_add_custom_form_field_in_email', $data_obtained_from_form, $source);
        }

        do_action('quoteup_before_product_table_enquiry_mail', $form_data_for_mail, $source);
        ?>
    </table>
    <?php

    //This loads the template for product table
    quoteupGetPublicTemplatePart('enquiry-mail/products-table', '', $args);
    ?>
</div>

<?php

if (!quoteupIsPriceColumnDisabled($form_data) && isset($GLOBALS['enable_price_flag']) && $GLOBALS['enable_price_flag'] && $source == 'admin') :
    ?>
    <label class='price-message'>
        <?php _e('Products prices that are hidden on the frontend are displayed only in the enquiry emails received by the admin', QUOTEUP_TEXT_DOMAIN); ?>
    </label>
    <?php
endif;

do_action('quoteup_after_product_table_enquiry_mail', $form_data_for_mail, $source, $args);

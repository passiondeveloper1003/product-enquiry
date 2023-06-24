<?php
/**
 * Enquiry cart table
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/enquiry-cart/enquiry-cart-table.php.
 *
 * HOWEVER, on occasion QuoteUp will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author  WisdmLabs
 * @version 6.1.0
 */

global $quoteup;
?>
<table class='shop_table cart generated_for_desktop wdm_shop_tbl wdm-quote-cart-table' cellspacing='0'>
    <?php

    //This loads the template for product table
    quoteupGetPublicTemplatePart('enquiry-cart/enquiry-cart-head', '', $args);
    ?>
    <tbody>
        <?php
        $prod = $quoteup->wcCartSession->get('wdm_product_info');
        foreach ($prod as $product) {
        //foreach ($_SESSION[ 'wdm_product_info' ] as $product) {
            $args = apply_filters('quoteup_enquiry_cart_row_arguments', $args, $product);

            //This loads the template for enquiry cart row
            quoteupGetPublicTemplatePart('enquiry-cart/enquiry-cart-row', '', $args);
        }

        if (!quoteupIsPriceColumnDisabled($form_data)) :
            //This loads the template for enquiry cart row
            quoteupGetPublicTemplatePart('enquiry-cart/enquiry-cart-total-row', '', $args);
        endif;

        //This loads the template for enquiry cart row
        quoteupGetPublicTemplatePart('enquiry-cart/enquiry-cart-update-button-row', '', $args);

        do_action('quoteup_enquiry_cart_table_body');
    ?>
    </tbody>
</table>

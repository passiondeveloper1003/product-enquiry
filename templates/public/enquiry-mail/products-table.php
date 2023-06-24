<?php

/**
 * Enquiry mail products table
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/enquiry-mail/products-table.php.
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

// @session_start();
if (isset($form_data[ 'enable_disable_mpe' ]) && $form_data[ 'enable_disable_mpe' ] == 1) :
    //If multi product enquiry is active
    ?>
    <table class="wdm-enquiry-table" style='width:100%;table-layout: fixed;' cellspacing='0' cellpadding='0'>
        <?php

        //This loads the template for product table head for multi product enquiry
        quoteupGetPublicTemplatePart('enquiry-mail/products-table-head', '', $args);

        //This loads the template for products table body for multi product enquiry
        quoteupGetPublicTemplatePart('enquiry-mail/multi-product-enquiry/products-table-body', '', $args);

        if (!quoteupIsPriceColumnDisabled($form_data)) :
            //This loads the template for products table footer for multi product enquiry
            quoteupGetPublicTemplatePart('enquiry-mail/multi-product-enquiry/products-table-footer', '', $args);
        endif;

        ?>
    </table>
    <?php
else :
    ?>
    <table class="wdm-enquiry-table" style='width: 100%;' cellspacing='0' cellpadding='0'>
        <?php
        //This loads the template for product table head for single product enquiry
        quoteupGetPublicTemplatePart('enquiry-mail/products-table-head', '', $args);

        //This loads the template for products table body for single product enquiry
        quoteupGetPublicTemplatePart('enquiry-mail/single-product-enquiry/products-table-body', '', $args);

        if (!quoteupIsPriceColumnDisabled($form_data)) :
            $productQuantity = $data_obtained_from_form[ 'product_quant' ];
            $product_id      = $data_obtained_from_form[ 'product_id' ];
            $variation_id    = filter_var($data_obtained_from_form['variation_id'], FILTER_SANITIZE_NUMBER_INT);

            if ($variation_id != '') {
                $product = wc_get_product($variation_id);
                $price   = quoteupGetPriceToDisplay($product, $productQuantity);
            } else {
                $product = wc_get_product($product_id);
                $price   = quoteupGetPriceToDisplay($product, $productQuantity);
            }

            $args['productQuantity'] = $productQuantity;
            $args['price']           = $price;
            $args['enable_price']    = quoteupShouldHidePrice($product_id) ? '' : 'yes';

            //This loads the template for products table footer for single product enquiry
            quoteupGetPublicTemplatePart('enquiry-mail/single-product-enquiry/products-table-footer', '', $args);
        endif;
        ?>
    </table>
    <?php
endif;

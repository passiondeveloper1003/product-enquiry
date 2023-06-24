<?php
/**
 * Enquiry mail products table heading
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/enquiry-mail/products-table-head.php.
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
?>
<!--Table heading of products table-->
<tr>
    <?php do_action('quoteup_before_product_name_head', $data_obtained_from_form, $source); ?>
    <th class='product-name-head'>
        <?php _e('Product Name', QUOTEUP_TEXT_DOMAIN); ?>
    </th>
    <?php do_action('quoteup_before_sku_head', $data_obtained_from_form, $source); ?>
    <th class='sku-head'>
        <?php _e('SKU', QUOTEUP_TEXT_DOMAIN); ?>
    </th>
    <?php do_action('quoteup_before_qty_head', $data_obtained_from_form, $source); ?>
    <th class='qty-head'>
        <?php _e('Quantity', QUOTEUP_TEXT_DOMAIN); ?>
    </th>

    <?php
    // Check if 'Price' column disabled.
    if (!quoteupIsPriceColumnDisabled($form_data)) :
        do_action('quoteup_before_price_head', $data_obtained_from_form, $source);
        ?>
        <th class='price-head'>
            <?php _e('Price', QUOTEUP_TEXT_DOMAIN); ?>
        </th>
        <?php
    endif;

    // Check if Multi-product Enquiry mode and 'Remarks' column settings are enabled.
    if (quoteupIsMPEEnabled($form_data) && !quoteupIsRemarksColumnDisabled($form_data)) :
        $remarksTableHeadName = quoteupGetRemarksThNameEnqCart($form_data);
        do_action('quoteup_before_remarks_head', $data_obtained_from_form, $source);
        ?>
        <th class='remarks-head'>
            <?php echo esc_html($remarksTableHeadName); ?>
        </th>
        <?php
    endif;
    ?>
</tr>

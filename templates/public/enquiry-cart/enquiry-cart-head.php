<?php
/**
 * Enquiry cart head
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/enquiry-cart/enquiry-cart-head.php.
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
<thead>
    <tr class='cart_item cart_header'>
        <th class='product-remove cart-remove'>&nbsp;</th>
        <th class='product-thumbnail cart-thumbnail'>&nbsp;</th>
        <?php do_action('quoteup_enq_cart_before_product_name_head', $args); ?>
        <th class='product-name cart-name'>
            <?php _e('Product', QUOTEUP_TEXT_DOMAIN); ?>
        </th>
        <?php
        // If Price column is enabled, then show 'Price' table head.
        if (!quoteupIsPriceColumnDisabled($form_data)) :
        do_action('quoteup_enq_cart_before_product_price_head', $args);    
            ?>
        <th class='product-price quote-cart-price'>
            <?php _e('Price', QUOTEUP_TEXT_DOMAIN); ?>
        </th>
            <?php
        endif;
        do_action('quoteup_enq_cart_before_product_qty_head', $args);
        ?>
        <th class='product-quantity cart-quantity'>
            <?php _e('Quantity', QUOTEUP_TEXT_DOMAIN); ?>
        </th>
        <?php
        if (!quoteupIsRemarksColumnDisabled($form_data)) :
            $remarksTableHeadName = quoteupGetRemarksThNameEnqCart($form_data);
            do_action('quoteup_enq_cart_before_product_rem_head', $args);
            ?>
            <th class='product-subtotal cart-subtotal'>
                <?php echo esc_html($remarksTableHeadName); ?>
            </th>
            <?php
        endif;
        do_action('quoteup_enq_cart_after_product_rem_head', $args);
        ?>
    </tr>
</thead>

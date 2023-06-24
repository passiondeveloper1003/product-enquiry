<?php
/**
 * Enquiry cart row
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/enquiry-cart/enquiry-cart-row.php.
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
<tr class='cart_item cart_product'>
    <td class="product-remove">
        <a href="#" class="remove" data-product_id="<?php echo $product[ 'id' ]; ?>" data-variation_id="<?php echo $product['variation_id']; ?>" data-variation = '<?php echo $dataVariation; ?>'>
            &times;
        </a>
    </td>
    <td class="product-thumbnail">
        <img width="180" height="180" src="<?php echo $img_content; ?>" class="attachment-shop_thumbnail size-shop_thumbnail wp-post-image"  sizes="(max-width: 180px) 100vw, 180px" />
    </td>
    <?php do_action('quoteup_enq_cart_before_product_name', $args, $product); ?>
    <td class="product-name">
        <a href="<?php echo $url; ?>">
            <?php
            echo get_the_title($product[ 'id' ]); ?>
        </a>
        <?php echo printVariations($product); ?>
    </td>
    <?php
    // If Price column is enabled, then show 'Price' table data.
    if (!quoteupIsPriceColumnDisabled($form_data)) :
        do_action('quoteup_enq_cart_before_product_price', $args, $product);
        ?>
        <td class='product-price'>
            <?php echo $totalPrice; ?>
        </td>
        <?php
    endif;
    do_action('quoteup_enq_cart_before_product_qty', $args, $product);
    ?>
    <td class='product-quantity'>
        <div class='quantity wdm-quantity'>
            <input type='number' min='1' step='1' data-product_id='<?php echo $product[ 'id' ]; ?>' data-variation_id='<?php echo $product['variation_id']; ?>' data-variation = '<?php echo $dataVariation; ?>' name='wdm_product_quantity' data-product_hash="<?php echo esc_attr($productHash); ?>" value='<?php echo $product[ 'quantity' ]; ?>' class='wdm-prod-quant input-text qty '>
        </div>
    </td>
    <?php
    if (!quoteupIsRemarksColumnDisabled($form_data)) :
        do_action('quoteup_enq_cart_before_remarks', $args, $product);
        ?>
        <td class='product-subtotal'>
            <textarea placeholder='<?php echo $placeholder; ?>' rows='2' cols='5' class='wdm-remark' data-product_id='<?php echo $product[ 'id' ]; ?>'><?php echo $product[ 'remark' ]; ?></textarea>
        </td>
        <?php
    endif;
    do_action('quoteup_enq_cart_after_remarks', $args, $product);
    ?>
</tr>

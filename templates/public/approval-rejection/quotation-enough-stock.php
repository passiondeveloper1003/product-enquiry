<?php
/**
 * Quotation enough stock
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/approval-rejection/quotation-enough-stock.php.
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
global $quoteup_enough_stock_product_id, $quoteup_enough_stock_variation_details;
?>

<div style="color:red;" class="quoteup-error">
    <?php
    echo sprintf(__("'%s%s' is not in stock. Contact site admin to make this order", QUOTEUP_TEXT_DOMAIN), get_the_title($quoteup_enough_stock_product_id), $quoteup_enough_stock_variation_details);

    do_action('quoteup_not_enough_stock', $args);
    ?>
</div>
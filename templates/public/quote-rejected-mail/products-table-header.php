<?php
/**
 * Quote Rejected Mail Product Table Header
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/quote-rejected-mail/products-table-header.php.
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

?>
<tr>
<?php
    $classPrefix = '';
    $newPriceHeading = __('Price', QUOTEUP_TEXT_DOMAIN);
if ('yes' == $args['show_price']) {
    $classPrefix = '-price';
    $newPriceHeading = __('New', QUOTEUP_TEXT_DOMAIN);
}
    ?>
    <th class="head-product<?php echo $classPrefix; ?>" align="left"><?php _e('Product', QUOTEUP_TEXT_DOMAIN); ?></th>
    <th class="head-sku<?php echo $classPrefix ?>" align="left"> <?php _e('Sku', QUOTEUP_TEXT_DOMAIN); ?> </th>
    <?php if ('yes' == $args['show_price']) : ?>
        <th class="old_price" align="left"> <?php _e('Old', QUOTEUP_TEXT_DOMAIN); ?> </th>
    <?php endif; ?>
    <th class="new_price" align="left"> <?php echo $newPriceHeading; ?> </th>
    <th class="quantity" align="center"> <?php _e('Quantity', QUOTEUP_TEXT_DOMAIN); ?> </th>
    <th class="total" align="right"> <?php _e('Amount', QUOTEUP_TEXT_DOMAIN); ?> </th>
</tr>

<tr>
<?php
    $classPrefix = '';
    $newPriceHeading = __('Price', QUOTEUP_TEXT_DOMAIN);
if ($show_price == 1) {
    $classPrefix = '-price';
    $newPriceHeading = __('New', QUOTEUP_TEXT_DOMAIN);
}
    ?>
    <th class="head-product<?php echo $classPrefix; ?>" align="left"><?php _e('Product', QUOTEUP_TEXT_DOMAIN); ?></th>
    <th class="head-sku<?php echo $classPrefix ?>" align="left"> <?php _e('Sku', QUOTEUP_TEXT_DOMAIN); ?> </th>
    <?php if ($show_price == 1) : ?>
        <th class="old_price" align="left"> <?php _e('Old', QUOTEUP_TEXT_DOMAIN); ?> </th>
    <?php endif; ?>
    <th class="new_price" align="left"> <?php echo $newPriceHeading; ?> </th>
    <th class="quantity" align="center"> <?php _e('Quantity', QUOTEUP_TEXT_DOMAIN); ?> </th>
    <th class="total" align="right"> <?php _e('Amount', QUOTEUP_TEXT_DOMAIN); ?> </th>
</tr>

<?php
/**
 * Quotation Table Head.
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/approval-rejection/quotation/quotation-table-head.php.
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
<thead>
    <tr>
    <?php
        $newPriceHeading = __('Price', QUOTEUP_TEXT_DOMAIN);

    if ($show_price == 'yes') {
        $newPriceHeading = __('New', QUOTEUP_TEXT_DOMAIN);
        ?>
        <th class="product-price">
            <?php _e('Product', QUOTEUP_TEXT_DOMAIN);
            ?>
            </th>
            <th class="sku-price">
            <?php _e('Sku', QUOTEUP_TEXT_DOMAIN);
            ?>
            </th>
            <th>
            <?php _e('Old', QUOTEUP_TEXT_DOMAIN);
            ?>
            </th>
        <?php
    } else {
        ?>
        <th class="product">
            <?php _e('Product', QUOTEUP_TEXT_DOMAIN);
            ?>
            </th>
            <th class="sku">
            <?php _e('Sku', QUOTEUP_TEXT_DOMAIN);
            ?>
            </th>
            <?php
    }
        ?>
        <th>
            <?php echo $newPriceHeading; ?>
        </th>
        <th>
            <?php _e('Qty', QUOTEUP_TEXT_DOMAIN);?>
        </th>
        <th>
            <?php _e('Amount', QUOTEUP_TEXT_DOMAIN); ?>
        </th>
    </tr>
</thead>
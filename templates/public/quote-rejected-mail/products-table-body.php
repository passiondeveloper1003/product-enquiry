<?php
/**
 * Quote Rejected Mail Product Table Body
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/quote-rejected-mail/products-table-body.php.
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
    if ('yes' == $args['show_price']) :
        $classPrefix = '-price';
    endif;
    ?>
    <td class="product<?php echo $classPrefix; ?>" align="left">
        <?php
            echo get_the_title($quoteProduct['product_id']);
        if ($_product->is_type('variable')) :
            quoteupRemoveClassFilter('woocommerce_attribute_label', 'WCML_WC_Strings', 'translated_attribute_label');
            global $sitepress;
            remove_filter('get_term', array($sitepress, 'get_term_adjust_id'), 1);
            echo printVariations($quoteProduct);
        endif;
        ?>
    </td> <!-- td.product ends here -->
    <td class="sku<?php echo $classPrefix ?>" align="left">
        <?php
        if ($_product->is_type('variable')) :
            $_product_variation = wc_get_product($quoteProduct['variation_id']);
            echo $_product_variation->get_sku();
        else :
                    echo $_product->get_sku();
        endif;
        ?>
    </td> <!-- td.sku ends here -->
    <?php if ('yes' == $args['show_price']) : ?>
        <td align="left" class="old_price">
            <?php echo wc_price($oldPrice); ?>
        </td> <!-- td.old_price ends here -->
    <?php endif; ?>
    <td align="left" class="new_price">
        <?php echo wc_price($quoteProduct['newprice']); ?>
    </td> <!-- td.new_price ends here -->
    <td align="center" class="quantity">
        <?php echo $quoteProduct['quantity']; ?>
    </td> <!-- td.new_price ends here -->
    <td align="right" class="total">
        <?php
            $change = quoteupReturnOldNewPriceChangeInPerc($oldPrice, $quoteProduct['newprice']);

            if (0 == $change):
                ?>
                <!-- Product new total price -->
                <div class="quote-pdf product-total-new-price"><?php echo wc_price($quoteProduct['newprice'] * $quoteProduct['quantity']);?></div>
                <?php
            else:
                $classAttr = quoteupReturnClassForCostChange($change);
                $change    = $change . '%';

                ?>
                <!--  Product original total price -->
                <div class="quote-pdf product-total-og-price <?php echo esc_attr($classAttr); ?>"><s><?php echo wc_price($oldPrice * $quoteProduct['quantity']); ?></s></div>
                <!-- Product new total price -->
                <div class="quote-pdf product-total-new-price">
                <?php echo wc_price($quoteProduct['newprice'] * $quoteProduct['quantity']);
                ?>
                </div>
                <!-- Change / difference in product original and new total price in percentage -->
                <div class="quote-pdf product-og-new-price-change percentage-diff <?php echo esc_attr($classAttr); ?>">(<?php echo esc_html($change); ?>)</div>
                <?php
            endif;
        ?>
    </td> <!-- td.total ends here -->
</tr>

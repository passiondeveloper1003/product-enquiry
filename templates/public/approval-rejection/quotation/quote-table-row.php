<?php
/**
 * Quote Table Row.
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/approval-rejection/quotation/quote-table-row.php.
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
<tr>
    <?php
    if ($show_price == 'yes') {
        ?>
        <td class="product-price" align="left"><?php
            echo get_the_title($quoteProduct[ 'product_id' ]);
        if ($_product->is_type('variable')) {
            $variationArray = unserialize($quoteProduct['variation']);
            quoteupRemoveClassFilter('woocommerce_attribute_label', 'WCML_WC_Strings', 'translated_attribute_label');
            global $sitepress;
            remove_filter('get_term', array($sitepress, 'get_term_adjust_id'), 1);
            echo printVariations($quoteProduct);
        }
        ?>
        </td>
        <?php
    } else {
        ?>
        <td class="product" align="left"><?php
            echo get_the_title($quoteProduct[ 'product_id' ]);
        if ($_product->is_type('variable')) {
            $variationArray = unserialize($quoteProduct['variation']);
            quoteupRemoveClassFilter('woocommerce_attribute_label', 'WCML_WC_Strings', 'translated_attribute_label');
            global $sitepress;
            remove_filter('get_term', array($sitepress, 'get_term_adjust_id'), 1);
            echo printVariations($quoteProduct);
        }
        ?>
        </td>
        <?php
    }
    if ($show_price == 'yes') {
        ?>
        <td class="sku-price">
            <?php echo $sku;
        ?>
        </td>
        <?php
    } else {
        ?>
        <td class="sku">
            <?php echo $sku;
        ?>
        </td>
        <?php
    }
    if ($show_price == 'yes') {
        ?>
        <td align="left">
            <?php echo wc_price($price);
        ?>
        </td>
        <?php
    }
    ?>
    <td align="left">
        <?php echo wc_price($quoteProduct['newprice']);
        ?>
    </td>
    <td align="left">
        <?php echo $quoteProduct['quantity'];
        ?>
    </td>
    <td align="right">
        <?php
        echo wc_price($quoteProduct['newprice'] * $quoteProduct['quantity']);
        $GLOBALS['total_price'] = $total_price + $quoteProduct['newprice'] * $quoteProduct['quantity'];
        ?>
    </td>
</tr>
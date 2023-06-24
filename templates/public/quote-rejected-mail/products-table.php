<?php
/**
 * Quote Rejected Mail Product Table
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/quote-rejected-mail/products-table.php.
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
<table align="center" class="quote_table wdm-email-table" style="min-width:600px;overflow:auto">
    <?php 
        quoteupGetPublicTemplatePart('quote-rejected-mail/products-table-header', '', $args);
        $grandTotalOldPrice = 0;
        $grandTotalNewPrice = 0;
        $quotation          = $args['quotation_details'];

        foreach ($quotation as $quoteProduct) {
            $_product    = wc_get_product($quoteProduct['product_id']);
            if (empty($_product)) {
                continue;
            }
            $oldPrice             = $quoteProduct['oldprice'];
            $grandTotalOldPrice   = $grandTotalOldPrice + $oldPrice * $quoteProduct['quantity'];
            $grandTotalNewPrice   = $grandTotalNewPrice + $quoteProduct['newprice'] * $quoteProduct['quantity'];
            $args['quoteProduct'] = $quoteProduct;
            $args['_product']     = $_product;
            $args['oldPrice']     = $oldPrice;
            quoteupGetPublicTemplatePart('quote-rejected-mail/products-table-body', "", $args);
        }
    ?>
    <tr border="1">
        <td align="right" <?php echo ('yes' == $args['show_price']) ? 'colspan="5"' : 'colspan="4"' ?>> 
            <?php _e('TOTAL', QUOTEUP_TEXT_DOMAIN); ?> 
        </td>
        <td align="right" >
            <?php
                $change = quoteupReturnOldNewPriceChangeInPerc($grandTotalOldPrice, $grandTotalNewPrice);
              
                if (0 == $change):
                    ?>
                    <div class="quote-final-new-total quote-pdf "><?php echo wc_price($grandTotalNewPrice);?></div>
                <?php
                else:
                    $classAttr = quoteupReturnClassForCostChange($change);
                    $change    = $change . '%';

                    ?>
                    <div class="quote-final-og-total quote-pdf <?php echo esc_attr($classAttr); ?>"><s><?php echo wc_price($grandTotalOldPrice);?></s></div>
                    <div class="quote-final-new-total quote-pdf "><?php echo wc_price($grandTotalNewPrice);?></div>
                    <div class="quote-final-total-change-percetange quote-pdf percentage-diff <?php echo esc_attr($classAttr); ?>">(<?php echo esc_html($change);?>)</div>
                    <?php
                endif;
            ?>
        </td>
    </tr>
</table>

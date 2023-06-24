<table align="center" class="quote_table wdm-email-table" style="min-width:600px;overflow:auto">
    <?php quoteupGetAdminTemplatePart('pdf-quote/quote-table-header', "", $args); ?>
    <?php
    $products                    = unserialize($enquiry_details->product_details);
    $grandTotalOldPrice          = 0;
    $grandTotalNewPrice          = 0;
    $shouldShowPriceDiff         = quoteupShouldShowOldNewPriceDiff($args['enquiry_id']) ;
    $args['shouldShowPriceDiff'] = $shouldShowPriceDiff;

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
        quoteupGetAdminTemplatePart('pdf-quote/quote-table-body', "", $args);
    }
    ?>
    <tr border="1">
        <td align="right" <?php echo ($show_price == 1) ? 'colspan="5"' : 'colspan="4"' ?>> 
            <?php _e('TOTAL', QUOTEUP_TEXT_DOMAIN); ?> 
        </td>
        <td align="right" >
            <?php
            $change = quoteupReturnOldNewPriceChangeInPerc($grandTotalOldPrice, $grandTotalNewPrice);
              
            if (!$shouldShowPriceDiff || 0 == $change) :
                ?>
                <div class="quote-final-new-total quote-pdf "><?php echo wc_price($grandTotalNewPrice);?></div>
                <?php
            else :
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
 

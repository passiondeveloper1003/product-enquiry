<tbody>
    <?php
    use \Includes\Frontend\QuoteUpAddPopupModal;

    $popup               = new QuoteUpAddPopupModal();
    $enquiryId           = $args['enquiryid'];
    $productOldAmount    = 0;
    $productNewAmount    = 0;
    $grandOldTotal       = 0;
    $grandNewTotal       = 0;
    $enquiryProducts     = getQuoteProducts($enquiryId);
    // To check if show price option is enabled at backend.
    $shouldShowOldPrice  = $popup->shouldShowOldPriceForEnquiry($enquiryId);
    $shouldShowPriceDiff = quoteupShouldShowOldNewPriceDiff($enquiryId);

    foreach ($enquiryProducts as $variations) {
        $varationPro      = printVariations($variations);
        $quoteTitle       = $variations['product_title'].$varationPro;
        $quoteOldPrice    = $variations['oldprice'];
        $quoteNewPrice    = $variations['newprice'];
        $quoteQuantity    = $variations['quantity'];
        $productOldAmount = $quoteOldPrice * $quoteQuantity;
        $productNewAmount = $quoteNewPrice * $quoteQuantity;
        $grandOldTotal    = $grandOldTotal + $productOldAmount;
        $grandNewTotal    = $grandNewTotal + $productNewAmount;
        ?>
        <tr class="woocommerce-orders-table__row">
            <td data-title="Product" data-colname="Product" class="prodtitle woocommerce-orders-table__cell woocommerce-orders-table__cell-product proddata">
            <label id="qproduct"><?php echo $quoteTitle ?></label>
            </td>
            <?php
           
            if (true == $shouldShowOldPrice) {
                ?>
                    <td class="price woocommerce-orders-table__cell woocommerce-orders-table__cell-oldprice" data-title="Old Price" data-colname="Old Price">
                        <label id="oprice"><?php echo wc_price($quoteOldPrice); ?></label>
                    </td>
                    <?php
            }
            ?>
            <td class="proddata price woocommerce-orders-table__cell woocommerce-orders-table__cell-newprice" data-title="New Price" data-colname="New Price"><label id="nprice"><?php echo wc_price($quoteNewPrice); ?></label></td>
            <td class="proddata price woocommerce-orders-table__cell woocommerce-orders-table__cell-quantity" data-title="Quantity" data-colname="Quantity"><label id="qty"><?php echo esc_html($quoteQuantity); ?></label></td>
            <td class="proddata price woocommerce-orders-table__cell woocommerce-orders-table__cell-amount" data-title="Amount" data-colname="Amount">
                <?php
                $change = quoteupReturnOldNewPriceChangeInPerc($productOldAmount, $productNewAmount);

                if (!$shouldShowPriceDiff || 0 == $change) {
                    ?>
                        <div class="quoteup-total-amount new-amount"><?php echo wc_price($productNewAmount); ?></div>
                    <?php
                } else {
                    $amountChangePercentageClass = $change > 0 ? 'positive-percent' : 'negative-percent';
                    $change = $change . '%';
                    ?>
                        <div class="quoteup-total-amount old-amount"><s><?php echo wc_price($productOldAmount); ?></s></div>
                        <div class="quoteup-total-amount new-amount"><?php echo wc_price($productNewAmount); ?></div>
                        <div class="quoteup-total-amount amount-change-percentage <?php echo esc_attr($amountChangePercentageClass); ?>">(<?php echo esc_html($change); ?>)</div>
                    <?php
                }
                ?>
            </td>
        </tr>
        <?php
    }
    ?>
    <tr>
        <td class="footer-row" id='amt' colspan="<?php echo (true == $shouldShowOldPrice) ? 4 : 3 ?>">
            <label class="qdetails"><?php _e('Total', QUOTEUP_TEXT_DOMAIN); ?></label>
        </td>
        <td class="price footer-row">
            <?php
            $change = quoteupReturnOldNewPriceChangeInPerc($grandOldTotal, $grandNewTotal);

            if (!$shouldShowPriceDiff || 0 == $change) {
                ?>
                <div class="quoteup-grand-total new-total">
                    <?php echo wc_price($grandNewTotal); ?>
                </div>
                <?php
            } else {
                $amountChangePercentageClass = $change > 0 ? 'positive-percent' : 'negative-percent';
                $change = $change . '%';
                ?>
                    <div class="quoteup-grand-total old-total"><s><?php echo wc_price($grandOldTotal); ?></s></div>
                    <div class="quoteup-grand-total new-total"><?php echo wc_price($grandNewTotal); ?></div>
                    <div class="quoteup-grand-total amount-change-percentage <?php echo esc_attr($amountChangePercentageClass); ?>">(<?php echo esc_html($change); ?>)</div>
                <?php
            }
            ?>
        </td>
    </tr>
</tbody>

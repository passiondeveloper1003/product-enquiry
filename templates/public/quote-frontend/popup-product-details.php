 <h4 class="wdm-modal-title modal-heading"
    <?php
        $formData=quoteupSettings();
        $dialogue_text_color = $formData['dialog_text_color'];
        echo " style='color: ".$dialogue_text_color."; text-align:center'";
    ?>>
    <span class='pr_name'><?php _e('Product Details', QUOTEUP_TEXT_DOMAIN) ?></span>
</h4>
<table class="quotetable woocommerce-orders-table woocommerce-MyAccount-orders product-detail shop_table shop_table_responsive my_account_orders account-orders-table quote-user-table table-bordered table-hover">
    <?php
    $quoteid = $args['enquiryid'];
    $args = array(
        'enquiryid' =>$quoteid
    );

    quoteupGetPublicTemplatePart('quote-frontend/popup-product-details-header', '', $args);
    quoteupGetPublicTemplatePart('quote-frontend/popup-product-details-body', '', $args);
                        
    ?>
</table>

<?php

// Show order link associated with the quote if order has been placed.
$orderId = \Includes\QuoteupOrderQuoteMapping::getOrderIdOfQuote($quoteid);
if (!empty($orderId)) {
    $order   = wc_get_order($orderId);
    $orderLink = $order->get_view_order_url();
    $link = '<center><h3><label>';
    $link .= __('Order associated with the Quote  : ', QUOTEUP_TEXT_DOMAIN);
    $link .= '</label><label>';
    $link .= '<a href="'. esc_url($orderLink) . '" >';
    $link .= $orderId;
    $link .= '</a></label></h3></center>';

    echo $link;
}

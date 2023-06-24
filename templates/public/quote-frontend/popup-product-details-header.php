<?php
    use \Includes\Frontend\QuoteUpAddPopupModal;

    $popup = new QuoteUpAddPopupModal();
    $enquiryid = $args["enquiryid"];
?>
<thead>
    <tr>
        <th scope="col" class="table-head woocommerce-orders-table__header woocommerce-orders-table__header-enquiry_id">
            <label id="Quoteproduct" class="qdetails"><?php _e('Product', QUOTEUP_TEXT_DOMAIN) ?></label>
        </th>                               
        <?php
        // to check if show price option is enabled at backend.
        $flag = $popup->shouldShowOldPriceForEnquiry($enquiryid);
            
        if (true == $flag) {
            ?>
            <th scope="col" class="table-head price woocommerce-orders-table__header woocommerce-orders-table__header-oldprice">
                <label id="Quoteoprice" class="qdetails"><?php _e('Old Price', QUOTEUP_TEXT_DOMAIN) ?></label>
            </th>
            <?php
        }
        ?>
        <th scope="col" class="table-head price woocommerce-orders-table__header woocommerce-orders-table__header-newprice">
        <label id="Quotenprice" class="qdetails"><?php _e('New Price', QUOTEUP_TEXT_DOMAIN) ?></label>
        </th>
        <th scope="col" class="table-head price woocommerce-orders-table__header woocommerce-orders-table__header-quantity">
            <label id="Quoteqty" class="qdetails"><?php _e('Quantity', QUOTEUP_TEXT_DOMAIN) ?></label>
        </th>
        <th scope="col" class="table-head price woocommerce-orders-table__header woocommerce-orders-table__header-amount">
            <label id="Quotetotal" class="qdetails"><?php _e('Amount', QUOTEUP_TEXT_DOMAIN) ?></label>
        </th>
    </tr>
</thead>
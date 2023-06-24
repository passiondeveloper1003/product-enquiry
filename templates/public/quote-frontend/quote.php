<div class="ajaxmodal">
    </div>
<input type="hidden" id="hdnfld" name="hdnfld"/>
<table class="footable responsive" >
    <thead>
        <tr>
            <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-enquiry_id"><span id="Quoteid" class="nobr"><?php _e('ID', QUOTEUP_TEXT_DOMAIN) ?></span></th>
            <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-date"><span id="Quotedate" class="nobr"><?php _e('Date', QUOTEUP_TEXT_DOMAIN) ?></span></th>
            <?php
            if (empty($quotationModActive)) :
            ?>
                <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-status"><span id="Quotestatus" class="nobr"><?php _e('Status', QUOTEUP_TEXT_DOMAIN) ?></span></th>
            <?php
            endif;
            ?>
            <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-price"><span id="Quoteprice" class="nobr"><?php _e('Total', QUOTEUP_TEXT_DOMAIN) ?></span></th>
            <?php
            if (empty($quotationModActive)) :
            ?>
                <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-action"><span id="Quoteaction" class="nobr"><?php _e('Action', QUOTEUP_TEXT_DOMAIN) ?></span></th>
            <?php
            endif;
            ?>
        </tr>
    </thead>
    
</table>
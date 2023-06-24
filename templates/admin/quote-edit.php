<?php

if (!defined('ABSPATH')) {
    exit;
}

$productRowClass = 'wdmpe-detailtbl-content-row';
$productRowClass = apply_filters('quoteup_enquiry_edit_tmpl_product_row_class', $productRowClass);

?>

<script type="text/template" id="tmpl-quote-edit" >
    <tr class="<?php echo $productRowClass; ?>" data-row-num="{{{data.perProductDetail.count}}}">
        <td class="quote-product-remove">
            <a href="#" class="remove" data-row-num="{{{data.perProductDetail.count}}}" data-id="{{{data.perProductDetail.ID}}}" data-product_id="{{{data.perProductDetail.productID}}}" data-variation_id="{{{data.perProductDetail.variationID}}}" data-variation="{{{data.perProductDetail.variationAttributes}}}">×</a>
        </td>';
        <td class="wdmpe-detailtbl-content-item item-content-img">
            <img src= "{{{data.perProductDetail.product_image}}}" class='wdm-prod-img'/>
        </td>
        <td class="wdmpe-detailtbl-content-item item-content-link">
            <a href="{{{data.perProductDetail.url}}}" target='_blank' id='product-title-{{{data.perProductDetail.count}}}'>{{{data.perProductDetail.productTitle}}}</a>
            <!-- {{{data.perProductDetail.variationDetails}}} -->
        </td>
        <td class="wdmpe-detailtbl-content-item item-content-variations" data-row-num="{{{data.perProductDetail.count}}}" id="variations-{{{data.perProductDetail.count}}}" data-product-link="{{{data.perProductDetail.productTitle}}}">
                {{{data.perProductDetail.variationSelector}}}
        </td>
        <td class="wdmpe-detailtbl-content-item item-content-sku">
            <p>{{{data.perProductDetail.sku}}}</p>
        </td>
        <?php
            do_action('quoteup_enquiry_edit_tmpl_after_sku_th');
        ?>
        <td class="wdmpe-detailtbl-content-item item-content-old-cost" data-old_price='{{{data.perProductDetail.oldPriceData}}}'> 
            <span class="woocommerce-Price-amount amount">{{{data.perProductDetail.productPriceSymbol}}}</span>
            <input type="hidden" id="old-price-{{{data.perProductDetail.count}}}" value="{{{data.perProductDetail.productPrice}}}">
        </td>

        <td class="wdmpe-detailtbl-content-item item-content-newcost">
            <input id="content-new-{{{data.perProductDetail.count}}}" data-row-num="{{{data.perProductDetail.count}}}" class="newprice" type="number" name="newprice" value="{{{data.perProductDetail.productPrice}}}" min="0" step="any" >
        </td>
        <td class="wdmpe-detailtbl-content-item item-content-qty" >
            <input data-row-num="{{{data.perProductDetail.count}}}" id="content-qty-{{{data.perProductDetail.count}}}" class="newqty" type="number" name="newqty" value="1" min="1">
        </td>
        <td class="wdmpe-detailtbl-content-item item-content-cost" id="content-cost-{{{data.perProductDetail.count}}}">
            <div class="wdmpe-detailtbl-content-item item-content-og-cost no-cost-change">
                <s>{{{data.perProductDetail.productPriceSymbol}}}</s>
            </div>
            <div class="wdmpe-detailtbl-content-item item-content-new-cost">
                {{{data.perProductDetail.productPriceSymbol}}}
            </div>
            <div class="wdmpe-detailtbl-content-item item-content-cost-change-percentage percentage-diff no-cost-change">
                (0.00%)
            </div>
        </td>
        <input data-row-num="{{{data.perProductDetail.count}}}" id="content-amount-{{{data.perProductDetail.count}}}" class="amount_database" type="hidden" name="price" data-old-value="{{{data.perProductDetail.productPrice}}}" value="{{{data.perProductDetail.productPrice}}}">
        <input data-row-num="{{{data.perProductDetail.count}}}" id="content-ID-{{{data.perProductDetail.count}}}" class="id_database" type="hidden" name="id" value="{{{data.perProductDetail.productID}}}">
    </tr>
</script>

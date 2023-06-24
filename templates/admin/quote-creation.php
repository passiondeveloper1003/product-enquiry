<?php

if (!defined('ABSPATH')) {
    exit;
}
?>

<script type="text/template" id="tmpl-quote-creation" >
    <tr class="wdmpe-detailtbl-content-row quotetbl-content-row wdm-quote-row">
        <td class="quote-product-remove">
            <a href="#" class="remove" data-row-num="{{{data.perProductDetail.count}}}" data-id="{{{data.perProductDetail.ID}}}" data-product_id="{{{data.perProductDetail.productID}}}" data-variation_id="{{{data.perProductDetail.variationID}}}" data-variation="{{{data.perProductDetail.variationAttributes}}}">×</a>
        </td>
        <td class="wdmpe-detailtbl-content-item item-content-img">
            <img src= "{{{data.perProductDetail.product_image}}}" class='wdm-prod-img'/>
        </td>
        <td class="quote-product-title" data-title="Product"> 
            <p><a href="{{{data.perProductDetail.url}}}" target='_blank' id='product-title-{{{data.perProductDetail.count}}}' >{{{data.perProductDetail.productTitle}}}</a></p>
        </td>
        <td class="quote-product-variation" data-row-num="{{{data.perProductDetail.count}}}" id="variations-{{{data.perProductDetail.count}}}" data-product-link="{{{data.perProductDetail.productTitle}}}" data-title="variations">
               <p>{{{data.perProductDetail.variationSelector}}}</p>
        </td>
        <td class="wdmpe-detailtbl-content-item item-content-sku" data-title="SKU">
           <p>{{{data.perProductDetail.sku}}}</p>
        </td>
        <td class="quote-product-sale-price item-content-old-cost" data-old_price='{{{data.perProductDetail.oldPriceData}}}' data-title="Price">
           <p><span class="woocommerce-Price-amount amount">{{{data.perProductDetail.productPriceSymbol}}}</span>
            <input type="hidden" name="content-sale-price" id="content-sale-price-{{{data.perProductDetail.count}}}" data-row-num="{{{data.perProductDetail.count}}}" value="{{{data.perProductDetail.productPrice}}}"></p>
        </td>
        <td class="quote-product-new-price" data-title="New Price"> 
           <p><input id="content-new-{{{data.perProductDetail.count}}}" type="number" data-row-num="{{{data.perProductDetail.count}}}" data-id="{{{data.perProductDetail.ID}}}" data-product_id="{{{data.perProductDetail.productID}}}" data-variation_id="{{{data.perProductDetail.variationID}}}" data-variation="{{{data.perProductDetail.variationAttributes}}}" name="quote-product-new-price" class="wdm-prod-price input-text" value="{{{data.perProductDetail.productPrice}}}" min="0" step="any"></p>
        </td>
        <td class="quote-product-qty input-text qty" data-title="Quantity">
           <p> <input id="content-qty-{{{data.perProductDetail.count}}}" type="number" min="1" step="1" data-row-num="{{{data.perProductDetail.count}}}" data-id="{{{data.perProductDetail.ID}}}" data-product_id="{{{data.perProductDetail.productID}}}" data-variation_id="{{{data.perProductDetail.variationID}}}" data-variation="{{{data.perProductDetail.variationAttributes}}}" name="quote-product-qty" class="wdm-prod-quant quote-newqty newqty input-text qty" value="1"></p>
        </td>
        <td class="quote-product-total" id="content-cost-{{{data.perProductDetail.count}}}" data-title="Total">
            <div class="wdmpe-detailtbl-content-item item-content-og-cost no-cost-change">
                <s>{{{data.perProductDetail.productPriceSymbol}}}</s>
            </div>
            <div class="wdmpe-detailtbl-content-item item-content-new-cost">
                {{{data.perProductDetail.productPriceSymbol}}}
            </div>
            <div class="wdmpe-detailtbl-content-item item-content-cost-change-percentage percentage-diff no-cost-change">
                (0%)
            </div>
        </td>
        <input type="hidden" name="content-amount" class="amount_database" id="content-amount-{{{data.perProductDetail.count}}}" data-row-num="{{{data.perProductDetail.count}}}" data-old-value="{{{data.perProductDetail.productPrice}}}" value="{{{data.perProductDetail.productPrice}}}">
    </tr>
</script>

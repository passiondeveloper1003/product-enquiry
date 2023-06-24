/**
 * @var csp_prices Contains quantity-pricing pairs for different products.
 */
var csp_prices = Array();

(function($){
    $(document).ready(function(){
        $(document).on('input', '.newqty', function(){
            let tr                   = jQuery(this).closest('tr');
            let product_removal_link = tr.find('.quote-product-remove a');
            let current_qty            = tr.find('.item-content-qty input').val();
            let price_field           = tr.find('.item-content-old-cost .woocommerce-Price-amount');
            let old_price_input_field   = tr.find('.item-content-old-cost input');
            let user_email            = jQuery('textarea.wdm-input.input-email').val();
            let product_id            = 0;
            let price = 0.0;

            if (tr.find('input.variation_id').length > 0) {
                product_id = tr.find('input.variation_id').val();
            } else {
                product_id = product_removal_link.data('product_id');
            }

            quoteupFetchCSPPrice(product_id, false, user_email);
            price = quoteupCSPgetPriceFor(current_qty, csp_prices[product_id]['prices']);
            old_price_input_field.val(price);
            price = quoteupFormatPrice(price);
            jQuery(price_field).html(price);
        });

        // When 'Here' button is clicked to refresh the page.
        jQuery('.quoteup-csp-price-update-btn').click(function(){
            let product_ids = quoteupFetchAllProductVaritionIds();
            quoteupFetchCSPPrice(product_ids, false);

            let product_id = 0, price = 0.0, quantity = 0;
            let product_row = jQuery('.wdmpe-quotation-table .wdmpe-detailtbl-content .wdmpe-detailtbl-content-row');

            product_row.each(function(index, element){
                product_id = product_ids[index];
                quantity   = $(element).find('.item-content-qty input').val();
                price      = (0 == csp_prices.length) ? $(element).find('.item-content-old-cost input').val() : quoteupCSPgetPriceFor(quantity, csp_prices[product_id]['prices']);

                jQuery(element).find('.item-content-old-cost input').val(price);

                price = quoteupFormatPrice(price);
                jQuery(element).find('.item-content-old-cost .woocommerce-Price-amount').html(price);

                // Trigger the event to change the price in the 'Amount' column.
                jQuery("input.newqty").trigger("input");
            });
        });

        /*
         * Return the product ids of all the products present in the 'Product
         * Details' section on the enquiry edit page.
         */ 
        function quoteupFetchAllProductVaritionIds()
        {
            let removal_link_elements = jQuery('.wdmpe-detailtbl-content .wdmpe-detailtbl-content-row td a.remove');
            let product_ids = Array(); // it can be product ids or variation ids
            let id = 0;

            removal_link_elements.each(function(index, element){
                id = $(element).data('variation_id');
                if (0 == id) {
                    id = $(element).data('product_id')
                }
                product_ids.push(id);
            });

            return product_ids;
        }
    });
})(jQuery);

/**
* Returns the prices for the specified quantity.
* @param string current_qty Product quantity.
* @param array prices Array of prices for different quantities of product.
* @return Price for the specified quantity.
*/
function quoteupCSPgetPriceFor(current_qty, prices)
{
    current_qty=parseInt(current_qty);
    let price = 0.0;
    let indexes = Array();
    if (!jQuery.isEmptyObject(prices)) {
        jQuery.each(prices, function(index, value){
            indexes.push(parseInt(index));
        });
    }

    price = quoteupCSPqtyInRange(prices, indexes, current_qty);

    return price;
}

/**
* Returns the price appliacble in between the range of min-quantity.
*
* @param array qtyList min-qty min quantity list.
* @param int qty quantity of product
* @param array csp_prices quantity pricing apirs.
*
* @return Price for that quantity range applicable.
*/
function quoteupCSPqtyInRange(csp_prices, qtyList, qty) {
    let qtyListSize = qtyList.length;
    let price = 0.0;
    for (var i in qtyList) {
        var next = parseInt(i, 10) + 1;
        if (qty > qtyList[i]) {
            if (next != qtyListSize && qty < qtyList[next]) {
                price = parseFloat(csp_prices[qtyList[i]]);
                break;
            }
            if (next == qtyListSize) {
                price = parseFloat(csp_prices[qtyList[i]]);
                break;
            }
        } else {
            price = parseFloat(csp_prices[qtyList[i]]);
            break;
        }
    }

    return price;
}

/**
 * Sets the prices for the products in the global variable csp_prices.
 *
 * @param array/int product_id Contains product id or array of product ids.
 * @param bool asyncRequest Flag represents whether the request should be sync or async.
 * @param string user_email User Email who CSP price should be fetched for.
 *
 * @retun void
 */
function quoteupFetchCSPPrice(product_id, asyncRequest = true, user_email = '',)
{
    if (Array.isArray(product_id)) {
        /*
         * When there is an array of product Ids, the variable csp_prices is emptied
         * and csp prices are fetched for all the products. If the variable csp_prices
         * is not emptied here, then there is a need to loop through all the product
         * Ids and process accordingly after fetching the prices for different products.
         */
        csp_prices  = Array();
        product_ids = product_id;
    } else {
        product_ids = [product_id];
    }

    if (0 == csp_prices.length || jQuery.isEmptyObject(csp_prices[product_id])) {
        if ('' == user_email) {
            user_email = jQuery('textarea.wdm-input.input-email').val();
        }

        jQuery.ajax({
            url: ajaxurl,
            type: "post",
            async: asyncRequest,
            dataType: "JSON",
            data: {
                action: 'pep_get_csp_prices_for_product',
                nonce: pep_csp_comp.nonce,
                product_ids: product_ids,
                user_email: user_email
            },
            timeout: 60000,
            error: function (jqXHR, textStatus) {
                alert(textStatus);
            },
            success: function (response) {
                if (true == response.error) {
                    return response;
                }

                if (Array.isArray(product_id)) {
                    csp_prices = response.cspPrices;                
                } else {
                    csp_prices[product_id] = response.cspPrices[product_id];
                }
            },
        });
    }
}

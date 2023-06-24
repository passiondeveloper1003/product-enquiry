/**
* This js file is to remove add to cart option for variable products on the archive page.
*/
/*global quoteup_add_to_cart_disabled_variable_products, quoteup_hide_variation_variable_products */
jQuery(document).ready(function () {
    var $allVariationForms  = jQuery('form.variations_form.cart');
    var variable_product_id = $allVariationForms.data('product_id');
    var quoteupButtonExists = jQuery('#wdm-quoteup-trigger-' + variable_product_id).length > 0 ? true : false;

    if ( typeof quoteup_add_to_cart_disabled_variable_products != 'undefined' ) {// Any scope
        for ($i = 0; $i < quoteup_add_to_cart_disabled_variable_products.length; $i++) {
        //Hide Select Option button on archive page for products having 'Enable add to cart' set to disabled.
            jQuery('.product_type_variable[data-product_id="' + quoteup_add_to_cart_disabled_variable_products[$i] + '"]').remove();

        //Hide Add to Cart button on single product page if 'Add to Cart' is disabled
            if ( $allVariationForms.length ) {
                if ( $allVariationForms.length > 1 ) {
                    $allVariationForms.each(function () {
                        if ( jQuery(this).data('product_id') == quoteup_add_to_cart_disabled_variable_products[$i] ) {
                            if (typeof quoteup_remove_qty_field != "undefined" && "yes" == quoteup_remove_qty_field.remove_qty_field) {
                                $allVariationForms.find('.quantity').remove();
                            }
                            jQuery(this).find('.single_add_to_cart_button').remove();
                        }
                    })
                } else {
                    if ( $allVariationForms.data('product_id') == quoteup_add_to_cart_disabled_variable_products[$i] ) {
                        if (typeof quoteup_remove_qty_field != "undefined" && "yes" == quoteup_remove_qty_field.remove_qty_field) {
                            $allVariationForms.find('.quantity').remove();
                        }
                        $allVariationForms.find('.single_add_to_cart_button').remove();
                    }
                }
            }
        }
    }

    if ( typeof quoteup_hide_variation_variable_products != 'undefined' ) {
        //Hide variation on single product page if Add to Cart and Enquiry/Quote request both are disabled
        for ($i = 0; $i < quoteup_hide_variation_variable_products.length; $i++) {
            jQuery('.variations_form[data-product_id="' + quoteup_hide_variation_variable_products[$i] + '"]').remove();
        }
    }

    if ( typeof quoteup_price_disabled_variable_products != 'undefined' ) {
        $product_id = jQuery('.variations_form.cart').data('product_id');
        //Hide variation price on single product page if Show Price is disabled.
        for ($i = 0; $i < quoteup_price_disabled_variable_products.length; $i++) {
            if (quoteup_price_disabled_variable_products[$i] == $product_id) {
                jQuery('body').append("<style> .woocommerce-variation-price { display : none; } </style>")
                break;
            }
        }
    }
});

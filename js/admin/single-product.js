/*
*  If enable price is checked then only provide option of Add to Cart.
*/
jQuery(window).on("load", function () {
    // On Product page in dashborad checkbox of enable price.
    var enable_price_meta_on_load = jQuery("input[name='_enable_price']");
    if ( enable_price_meta_on_load.length && !enable_price_meta_on_load.is(":checked") ) {
        jQuery("#wdm_enable_add_to_cart:not(.quoteup_hide)").hide();
        jQuery("#wdm_enable_add_to_cart:not(.quoteup_hide) input[name='_enable_add_to_cart']").prop('disabled', true);
    }
    
    // If price is not checked do not enable add to cart
    jQuery("input[name='_enable_price']").change(function () {
        if ( !jQuery(this).is(":checked") ) {
            jQuery("#wdm_enable_add_to_cart:not(.quoteup_hide)").hide();
            jQuery("#wdm_enable_add_to_cart:not(.quoteup_hide) input[name='_enable_add_to_cart']").prop('disabled', true);
        } else {
            jQuery("#wdm_enable_add_to_cart:not(.quoteup_hide)").show();
            jQuery("#wdm_enable_add_to_cart:not(.quoteup_hide) input[name='_enable_add_to_cart']").prop('disabled', false);
        }
    });
});



var public_quoteup_show;
var public_quoteup_get_cart;

(function( $ ) {
    $(document).ready(function(){
        $('body').on('click','.pep-mc-collapse',function () {
            quoteup_hide();            
        });

        var $wdmCartCount = jQuery('#wdm-cart-count');
        $wdmCartCount.click(function(e){
            e.preventDefault();
            if ($wdmCartCount.hasClass('active')) {
                quoteup_hide();
            } else {
                quoteup_show();
                // quoteup_get_cart();
            }
        });

        $('body').on('click','.quoteup_remove_item',function (e) {
            e.preventDefault();
            $self = jQuery(this);
            product_id = $self.attr('data-product_id');
            product_var_id = $self.attr('data-variation_id');
            product_variation_details = $self.attr('data-variation');
          
          
            jQuery.ajax({
                url: mini_cart.ajax_url,
                type: 'post',
                dataType: 'json',
                data:
                {
                    action: 'wdm_update_enq_cart_session',
                    'product_id': product_id,
                    'product_var_id': product_var_id,
                    'quantity': 0,
                    'variation' : JSON.parse(product_variation_details),
                    'clickcheck': 'remove'
                },
                beforeSend: function () {
                    $self.closest('.pep-mc-item').remove();
                },
                success: function ( response ) {
                    jQuery('.quote-form .wdm_enquiry').html(response.cartCustomLabel).removeClass('added'); 
                    jQuery('.quote-form .wdm_enquiry').text(response.customLabel);
                    jQuery('.quote-form .wdm_enquiry').removeAttr("onclick");
                    jQuery('.wdm-quoteupicon-count').text(response.count);
                    jQuery('#wdm-cart-count').children('a').attr('title', response.product_count_in_text);
                    $('.pep-mc-footer .pep-mc-price').html(response.totalString);
                },
                error: function ( error ) {
                    console.log(error);
                }
            });
        });
    });

    function quoteup_hide() {
        var cssProperty = 'right';
        if ($('.pep-mc-wrap').hasClass('left')) {
            cssProperty = 'left';
        }
        $('.pep-mc-wrap').css(cssProperty, '-430px');
        $('.pep-mc-footer').css(cssProperty, '-430px');
        $('#wdm-cart-count').show();
        jQuery('#wdm-cart-count').removeClass('active');
    }

    function quoteup_show() {
        var cssProperty = 'right';
        if ($('.pep-mc-wrap').hasClass('left')) {
            cssProperty = 'left';
        }
        $('.pep-mc-wrap').css(cssProperty, '0px');
        $('.pep-mc-footer').css(cssProperty, '0px');
        $('#wdm-cart-count').hide();
        jQuery('#wdm-cart-count').addClass('active');
    }

    function quoteup_get_cart() {
        $( '.quoteup_items_wrap' ).addClass( 'quoteup_items_wrap_loading' );
        var data = {
            action: 'quoteup_get_cart',
        };
        $.post( mini_cart.ajax_url, data, function( response ) {
            var cart_response = JSON.parse( response );
            $( '.pep-mc-items' ).html( cart_response['html'] );
            $('.pep-mc-footer .pep-mc-price').html(cart_response['totalString']);
        } );
    }

    public_quoteup_show = quoteup_show;
    public_quoteup_get_cart = quoteup_get_cart;

})( jQuery );

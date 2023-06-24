jQuery(function () {
    jQuery('#enable_disable_mpe').on('change', function () {
        // When 'Enable Multiproduct Enquiry and Quote Request' setting is changed.
        if (jQuery(this).prop('checked')) {
            jQuery('.quoteup-enable-disable-mpe-depd').removeClass('hide');
        } else {
            jQuery('.quoteup-enable-disable-mpe-depd').addClass('hide');
        }

        if (jQuery('#enable_disable_mpe').prop('checked') && !jQuery('#gen_enq_cart_page').prop('checked')) {
            jQuery('.quoteup-gen-enq-cart-page-depd').removeClass('hide');
        } else {
            jQuery('.quoteup-gen-enq-cart-page-depd').addClass('hide');
        }
    });

    // When 'Auto Generate Enquiry Cart Page' setting is changed.
    jQuery('#gen_enq_cart_page').on('change', function () {
        if (jQuery('#enable_disable_mpe').prop('checked') && !jQuery('#gen_enq_cart_page').prop('checked')) {
            jQuery('.quoteup-gen-enq-cart-page-depd').removeClass('hide');
        } else {
            jQuery('.quoteup-gen-enq-cart-page-depd').addClass('hide');
        }
    });

    // When 'Enable Quotation System' setting is changed.
    jQuery('#enable_disable_quote').on('change', function () {
        if (jQuery(this).prop('checked')) {
            jQuery('.quoteup-enable-disable-quote-depd').removeClass('hide');
        } else {
            jQuery('.quoteup-enable-disable-quote-depd').addClass('hide');
        }

        if (jQuery('#enable_disable_quote').prop('checked') && !jQuery('#gen_quote_app_rej_page').prop('checked')) {
            jQuery('.quoteup-gen-quote-app-rej-page-depd').removeClass('hide');
        } else {
            jQuery('.quoteup-gen-quote-app-rej-page-depd').addClass('hide');
        }

        if (jQuery('#enable_disable_quote').prop('checked') && jQuery('#enable_disable_quote_pdf').prop('checked')) {
            jQuery('.quoteup-enable-pdf-depd').removeClass('hide');
        } else {
            jQuery('.quoteup-enable-pdf-depd').addClass('hide');
        }
    });

    // When 'Enable PDF' setting is changed.
    jQuery('#enable_disable_quote_pdf').on('change', function () {
        if (jQuery(this).prop('checked')) {
            jQuery('.quoteup-enable-pdf-depd').removeClass('hide');
        } else {
            jQuery('.quoteup-enable-pdf-depd').addClass('hide');
        }
    });

    // When 'Auto Generate Quote Approval/ Rejection Page' setting is changed.
    jQuery('#gen_quote_app_rej_page').on('change', function () {
        if (jQuery('#enable_disable_quote').prop('checked') && !jQuery('#gen_quote_app_rej_page').prop('checked')) {
            jQuery('.quoteup-gen-quote-app-rej-page-depd').removeClass('hide');
        } else {
            jQuery('.quoteup-gen-quote-app-rej-page-depd').addClass('hide');
        }
    });
});

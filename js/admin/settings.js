jQuery(document).ready(function () {
// Settings page script actions
jQuery('.wdm-button-color-field').wpColorPicker();
jQuery('#button_text_color').wpColorPicker();
jQuery('#button_border_color').wpColorPicker();
jQuery('#dialog_color').wpColorPicker();
jQuery('#dialog_text_color').wpColorPicker();
jQuery('#dialog_product_color').wpColorPicker();

    // Trigger WooCommerce Tooltips. This is used to trigger tooltips added by function \wc_help_tip
    var tiptip_args = {
        'attribute': 'data-tip',
        'fadeIn': 50,
        'fadeOut': 50,
        'delay': 200
    };
    jQuery('.tips, .help_tip, .woocommerce-help-tip').tipTip(tiptip_args);

    jQuery('#wdm_display').on("click", "#manual-css:checked", function () {
        jQuery('#Other_Settings').css('display', 'block');
    });

    jQuery('#wdm_display').on("click", '#theme-css:checked', function () {
        jQuery('#Other_Settings').css('display', 'none');
    });

    jQuery('#wdm_form').on("click", "#default:checked", function () {
        jQuery('#default_Settings').css('display', 'block');
        jQuery('#custom_Settings').css('display', 'none');
    });

    jQuery('#wdm_form').on("click", "#custom:checked", function () {
        jQuery('#custom_Settings').css('display', 'block');
        jQuery('#default_Settings').css('display', 'none');
    });

    var cust_email_template = jQuery('#custom_email_template').is(":checked");

    if ( cust_email_template ) {
        jQuery('#wdm_custom_email_template').css('display', 'block');
    }

    jQuery(document).on("click", '#custom_email_template:checked', function () {
        jQuery('#wdm_custom_email_template').css('display', 'block');
    });

    jQuery(document).on("click", '#default_email_template:checked', function () {
        jQuery('#wdm_custom_email_template').css('display', 'none');
    });

//Migration no longer in use
jQuery('#btnMigrate').click(function () {
    jQuery(this).attr('disabled', 'disabled');
    jQuery('.wdm-migrate-txt').animate({
        opacity: 0
    }, 400, function () {
        jQuery(this).css('display', 'none');
    });
    jQuery('.wdm-migrate-loader-wrap').animate({
        opacity: 0
    }, 400, function () {
        jQuery(this).css('display', 'inline-block');
        req_migration = {
            action: 'migrateScript',
            'security': jQuery('#migratenonce').val(),
        };

        jQuery.post(data.ajax_admin_url, req_migration, function ( response ) {
            if ( response == 'SECURITY_ISSUE' ) {
                alert(data.could_not_migrate_enquiries);
                jQuery('.wdm-migrate-txt').animate({
                    opacity: 1
                }, 400, function () {
                 jQuery(this).css('display', 'initial');
                 jQuery('#btnMigrate').removeAttr('disabled');
             });
                jQuery('.wdm-migrate-loader-wrap').animate({
                    opacity: 1
                }, 400, function () {
                  jQuery(this).css('display', 'none');
              });
                return false;
            }
            jQuery('.wdm-migrate-txt').animate({
                opacity: 1
            }, 400, function () {
                jQuery(this).css('display', 'initial');
                jQuery('#btnMigrate').removeAttr('disabled');
            });
            jQuery('.wdm-migrate-loader-wrap').animate({
                opacity: 1
            }, 400, function () {
                jQuery(this).css('display', 'none');
            });
        });
    });
});
    // settings tab display
    jQuery('#tab-container').easytabs({updateHash: false});
    $selectedTab = jQuery('#tab-container .etabs li.active').find('.active').closest('a').attr("href");
    $url = jQuery('input[name="_wp_http_referer"]').val();
    if ($url.indexOf("#wdm_") !== -1) {
      $url = $url.substr(0, $url.indexOf("#wdm_"));
  }
  if($selectedTab == '#wdm_other_extensions' || $selectedTab == '#wdm_whats_new') {
    quoteupWhatsNewTabVisited();
    jQuery('#wdm_ask_button').hide();
} else {
    jQuery('#wdm_ask_button').show();
}
  // reference to selected settings tab
  jQuery('input[name="_wp_http_referer"]').val($url+$selectedTab)
  jQuery('#tab-container')
  .bind('easytabs:after', function () {
          $selectedTab = jQuery(this).find('.active').closest('a').attr("href");
        // jQuery['input[name="_wp_http_referer"']
        $url = jQuery('input[name="_wp_http_referer"]').val();
        if ($url.indexOf("#wdm_") !== -1) {
            $url = $url.substr(0, $url.indexOf("#wdm_"));
        }
        jQuery('input[name="_wp_http_referer"]').val($url+$selectedTab)

        if($selectedTab == '#wdm_other_extensions' || $selectedTab == '#wdm_whats_new') {
            quoteupWhatsNewTabVisited();
            jQuery('#wdm_ask_button').hide();
        } else {
            jQuery('#wdm_ask_button').show();
        }
    });

    // When any checkbox on the settinga page is changed, find out next hidden field and set it to 1 or 0
    jQuery('.wdm_wpi_checkbox').change(function () {
        var nextHiddenField = jQuery(this).next("input[type='hidden']");
        if ( jQuery(this).is(':checked') ) {
         nextHiddenField.val('1');
     } else {
         nextHiddenField.val('0');
     }
 });

    //Show or hide telephone number related fields on page load
    var phNumber = jQuery('#enable_telephone_no_txtbox').is(':checked');
    if ( phNumber ) {
        jQuery('.toggle').show();
    } else {
        jQuery('.toggle').hide()
    }

    //Show or hide telephone number related fields on click
    jQuery('#enable_telephone_no_txtbox').click(function () {
        if ( jQuery(this).is(':checked') ) {
         jQuery('.toggle').show();
     } else {
         jQuery('.toggle').hide()
     }
 });

    //Show or hide date related fields on page load
    var dateField = jQuery('#enable_date_field').is(':checked');
    if ( dateField ) {
        jQuery('.toggle-date').show();
    } else {
        jQuery('.toggle-date').hide()
    }

    //Show or hide date related fields on click
    jQuery('#enable_date_field').click(function () {
        if ( jQuery(this).is(':checked') ) {
         jQuery('.toggle-date').show();
     } else {
         jQuery('.toggle-date').hide()
     }
 });

    //Show or hide attach related fields on page load
    var attachField = jQuery('#enable_attach_field').is(':checked');
    if ( attachField ) {
        jQuery('.toggle-attach').show();
    } else {
        jQuery('.toggle-attach').hide()
    }

    //Show or hide Attach related fields on click
    jQuery('#enable_attach_field').click(function () {
        if ( jQuery(this).is(':checked') ) {
         jQuery('.toggle-attach').show();
     } else {
         jQuery('.toggle-attach').hide()
     }
 });

     //Show or hide captcha related fields on page load
     var attachField = jQuery('#enable_google_captcha').is(':checked');
     if ( attachField ) {
        jQuery('.toggle-captcha').show();
    } else {
        jQuery('.toggle-captcha').hide()
    }

    //Show or hide captcha related fields on click
    jQuery('#enable_google_captcha').click(function () {
        if ( jQuery(this).is(':checked') ) {
         jQuery('.toggle-captcha').show();
     } else {
         jQuery('.toggle-captcha').hide()
     }
 });

    //Show or hide PDF related fields on page load
    var pdfOptions = jQuery('#enable-disable-pdf').is(':checked');
    if ( pdfOptions ) {
        jQuery('.toggle-pdf').show();
    } else {
        jQuery('.toggle-pdf').hide()
    }

    //Show or hide Attach related fields on click
    jQuery('#enable-disable-pdf').click(function () {
        if ( jQuery(this).is(':checked') ) {
           jQuery('.toggle-pdf').show();
       } else {
           jQuery('.toggle-pdf').hide()
       }
   });


    // Enable quotation system checkbox
    var quoteVisibilityOnFirstLoad = jQuery('#quote-enable-disable').is(':checked');
    
    var previousStatus = quoteVisibilityOnFirstLoad;
    //If default value is not set, set it to 'yes'
    if ( quoteVisibilityOnFirstLoad ) {
        quoteVisibilityOnFirstLoad = 'yes';
        jQuery('#quote-settings').hide();
    } else {
        jQuery('#quote-settings').show();
    }

        // Enable quotation system checkbox when changed
        jQuery("#quote-enable-disable").change(function () {
            var newval = jQuery("#quote-enable-disable").is(':checked');
            if ( !newval ) {
                jQuery('#quote-settings').show();
            } else {
                jQuery('#quote-settings').hide();
            }
        });

// Enable multiproduct enquiry checkbox
var val = jQuery("#enable_disable_mpe").is(':checked');
if ( val ) {
    jQuery('.quote_cart').show();
} else {
    jQuery('.quote_cart').hide();
}

// Enable multiproduct enquiry checkbox when changed
jQuery("#enable_disable_mpe").change(function () {
    var newval = jQuery("#enable_disable_mpe").is(':checked');
    if ( newval ) {
        jQuery('.quote_cart').show();
    } else {
        jQuery('.quote_cart').hide();
    }
    manipulateEnquiryMailAndCartOptions();
});


// Enable/ Disable 'Expected Price or Remarks column Label' setting
jQuery("#enable_remarks_col").change(function () {
    manipulateEnquiryMailAndCartOptions();
});

// Enable Specify Icon Color checkbox.
var val = jQuery("#enable-manual-ec-icon-color").is(':checked');
if (val) {
    jQuery('.manual-ec-icon-color-depd').show();
} else {
    jQuery('.manual-ec-icon-color-depd').hide();
}

// Enable Specify Icon Color checkbox when changed.
jQuery("#enable-manual-ec-icon-color").change(function () {
    var newval = jQuery("#enable-manual-ec-icon-color").is(':checked');
    if (newval) {
        jQuery('.manual-ec-icon-color-depd').show();
    } else {
        jQuery('.manual-ec-icon-color-depd').hide();
    }
});

// Show/ hide 'Subscription Checkbox Label' setting.
var val = jQuery("#enable_mc_subs_cb").is(':checked');
if ( val ) {
    jQuery('.enable_mc_subs_cb_depd').show();
} else {
    jQuery('.enable_mc_subs_cb_depd').hide();
}

// Show/ hide 'Subscription Checkbox Label' setting when changed.
jQuery('#enable_mc_subs_cb').change(function(){
    var newval = jQuery("#enable_mc_subs_cb").is(':checked');
    if (newval) {
        jQuery('.enable_mc_subs_cb_depd').show();
    } else {
        jQuery('.enable_mc_subs_cb_depd').hide();
    }
});

//Enable terms and conditions checkbox
var val = jQuery("#enable_terms_conditions").is(':checked');
if ( val ) {
    jQuery('.enquiry-privacy-policy').show();
} else {
    jQuery('.enquiry-privacy-policy').hide();
}

//Enable terms and conditions checkbox when changed
jQuery("#enable_terms_conditions").change(function () {
    var newval = jQuery("#enable_terms_conditions").is(':checked');
    if ( newval ) {
        jQuery('.enquiry-privacy-policy').show();
    } else {
        jQuery('.enquiry-privacy-policy').hide();
    }
});

// Enable Cookie Consent checkbox
var val = jQuery("#enable_cookie_consent").is(':checked');
if ( val ) {
    jQuery('.cookie-consent-text').show();
} else {
    jQuery('.cookie-consent-text').hide();
}

// Enable Cookie Consent checkbox when changed
jQuery("#enable_cookie_consent").change(function () {
    var newval = jQuery("#enable_cookie_consent").is(':checked');
    if ( newval ) {
        jQuery('.cookie-consent-text').show();
    } else {
        jQuery('.cookie-consent-text').hide();
    }
});

// Check if 'Hide Price on All Products' setting is enabled
var val = jQuery("#hide_price_on_all_products").is(':checked');
if ( val ) {
    jQuery('.hide_add_to_cart_on_all_products_wrapper').hide();
} else {
    jQuery('.hide_add_to_cart_on_all_products_wrapper').show();
}

/*
 * Show 'Hide Add to Cart on All Products' setting if
 * 'Hide Price on All Products' setting is disabled, otherwise
 * hide 'Hide Add to Cart on All Products' setting.
 */
jQuery("#hide_price_on_all_products").change(function () {
    var newval = jQuery("#hide_price_on_all_products").is(':checked');
    if ( newval ) {
        jQuery('.hide_add_to_cart_on_all_products_wrapper').hide();
    } else {
        jQuery('.hide_add_to_cart_on_all_products_wrapper').show();
    }
});

// Show vendor related settings if 'Compatibility with vendors' setting is
// enabled.
var val = jQuery("#enable_comp_vendors").is(':checked');
if (val) {
    jQuery('.enable-comp-vendors-depended').show();
    jQuery('.vendor-visible-form-fields-fieldset').show();
    jQuery('#send-mail-to-product-author').prop("checked", true);
    jQuery('#send-mail-to-product-author').prop("disabled", true);
    jQuery('#send-mail-to-product-author').change();
} else {
    jQuery('.enable-comp-vendors-depended').hide();
    jQuery('.vendor-visible-form-fields-fieldset').hide();
}

// Show/ hide vendor related settings when 'Compatibility with vendors'
// checkbox setting is changed.
jQuery("#enable_comp_vendors").change(function () {
    var newval = jQuery("#enable_comp_vendors").is(':checked');
    if (newval) {
        jQuery('.enable-comp-vendors-depended').show();
        jQuery('.vendor-visible-form-fields-fieldset').show();
        jQuery('#send-mail-to-product-author').prop("checked", true);
        jQuery('#send-mail-to-product-author').prop("disabled", true);
        jQuery('#send-mail-to-product-author').change();
    } else {
        jQuery('.enable-comp-vendors-depended').hide();
        jQuery('.vendor-visible-form-fields-fieldset').hide();
        if (!(jQuery('#send-mail-to-product-author')[0].getAttribute("checked") == "checked")) {
            jQuery('#send-mail-to-product-author').prop("checked", false);
            jQuery('#send-mail-to-product-author').change();
        }
        jQuery('#send-mail-to-product-author').prop("disabled", false);
    }
    showHideSetDeadlineAfterHours();
});

// Show/ hide when 'enable_auto_set_deadline_vendor' setting is changed.
jQuery('#enable_auto_set_deadline_vendor').change(function(){
    showHideSetDeadlineAfterHours();
});

// Product form save chnages posts the data
jQuery("#ask_product_form").submit(function ( e ) {

    error = 0;
    em_regex = /^(\s)*(([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+)((\s)*,(\s)*(([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+)(\s)*(,)*)*(\s)*$/;
    email = jQuery('#wdm_user_email').val();
        // Kept in comments Just for Future reference.
        // if ( email == '' ) {
        //     jQuery('.email_error').text(data.name_req);
        //     error = 1;
        // } else 
        if (email != '' && !em_regex.test(email) ) {
            jQuery('.email_error').text(data.valid_name);
            error = 1;
        } else {
            jQuery('.email_error').text('');
        }
        if ( error == 1 ) {
            return false;
        }

    });

    // jQuery('.anonymize-form-fields-accordion').accordion({
    //     collapsible: true
    // });

    var acc = document.getElementsByClassName("wdm-accordion");
    var i;

    for (i = 0; i < acc.length; i++) {
        acc[i].addEventListener("click", function() {
            this.classList.toggle("active");
            var panel = this.nextElementSibling;
            if (panel.style.display === "block") {
                panel.style.display = "none";
            } else {
                panel.style.display = "block";
            }
        });
    }
    // Open first accordion
    acc[0].click();


    function manipulateEnquiryMailAndCartOptions()
    {
        let isMPEEnabled = jQuery("#enable_disable_mpe").is(':checked');
        
        if (isMPEEnabled) {
            let isRemarksColEnabled = jQuery("#enable_remarks_col").is(':checked');

            jQuery('.enquiry-mail-opt').hide();
            jQuery('.enquiry-mail-cart-cust-opt').show();

            jQuery('.checkbox-coll-header.enq-mail-table').hide();
            jQuery('.checkbox-coll-header.enq-cart-mail-table').show();

            jQuery('.checkbox-coll.remarks-col-setting').show();
            if (isRemarksColEnabled) {
                jQuery('.expected_price_remarks_label_wrapper').show();
                jQuery('.expected_price_remarks_col_phdr_wrapper').show();
            } else {
                jQuery('.expected_price_remarks_label_wrapper').hide();
                jQuery('.expected_price_remarks_col_phdr_wrapper').hide();
            }
        } else {
            jQuery('.enquiry-mail-cart-cust-opt').hide();
            jQuery('.enquiry-mail-opt').show();

            jQuery('.checkbox-coll-header.enq-mail-table').show();
            jQuery('.checkbox-coll-header.enq-cart-mail-table').hide();

            jQuery('.checkbox-coll.remarks-col-setting').hide();
            jQuery('.expected_price_remarks_label_wrapper').hide();
            jQuery('.expected_price_remarks_col_phdr_wrapper').hide();
        }
    }

    // Show and hide 'set_deadline_after_days' setting.
    function showHideSetDeadlineAfterHours()
    {
        let isVendorCompEnabled  = jQuery("#enable_comp_vendors").is(':checked'),
        isAutoSetDeadlineEnabled = jQuery("#enable_auto_set_deadline_vendor").is(':checked');

        if (isVendorCompEnabled && isAutoSetDeadlineEnabled) {
            jQuery('.enable-auto-set-dl-depended').show();
        } else {
            jQuery('.enable-auto-set-dl-depended').hide();
        }
    }

    function quoteupWhatsNewTabVisited()
    {
        let ajaxdata = {
            action: 'wdm_whats_new_visited',
        };
        
        jQuery.ajax( {
            type: 'POST',
            url: ajaxurl,
            data: ajaxdata,
            success: function ( response ) {
                if (response == 'valid') {
                    jQuery('.pep-update-dot').hide();
                }
            },
        } );
    }

    manipulateEnquiryMailAndCartOptions();
    showHideSetDeadlineAfterHours();
});


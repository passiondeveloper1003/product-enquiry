var validated = true;
var error_val = 0;
var err_string = '';
jQuery(document).ready(function () {

    var triggerUpdateCartAutomatically = false;
    // when the send button is clicked on Quote  cart for sending enquiry of multiple products.
    jQuery("body").on('click', '#btnMPESend', function ( e ) {
        err_string = '';
        error_val = 0;
        jQuery(".load-send-quote-ajax").addClass('loading');
        triggerUpdateCartAutomatically = true;
        // sendRequestToUpdateCart(false, false);
        var $this = jQuery(this);

        e.preventDefault();
        var path = jQuery('#site_url').val();
        var cust_name = jQuery('#frm_mpe_enquiry').find('#custname').val();
        var email = jQuery('#frm_mpe_enquiry').find('#txtemail').val();
        var subject = jQuery('#frm_mpe_enquiry').find('#txtsubject').val();
        var message = jQuery('#frm_mpe_enquiry').find('#txtmsg').val();
        var phone = jQuery('#frm_mpe_enquiry').find('#txtphone').val();
        var uemail = jQuery('#author_email').val();
        var fields = wdm_data.fields;

        nm_regex = /^[a-zA-Z ]+$/;
        var enquiry_field;

        if ( fields.length > 0 ) {
            jQuery('#wdm-quoteupform-error').css('display', 'none');
            jQuery('.error-list-item').remove();
            jQuery('.wdm-error').removeClass('wdm-error');
            for (i = 0; i < fields.length; i++) {
                var temp = jQuery('#frm_mpe_enquiry').find('#' + fields[i].id).val();

                var required = fields[i].required;
                if ( fields[i].validation != "" ) {
                    var validation = new RegExp(fields[i].validation);
                }

                var message = fields[i].validation_message;
                var flag = 0;
                if ( required == 'yes' ) {

                    if (fields[i].type == "file")
                    {

                        enquiry_field = jQuery('#frm_mpe_enquiry').find('#' + fields[i].id);

                        var attachedFiles = enquiry_field.prop('files');

                        if (attachedFiles.length < 1 || attachedFiles.length > 10) {
                            enquiry_field.addClass('wdm-error');
                            flag = 1;
                            error_val = 1;
                            err_string += '<li class="error-list-item">' + fields[i].required_message + '</li>';
                        } else {
                            flag = 0;
                            enquiry_field.removeClass('wdm-error');
                        }
                    }else if ( fields[i].type == "text" || fields[i].type == "textarea" ) {
                        enquiry_field = jQuery('#frm_mpe_enquiry').find('#' + fields[i].id);
                        if ( temp == "" ) {
                            enquiry_field.addClass('wdm-error');
                            flag = 1;
                            error_val = 1;
                            err_string += '<li class="error-list-item">' + fields[i].required_message + '</li>';
                        } else {
                            flag = 0;
                            enquiry_field.removeClass('wdm-error');
                        }
                    } else if ( fields[i].type == "radio" ) {
                        jQuery('#frm_mpe_enquiry').find("[name=" + fields[i].id + "]").each(function () {

                            var temp1 = jQuery(this);
                            if ( temp1.is(":checked") ) {
                                flag = 1;
                            }
                        });

                        if ( flag == 0 ) {
                            error_val = 1;
                            jQuery('#frm_mpe_enquiry').find('#' + fields[i].id).parent().css("cssText", "background:#FCC !important;");
                            err_string += '<li class="error-list-item">' + fields[i].required_message + '</li>';
                        } else {
                            jQuery('#frm_mpe_enquiry').find('#' + fields[i].id).parent().css("cssText", "background:white !important;");
                        }
                    } //radio

                    else if ( fields[i].type == "checkbox" ) {
                        jQuery('#frm_mpe_enquiry').find("input[name=" + fields[i].id + "\\[\\]]").each(function () {

                            var temp1 = jQuery(this);

                            if ( temp1.is(":checked") ) {
                                flag = 1;
                            }
                        });
                        if ( flag == 0 ) {
                            error_val = 1;
                            jQuery('#frm_mpe_enquiry').find('#' + fields[i].id).parent().css("cssText", "background:#FCC !important;");
                            err_string += '<li class="error-list-item">' + fields[i].required_message + '</li>';
                        } else {
                            jQuery('#frm_mpe_enquiry').find('#' + fields[i].id).parent().css("cssText", "background:white !important;");
                        }
                    } //checkbox
                    else if ( fields[i].type == "select" ) {
                        jQuery('#frm_mpe_enquiry').find("[name=" + fields[i].id + "]").each(function () {
                            var temp1 = jQuery(this);
                            if ( temp1.val() != "#" ) {
                                flag = 1;
                            }
                        });
                        if ( flag == 0 ) {
                            error_val = 1;
                            jQuery('#frm_mpe_enquiry').find('#' + fields[i].id).parent().css("cssText", "background:#FCC !important;");
                            err_string += '<li class="error-list-item">' + fields[i].required_message + '</li>';
                        } else {
                            jQuery('#frm_mpe_enquiry').find('#' + fields[i].id).parent().css("cssText", "background:white !important;");
                        }
                    }
                }//required

                if ( flag == 0 ) {
                    if ( fields[i].validation != "" && temp != "" ) {
                        if ( !validation.test(temp) ) {
                            enquiry_field = jQuery('#frm_mpe_enquiry').find('#' + fields[i].id);
                            enquiry_field.addClass('wdm-error');
                            err_string += '<li class="error-list-item">' + message + '</li>';
                            error_val = 1;
                        }
                    }
                }
            }//for feilds loop
        }//if


        if ( error_val == 0 ) {
            jQuery('#btnMPESend').attr('disabled', 'disabled');
            jQuery('.wdmquoteup-loader').css('display', 'inline-block');
            // jQuery('#frm_mpe_enquiry').find('#error' ).html( '' );
            jQuery('#submit_value').val(1);

            $cookieConField = jQuery('#frm_mpe_enquiry').find("input[name='cookie-consent-cb[]']");
            if($cookieConField.length > 0 && $cookieConField.is(":checked"))
            {
                let cname = document.getElementById('custname').value;
                let cemail = document.getElementById('txtemail').value;
                fun_set_cookie(cname, cemail);
            }
            else
            {
                fun_remove_cookie();
            }

            if ( jQuery("#contact-cc").is(":checked") ) {
                var wdm_checkbox_val = 'checked';
            } else {
                var wdm_checkbox_val = 0;
            }

            // Validates for correct nonce of enquiry.
            validate_enq = {
                action: 'quoteupValidateNonce',
                security: jQuery('#mpe_ajax_nonce').val(),
            }
            nonce_error = 0;
            jQuery.post(wdm_data.ajax_admin_url, validate_enq, function ( response ) {
                if ( response == '' ) {
                    jQuery(".load-send-quote-ajax").removeClass('loading');
                    jQuery('.wdmquoteup-loader').css('display', 'none');
                    jQuery('#frm_mpe_enquiry').find('#nonce_error').css('display', 'block');
                    nonce_error = 1;
                } else {
                    jQuery('.wdmquoteup-loader').css('display', 'none');
                    $form_data = new FormData();
                    $form_data.append( 'action', 'quoteupSubmitWooEnquiryForm' );
                    $form_data.append( 'security', jQuery('#mpe_ajax_nonce').val() );
                    $form_data.append( 'wdmLocale', jQuery('#wdmLocale').val() );
                    $form_data.append( 'cc', wdm_checkbox_val );
                    $form_data.append( 'mc-subscription-checkbox', jQuery('.quoteup-mc-subscription-checkbox').prop('checked') );

                    // mydatavar = {
                    //     action: 'quoteupSubmitWooEnquiryForm',
                    //     security: jQuery('#mpe_ajax_nonce').val(),
                    //     cc: wdm_checkbox_val,
                    // };

                    jQuery(".quoteup_registered_parameter").each(function () {
                        // mydatavar[jQuery(this).attr('id')] = jQuery(this).val();
                        $form_data.append( jQuery(this).attr('id'), jQuery(this).val());
                    });
                    if ( fields.length > 0 ) {
                        for (i = 0; i < fields.length; i++) {
                            if ( fields[i].type == 'text' || fields[i].type == 'textarea' || fields[i].type == 'select' ) {
                                $form_data.append(fields[i].id, jQuery('#frm_mpe_enquiry').find("#" + fields[i].id).val());
                                // mydatavar[fields[i].id] = jQuery('#frm_mpe_enquiry').find("#" + fields[i].id).val();
                            } else if ( fields[i].type == 'radio' ) {
                                $form_data.append(fields[i].id, jQuery('#frm_mpe_enquiry').find("[name='" + fields[i].id + "[]']:checked").val());
                                // mydatavar[fields[i].id] = jQuery('#frm_mpe_enquiry').find("[name='" + fields[i].id + "']:checked").val();
                            } else if ( fields[i].type == 'checkbox' ) {
                                var selected = "";
                                jQuery('#frm_mpe_enquiry').find("[name='" + fields[i].id + "[]']:checked").each(function () {
                                    if ( selected == "" ) {
                                        selected = jQuery(this).val();
                                    } else {
                                        selected += "," + jQuery(this).val();
                                    }
                                });

                                $form_data.append(fields[i].id, selected);
                                // mydatavar[fields[i].id] = selected;
                            } else if ( fields[i].type == 'multiple' ) {
                                var selected = "";
                                selected = jQuery('#frm_mpe_enquiry').find("#" + fields[i].id).multipleSelect('getSelects').join(',');

                                $form_data.append(fields[i].id, selected);
                                // mydatavar[fields[i].id] = selected;
                            } else if(fields[i].type == 'file')
                            {
                                var attachedFiles = jQuery('#frm_mpe_enquiry').find('#wdmFileUpload').prop('files');
                                if(attachedFiles && attachedFiles.length > 0) {
                                    jQuery(attachedFiles).each(function(index, value){
                                        $file = value;
                                        $file_size = $file.size;
                                        // $form_data.append( index, $file ); 
                                        $form_data.append( $file.name, $file );

                        // $form_data.append( 'media_file_name', $file.name );
                    });
                                }
                            }
                        }
                    }

                    if ('undefined' != typeof quoteup_captcha_data && 'v3' === quoteup_captcha_data.captcha_version) {
                        // captcha v3
                        let site_key = quoteup_captcha_data.site_key;
                        grecaptcha.execute(site_key, {action: 'quoteup_captcha'}).then(function(token) {
                            $form_data.append('captcha', token);
                            jQuery('#wdm-cart-count').hide();
                            submitEnquiryFormAjax($form_data, $this);
                        });
                    } else {
                        if (jQuery('.g-recaptcha').length > 0){
                            // captcha v2
                            $captcha = grecaptcha.getResponse();
                            if($captcha != '')
                            {
                                $form_data.append('captcha', $captcha);
                            }
                            jQuery('#wdm-cart-count').hide();
                        }
                        submitEnquiryFormAjax($form_data, $this);                  
                    }
                }
        });
} else {
    jQuery(".load-send-quote-ajax").removeClass('loading');
    jQuery('#wdm-quoteupform-error').css('display', 'block');
    jQuery('#wdm-quoteupform-error > .form-errors > ul.error-list').html(err_string);
    return false;
}

return false;
});

//Code to adjust view of enquiry button in mobile view.
lastRowClass = jQuery('.generated_for_mobile tr:last td').attr('class')
if (lastRowClass == "td-btn-update") {
    jQuery('.generated_for_mobile tr:last th').remove();
}
$secondLastRow = jQuery('.generated_for_mobile tr:last').prev();
if ($secondLastRow.find('td').attr('class') == 'cart-total') {
    $secondLastRow.find('th').html(wdm_data.totalText);
    $secondLastRow.prev().remove();
    $secondLastRow.prev().remove();
}
//End of enquiry button code

// Code to adjust view of total row when in mobile view.
secondLastRowClass = jQuery('.generated_for_mobile tr:last').prev().find('td').attr('class')
if (secondLastRowClass == "final-total") {
    jQuery('.generated_for_mobile tr:last').prev().find('th').html(jQuery('.generated_for_mobile tr:last').prev().find('td').html());
    jQuery('.generated_for_mobile tr:last').prev().find('td').html(jQuery('.generated_for_mobile tr:last td').html());
    jQuery('.generated_for_mobile tr:last').remove();
}
// End of total row code

jQuery('.wdm-modal_textarea').on('input', function () {
    var text_max = 500;
    var text_length = jQuery(this).val().length;
    var text_remaining = text_max - text_length;

    jQuery(this).next('#lbl-char').find('.wdmRemainingCount').html(text_remaining);
    if (text_remaining<50) {
        jQuery(this).next('#lbl-char').css('color','red');
    } else {
        jQuery(this).next('#lbl-char').css('color','#43454b');
    }
});


/**
 * Remove row from Enquiry cart when 'Remove' button is clicked
 * Make it responsive as per requirement
 */
 jQuery('.remove').on('click', function ( e ) {
  $enquiryCartTable = jQuery(this).closest('table');
  e.preventDefault();
    var totalColsInEnqCart = jQuery('.generated_for_desktop.wdm-quote-cart-table > thead > tr > th').length; 

        //Check if mobile table or desktop table
        if (isMobileTable($enquiryCartTable)) {
            $cellLocation = findDesktopTableCellLocation(jQuery(this).closest('tr').index(), totalColsInEnqCart);
            $desktopTableCellElement = selectCell('.generated_for_desktop.wdm-quote-cart-table', $cellLocation[0], $cellLocation[1]);
            mobileTableEnquiryCartRemoveRow(jQuery(this).closest('tr'));
            desktopTableEnquiryCartRemoveRow($desktopTableCellElement);
        } else {
         $cellLocation = findMobileTableCellLocation(jQuery(this).closest('tr').index(), 0, totalColsInEnqCart);
         $mobileTableCellElement = selectCell('.generated_for_mobile.wdm-quote-cart-table', $cellLocation[0], $cellLocation[1]);

         desktopTableEnquiryCartRemoveRow(jQuery(this).closest('tr'));
         mobileTableEnquiryCartRemoveRow($mobileTableCellElement);
     }

     product_id = jQuery(this).attr('data-product_id');
     product_var_id = jQuery(this).attr('data-variation_id');
     product_variation_details = jQuery(this).attr('data-variation');


     jQuery.ajax({
        url: wdm_data.ajax_admin_url,
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
        success: function ( response ) {
            count = jQuery('.wdm-quoteupicon-count').text();
            count = parseInt(count) - 1;
            jQuery('.wdm-quoteupicon-count').text(count);
            jQuery('#wdm-cart-count').children('a').attr('title', response.product_count_in_text);
        },
        error: function ( error ) {
            console.log(error);
        }

    });
 });
// On the Product quantity and remarks expected price change
jQuery('.wdm-prod-quant,.wdm-remark').change(function () {
    var $enquiryCartTable = jQuery(this).closest('table'),
        $currentElementValue = jQuery(this).val(),
        $currentElementClass = jQuery(this).attr('class').split(' ')[0],
        totalColsInEnqCart   = jQuery('.generated_for_desktop.wdm-quote-cart-table > thead > tr > th').length; 

        //Check if mobile table or desktop table
        if (isMobileTable($enquiryCartTable)) {
            $cellLocation = findDesktopTableCellLocation(jQuery(this).closest('tr').index(), totalColsInEnqCart);
            $desktopTableCellElement = selectCell('.generated_for_desktop.wdm-quote-cart-table', $cellLocation[0], $cellLocation[1]);
            jQuery($desktopTableCellElement).find('.'+$currentElementClass).val($currentElementValue);
        } else {
            $cellLocation = findMobileTableCellLocation(jQuery(this).closest('tr').index(), jQuery(this).closest('td').index(), totalColsInEnqCart);
            $mobileTableCellElement = selectCell('.generated_for_mobile.wdm-quote-cart-table', $cellLocation[0], $cellLocation[1]);
            jQuery($mobileTableCellElement).find('.'+$currentElementClass).val($currentElementValue);
        }
    })

// When quote cart is updated.
jQuery('.wdm-update').click(function () {
    sendRequestToUpdateCart(true, true);
});

/**
 * Returns true if Table being processed is mobile table. Else returns false
 */
 function isMobileTable($tableElement)
 {
  return $tableElement.hasClass('generated_for_mobile');
}


function mobileTableEnquiryCartRemoveRow($rowElement)
{
  $rowClassName = $rowElement.closest('tr').attr('class');
  var $count = 0;
  while ($rowElement.closest('tr').next().attr('class') == $rowClassName && $count < 5) {
    $rowElement.closest('tr').next().remove();
    $count++;
}
$rowElement.closest('tr').remove();
}

function desktopTableEnquiryCartRemoveRow($rowElement)
{
          //remove the row.
          $rowElement.closest('tr').remove();

          if ( jQuery('.generated_for_desktop .cart_item').length == 1 ) {
            jQuery('.wdm-quote-cart-table').append("<tr> <td colspan='6 class='no-product'>"+ wdm_data.empty_cart_remove +"</td></tr>");
            jQuery('.td-btn-update').remove();
            jQuery('.quoteup-cart-total-row').remove();
            jQuery('.wdm-enquiry-form').remove();
        }

}

function submitEnquiryFormAjax($form_data, $this)
{
    jQuery.ajax( {
        type: 'POST',
        url: wdm_data.ajax_admin_url,
        data: $form_data,
        contentType: false,
        processData: false,
        dataType: 'json',
        /*async: false,*/
        cache: false,
        success: function ( response ) {
            if ( response.status == 'COMPLETED' ) {
                // For old Analytics
                if(window.ga && ga.create) {
                    for (i=0; i < response.gaProducts.length; i++){
                        ga( 'send', 'event', 'Product/Quote Enquiry Form', 'submit', response.gaProducts[i] );
                    };
                }

                // For GA4 and Universal Analytics
                if (typeof gtag == 'function') {
                    for (i = 0; i < response.gaProducts.length; i++) {
                        gtag('event', 'submit', {
                            'event_category': 'Product/Quote Enquiry Form',
                            'event_label': response.gaProducts[i]
                        });
                    }
                }

                jQuery('.quoteup-quote-cart').slideUp();
                jQuery(".load-send-quote-ajax").removeClass('loading');

                /**
                 * Use this trigger event to perform the actions/ manipulations before
                 * the enquiry form is hidden when enquiry is successful.
                 *
                 * @param object $this      The jQuery object of the button clicked to submit the form.
                 * @param object $form_data Object of FormData containing the form values.
                 * @param object response   Ajax response.
                 */
                jQuery(document).trigger('quoteupEnquirySuccessBeforeFormHidden', [ $this, $form_data, response ]);

                setTimeout(function(){ 
                    jQuery('.success').slideDown();
                    jQuery('html, body').animate({ scrollTop: jQuery("#success").offset().top - 700 }, 0);

                    /**
                     * Use this trigger event to perform the actions/ manipulations after
                     * timeout when enquiry is successful.
                     *
                     * @param object $this      The jQuery object of the button clicked to submit the form.
                     * @param object $form_data Object of FormData containing the form values.
                     * @param object $reponse   Ajax response.
                     */
                    jQuery(document).trigger('quoteupEnquirySuccessAfterTimeout', [ $this, $form_data, response ]);
                }, 150);
                if ( wdm_data.redirect != 'n' ) {
                    /**
                     * Use this trigger event before PEP redirects to another page.
                     *
                     * @param object $this      The jQuery object of the button clicked to submit the form.
                     * @param object $form_data Object of FormData containing the form values.
                     * @param object $reponse   Ajax response.
                     */
                    jQuery(document).trigger('quoteupBeforeRedirect', [ $this, $form_data, response ]);
                    window.location = wdm_data.redirect;
                }
                // }
            }else {
                if(response.status == 'failed')
                {
                    jQuery(".load-send-quote-ajax").removeClass('loading');
                    jQuery('#btnMPESend').attr('disabled', false);
                    jQuery('#wdm-quoteupform-error').css('display', 'block');
                    jQuery('#wdm-quoteupform-error > .form-errors > ul.error-list').html(response.message);
                    // error_field.find('ul.error-list').html(response.message);
                    return false;
                }
            }
        }
    });
}

});

/**
* When the quote cart is updated , if any product is removed or quantity remarks changed,
* @param bool showUpdateCartImage
* @param bool showAlertAfterUpdateCart
*/
function sendRequestToUpdateCart( showUpdateCartImage, showAlertAfterUpdateCart )
{
  document.getElementById("error-quote-cart").innerHTML = "";
  validated = true;
  error_val = 0;
  jQuery('.td-btn-update').find('.load-ajax').removeClass('updated');
  jQuery('.cart_product').each(function () {
    thiss = jQuery(this);
    thiss.find('.wdm-prod-quant').css('border-color', '#515151');
})
  jQuery('.cart_product').each(function () {
     thiss = jQuery(this);
     quant = thiss.find('.wdm-prod-quant').val();
     if ( quant < 1 ) {
        thiss.find('.wdm-prod-quant').css('border-color', 'red');
        validated = false;
        err_string = '<li class="error-list-item">' + wdm_data.cart_not_updated + '</li>';
        error_val = 1;
    }

    if ( Number(quant) % 1 !== 0 ) {
        thiss.find('.wdm-prod-quant').css('border-color', 'red');
        validated = false;
        err_string = '<li class="error-list-item">' + wdm_data.cart_not_updated + '</li>';
        error_val = 1;
    }

})

  if ( validated ) {
    if ( jQuery('.cart_item').length > 1 ) {
        if ( showUpdateCartImage ) {
            jQuery('.td-btn-update').find('.load-ajax').removeClass('updated').addClass('loading');
        }

        var products_data = {};

        jQuery('.cart_item').each(function () {
            var $cart_item                = jQuery(this);
            var product_id                = $cart_item.find('.wdm-prod-quant').attr('data-product_id');
            var product_var_id            = "";
            var product_variation_details = "";
            var quantity                  = "";
            var remark                    = "";
            var productHash               = "";
           
            if ( !isNaN(product_id) ) {
                product_var_id            = $cart_item.find('.wdm-prod-quant').attr('data-variation_id');
                product_variation_details = $cart_item.find('.wdm-prod-quant').attr('data-variation');
                quantity                  = $cart_item.find('.wdm-prod-quant').val();
                remark                    = $cart_item.find('.wdm-remark').val();
                productHash               = $cart_item.find('.wdm-prod-quant').attr('data-product_hash');

                // Setup products data. 
                products_data[productHash] = {
                    product_id: product_id,
                    product_var_id: product_var_id,
                    variation: JSON.parse(product_variation_details),
                    quantity: quantity,
                    remark: remark
                };
            }
        });

        jQuery.ajax({
            url: wdm_data.ajax_admin_url,
            type: "post",
            dataType: "JSON",
            async: true,
            data: {
                action: 'wdm_bulk_update_enq_cart_session',
                'products_data': products_data,
            },
            success: function ( response ) {
                if ('SUCCESS' == response.status) {
                    var products_data        = response.products_data;
                    var $desktopEnqCartTable = jQuery('.generated_for_desktop.wdm-quote-cart-table');
                    var $mobileEnqCartTable  = jQuery('.generated_for_mobile.wdm-quote-cart-table');

                    for (var hash_key in products_data) {
                        if (!products_data.hasOwnProperty(hash_key)) {
                            continue;
                        }
                        var single_product_data = products_data[hash_key];
                        // Update price of the product for desktop view.
                        $desktopEnqCartTable.find('input[data-product_hash="' + hash_key + '"]').closest('tr').find('.product-price').html(single_product_data.price);
                        // Update price of the product for mobile view.
                        $mobileEnqCartTable.find('input[data-product_hash="' + hash_key + '"]').closest('tr').prev().find('td.product-price').html(single_product_data.price);
                    }

                    // Update total price of the cart for desktop and mobile view.             
                    jQuery('.wdm-quote-cart-table .cart-total').html(response.total);
                    jQuery('.sold_individually').val(1);
                }

                if ( showUpdateCartImage ) {
                    jQuery('.td-btn-update').find('.load-ajax').removeClass('loading').addClass('updated');
                }
            },
            error: function ( error ) {
                console.log(error);
            }
        });
    } else {
        jQuery('.wdm-quote-cart-table').append("<tr> <td colspan='6> No Products available in Quote Cart </td></tr>");
    }
} else {
    document.getElementById("error-quote-cart").innerHTML = wdm_data.cart_not_updated;
}
}

// Sets the customer name and email id in a cookie on clients' browser.
function fun_set_cookie(cname, cemail)
{
    // var cname = document.getElementById('custname').value;
    // var cemail = document.getElementById('txtemail').value;
    if ( cname != '' && cemail != '' ) {
        var d = new Date();
        d.setTime(d.getTime() + ( 90 * 24 * 60 * 60 * 1000 ));
        var expires = "expires=" + d.toGMTString();
        document.cookie = "wdmusername=" + cname + "; expires=" + expires + "; path=/";
        document.cookie = "wdmuseremail=" + cemail + "; expires=" + expires + ";path=/";
    }

}

// Remove cookie when cookie consent field is not present or not selected
// Remove the customer name and email
function fun_remove_cookie()
{
    document.cookie = "wdmusername=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/;";
    document.cookie = "wdmuseremail=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/;";
}

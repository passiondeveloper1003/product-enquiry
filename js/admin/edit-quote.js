jQuery(document).ready(function () {
    // jQuery('input[name=show_price]').removeAttr('checked');
    if(dateData.unreadEnquiryFlag == 'yes') {
      $countValue = jQuery('.enquiry-count').html();
      if($countValue == 1) {
        jQuery('.unread-enquiry-count').remove();
      }
      $countValue = parseInt($countValue) - 1;
      jQuery('.enquiry-count').html($countValue);
    }

    jQuery('#toplevel_page_quoteup-details-new').addClass('wp-has-current-submenu wp-menu-open');
    jQuery('#toplevel_page_quoteup-details-new a').each(function(){
      if(jQuery(this).attr('href') == 'admin.php?page=quoteup-details-new' && !jQuery(this).hasClass('wp-first-item'))
      {
        jQuery(this).addClass('wp-has-current-submenu wp-menu-open');
      }
    })
    

  //This is to allow metabox to collapse
  postboxes.add_postbox_toggles(pagenow);
  
  jQuery(".msg-wrapper:last").find(".msg-border").hide();
  var atLeastOneIsChecked;
  jQuery('.item-content-add-to-quote').each(function () {
    if (jQuery('.item-content-add-to-quote').html() == '-') {
      jQuery(this).closest('tr').children('.item-content-qty').find('.newqty    ').addClass("unchecked");
      jQuery(this).closest('tr').children('.item-content-newcost').find('.newprice ').addClass("unchecked");
      jQuery('#variation-'+rowNumber).hide();
      jQuery('#variation-unchecked-'+rowNumber).show();
    }
  })

  jQuery('.version-details-head').css('display', 'none');
  jQuery('.version-details-head').first().css('display', 'table-header-group');


  // jQuery('#editMainEnquiry').css('display', 'none');

  //Show original enquiry.
  jQuery('#showEnquiry').click(function(){
    jQuery('#editMainEnquiry').slideToggle('slow', function(){

      if(jQuery( "#editMainEnquiry" ).is( ":visible" ))
      {
        jQuery('#showEnquiry').text("Hide Original Enquiry");
      } else {
        jQuery('#showEnquiry').text("Show Original Enquiry")
      }
    });
  })

  jQuery('.rply-link').click(function (e) {
    e.preventDefault();

    jQuery(this).next('.reply-div').slideToggle();

  });
// Send the reply to user by admin.
jQuery('.button-rply-user').click(function (e) {
  e.preventDefault();
  jQuery('div.reply-msg-error').remove();
  elem = jQuery(this);
  thread_id = jQuery(this).attr('data_thread_id');

  user_email = jQuery('.wdm-enquiry-usr').val();
  subject = jQuery('.wdm_reply_subject_'+thread_id).val();
  path = jQuery('.admin-url').val();
  message = jQuery('.wdm_reply_msg_'+thread_id).val();
  enq_id =jQuery('.wdm-enq-id').val();
  parent_id=elem.closest('.reply-div').find('.parent-id').val();
  jQuery(this).next('.load-ajax').css('display','inline-block');
  division = '<div class="msg-wrapper"><div class="wdm-input-ip hide wdm-enquirymsg"><em>'+subject+'</em></div><div wdm-input-ip>'+message+'</div><hr class="msg-border" style="display: none;"/></div>'
  jQuery.ajax(
  {
    type: 'POST',
    url: path,
    data: {action: 'wdmSendReply',
    'email': user_email,
    'subject': subject,
    'msg': message,
    'eid': enq_id,
    'parent_id':parent_id
  },
  success: function (response) {
    jQuery('.wdm-action option:first-child').attr('selected', 'selected');
    elem.closest('.reply-div').slideUp();
    elem.closest('.reply-div').next('.msg-sent').fadeIn();
    elem.next('.load-ajax').css('display','none');
    jQuery('.reply-field').val('');
    setTimeout(function () {
      elem.closest('.reply-div').next('.msg-sent').fadeOut();
    }, 3000);
    if(isNaN(response))
    {
      var error_msg = "<div class='reply-msg-error'>"+response+"</div>";
      elem.closest('.msg-wrapper').append(error_msg);
      return;
    }

    elem.closest('.reply-div').next('.msg-sent').fadeIn();
    division = jQuery(division);
    division.hide();
    jQuery('.rply-link').prev('.msg-wrapper').find('.msg-border').css('display','block');
    jQuery('.rply-link').before(division);
    jQuery('.reply-div').attr('data-thred-id',response);
    jQuery('.parent-id').val(response);
    elem.attr('data_thread_id',response);
    jQuery('.wdm_reply_subject_'+thread_id).removeClass('wdm_reply_subject_'+thread_id).addClass('wdm_reply_subject_'+response);
    jQuery('.wdm_reply_subject_'+response).val(subject);
    jQuery('.wdm_reply_msg_'+thread_id).removeClass('wdm_reply_msg_'+thread_id).addClass('wdm_reply_msg_'+response);
    division.fadeIn(500);
                          // location.reload();
                        },
                        error: function (error) {
                          console.log(error);
                        }
                      }
                      );


});
  //On product quantity change on edit quote page
  jQuery(document).on( 'change', ".newqty", function(){
  // jQuery(".newqty").change(function () {
    quantity = jQuery(this).val();
    if (quantity % 1 !== 0) {
      jQuery(this).css('border-color','red');
      jQuery("#btnPQuote").attr("disabled", false);
      jQuery("#send").attr("disabled", false);
      jQuery("#downloadPDF").attr("disabled", false);

      jQuery("#PdfLoad").css("visibility", "hidden");

        // displayAjaxResponseMessages( quote_data.quantity_invalid );
        return;
      } else {
        jQuery(this).css('border-color','#ddd');
      }
    });

  var totalprice=0;
  var rowNumber;
  var previousSKU = jQuery(this).closest('.wdmpe-detailtbl-content-row').find('.item-content-sku').text();
        //Enable input boxes if checkbox is checked
        jQuery('.wdm-checkbox-quote').click(function () {
          var atLeastOneIsChecked = false;
          jQuery('.wdm-checkbox-quote').each(function () {
            if (jQuery(this).is(':checked')) {
              atLeastOneIsChecked = true;
                      // Stop .each from processing any more items
                      return false;
                    }
                  });
          if (atLeastOneIsChecked) {
            jQuery('#send').prop('disabled', false);
            jQuery('#btnPQuote').prop('disabled', false);
            jQuery('#show_price').prop('disabled', false);
            jQuery('.quoteup-pdf-language').prop('disabled', false);
            jQuery('.wdm-input-expiration-date').prop('disabled', false);
            jQuery('.quote-options').removeClass('deleted-product');
          } else {
            jQuery('#send').prop('disabled', true);
            jQuery('#btnPQuote').prop('disabled', true);
            jQuery('#show_price').prop('disabled', true);
            jQuery('.quoteup-pdf-language').prop('disabled', true);
            jQuery('.wdm-input-expiration-date').prop('disabled', true);
            jQuery('.quote-options').addClass('deleted-product');
          }
          rowNumber=jQuery(this).attr("data-row-num");
          if (jQuery(this).is(":checked")) {
            jQuery('#content-qty-'+rowNumber).removeClass("unchecked");
            jQuery('#content-new-'+rowNumber).removeClass("unchecked");
            jQuery('#variation-'+rowNumber).show();
            jQuery('#variation-unchecked-'+rowNumber).hide();
            jQuery(this).closest('.wdmpe-detailtbl-content-row').find('.item-content-sku').text(previousSKU);
            if (!jQuery('#content-qty-'+rowNumber).hasClass('sold-individual-quantity')) {
              jQuery('#content-qty-'+rowNumber).prop('disabled', false);
            }
            jQuery('#content-new-'+rowNumber).prop('disabled', false);
            var quantity=jQuery('#content-qty-'+rowNumber).val();
            var newprice=jQuery('#content-new-'+rowNumber).val();
            var finalprice=newprice*quantity;
            jQuery('#content-cost-'+rowNumber).html(quoteupFormatPrice(finalprice));
            jQuery('#content-amount-'+rowNumber).val(finalprice);
            var finaltotal=0
            jQuery('.amount_database').each(function () {
              var current=jQuery(this).val();
              finaltotal=parseFloat(finaltotal)+parseFloat(current);
            })

            jQuery('#amount_total').html(quoteupFormatPrice(finaltotal));
          } else {
            previousSKU = jQuery(this).closest('.wdmpe-detailtbl-content-row').find('.item-content-sku').text();
            jQuery('#content-qty-'+rowNumber).addClass("unchecked");
            jQuery('#content-new-'+rowNumber).addClass("unchecked");
            jQuery('#variation-'+rowNumber).hide();
            jQuery('#variation-unchecked-'+rowNumber).show();
            jQuery(this).closest('.wdmpe-detailtbl-content-row').find('.item-content-sku').text(jQuery('#variationUnchecked-'+rowNumber).val());
            jQuery('#content-qty-'+rowNumber).prop('disabled', true);
            jQuery('#content-new-'+rowNumber).prop('disabled', true);
            jQuery('#content-cost-'+rowNumber).html("-");
            jQuery('#content-amount-'+rowNumber).val(0);
            var finaltotal=0
            jQuery('.amount_database').each(function () {
              var current=jQuery(this).val();
              finaltotal=parseInt(finaltotal)+parseInt(current);
            })

            jQuery('#amount_total').html(quoteupFormatPrice(finaltotal));
          }
        });

        //Update Amout and total amount on change of new price or quantity
        jQuery(document).on( 'input', ".newprice, .newqty", function(e){
        // jQuery('.newprice, .newqty').on('input',function (e) {
          rowNumber         = jQuery(this).attr("data-row-num");
          var quantity      = jQuery('#content-qty-'+rowNumber).val();
          var oldprice      = jQuery('#old-price-'+rowNumber).val();
          var newprice      = jQuery('#content-new-'+rowNumber).val();
          var totalOgPrice  = oldprice * quantity;
          var totalNewprice = newprice * quantity;

          quoteupAlterProductTotalPriceChange(totalOgPrice, totalNewprice, rowNumber);

          var grandTotalOgPrice = 0;
          var grandTotalNewPrice = 0;
          jQuery('.amount_database').each(function () {
            var oldValue       = jQuery(this).data("old-value");
            var newValue       = jQuery(this).val();
            grandTotalOgPrice  = parseFloat(grandTotalOgPrice) + parseFloat(oldValue);
            grandTotalNewPrice = parseFloat(grandTotalNewPrice) + parseFloat(newValue);
          })

          quoteupAlterGrandTotalPriceChange(grandTotalOgPrice, grandTotalNewPrice);
        })

        //Change Quantity to 1 if it is blank or invalid
        jQuery(document).on( 'focusout', ".newqty, .newprice", function(){
        // jQuery('.newqty, .newprice').focusout(function () {
          makeDataValid(jQuery(this));

        });

  /**
   * Makes the data valid of Product price and quantity it should not be balck or invalid.
   * Change it to 1 if found so.
   * @param string selector element selected
   */
  function makeDataValid(selector)
  {
    var isDataValid = true;
    if (selector.hasClass('newqty')) {
      if (!jQuery.trim(selector.val()) || selector.val() <= 0) {
        isDataValid = false;
        selector.val("1");
      }
    } else {
      if (!jQuery.trim(selector.val()) || selector.val() < 0) {
        isDataValid = false;
        selector.val("0");
      }
    }

    if (isDataValid == false) {
      jQuery('.newprice').trigger('input');
    }
  }

       //Datepicker for expiration date.
       jQuery(".wdm-input-expiration-date").datepicker({
        altFormat: "yy-mm-dd 00:00:00",
        altField: ".expiration_date_hidden",
        minDate: 0,
        showButtonPanel: true,
        closeText: dateData.closeText,
        currentText: dateData.currentText,
        monthNames: dateData.monthNames,
        monthNamesShort: dateData.monthNamesShort,
        dayNames: dateData.dayNames,
        dayNamesShort: dateData.dayNamesShort,
        dayNamesMin: dateData.dayNamesMin,
        dateFormat: dateData.dateFormat,
        firstDay: dateData.firstDay,
        isRTL: dateData.isRTL,
      });

    // When WooCommerce changes image on changing variation, copy new image's src value in the image column of Product Details Table
   //  jQuery('.variation_image').observe({ attributes: true, attributeFilter: ['src'] }, function () {
   //    if (empty(this.getAttribute('src'))) {
   //     jQuery(this).closest('.wdmpe-detailtbl-content-row').find('.item-content-img img').attr('src', this.getAttribute('data-o_src'));
   //   } else {
   //     jQuery(this).closest('.wdmpe-detailtbl-content-row').find('.item-content-img img').attr('src', this.getAttribute('src'));
   //   }
   // });

    // When WooCommerce changes SKU, copy new SKU value in the SKU column of Product Details Table
    
    // jQuery('.sku').observe('childlist subtree', function () {
    //   jQuery(this).closest('.wdmpe-detailtbl-content-row').find('.item-content-sku').text(jQuery(this).text());
    // });

    //If no date is filled, set hidden value to 0000-00-00 00:00:00
    jQuery(".wdm-input-expiration-date").change(function () {
      if (empty(jQuery(this).val())) {
        jQuery('.expiration_date_hidden').val('0000-00-00 00:00:00');
      }
    });

    jQuery('#wpfooter').css("position", "relative");

  //change the product link if some other variation is selected.
  jQuery('.variations_form').on('change', '.variations select', function () {
    var productLink = jQuery(this).closest('.item-content-variations').data('product-link');
    wc_cart_fragments_params.wc_ajax_url = add_query_arg('wc-ajax', '%%endpoint%%', productLink);
  });

  /**
     * When variation id is changed, then show actual price of selected variation as old price
     */
     jQuery('#Quotation').delegate('.product', 'change', function(){
       // jQuery('.variations_form').on('change', '[name="variation_id"]', function () {
        /**
         * Check if selected variation was already present in the enquiry and if present, set old price
         * as price available during enquiry
         */
         $this = jQuery(this).find('.variation_id');
         var $shouldOriginalPriceBeRetrieved = true;
         var $rowOfCurrentVariation = $this.closest('.wdmpe-detailtbl-content-row');
         var $oldPriceCell = $rowOfCurrentVariation.find('.item-content-old-cost');
         var $oldPriceData = $oldPriceCell.data('old_price');

         if (!empty($oldPriceData.variation)) {
            /**
             * If value of all variation attributes match with the ones selected during enquiry, then
             * show price available at enquiry
             */
             for (var variation_attribute in $oldPriceData.variation) {
              if ($oldPriceData.variation.hasOwnProperty(variation_attribute)) {
                if ($rowOfCurrentVariation.find(".variations select[name='attribute_" + variation_attribute + "']").val() != $oldPriceData.variation[variation_attribute]) {
                  $shouldOriginalPriceBeRetrieved = false;
                  break;
                }
              }
            }
          } else {
            $shouldOriginalPriceBeRetrieved = false;
          }

          if ($shouldOriginalPriceBeRetrieved) {
            $oldPriceCell.find('.amount').html(quoteupFormatPrice($oldPriceData.price));
            $oldPriceCell.find('input').val($oldPriceData.price);
          } else {
            /**
             * Finds out price of a selected variation saved on Product Edit page and set it as old
             * price
             */
             var $productData = $this.closest('.product');
             var $variationId = $this.val();
             var $allVariations = $productData.find('.variations_form.cart').data('product_variations');
             var $variationsFetchedByAjax = false;
             if ($allVariations === false) {
              $variationsFetchedByAjax = true;
            }
            if (!$variationsFetchedByAjax) {
                /**
                 * Find out data of current selected variation from $allVariations object and set that data
                 * in 'Price' (i.e Old Price) column
                 */
                 for ($i = 0; $i < $allVariations.length; $i++) {
                  if ($allVariations[$i].variation_id == $variationId) {
                    $oldPriceCell.find('.amount').html(quoteupFormatPrice($allVariations[$i].display_regular_price));
                    $oldPriceCell.find('input').val($allVariations[$i].display_regular_price);
                    if (!empty($allVariations[$i].display_price)) {
                      $oldPriceCell.find('.amount').html(quoteupFormatPrice($allVariations[$i].display_price));
                      $oldPriceCell.find('input').val($allVariations[$i].display_price);
                    }

                    break;
                  }
                }
              } else {
                var newRegularPrice = $productData.find('.quoteup-regular-price').text();
                var newPrice = $productData.find('.quoteup-price').text();
                $oldPriceCell.find('.amount').html(quoteupFormatPrice(newRegularPrice));
                $oldPriceCell.find('input').val(newRegularPrice);
                if (!empty(newPrice)) {
                  $oldPriceCell.find('.amount').html(quoteupFormatPrice(newPrice));
                  $oldPriceCell.find('input').val(newPrice);
                }
              }

            }

            if ('' != $rowOfCurrentVariation.find('.variation_id').val()) {
              $rowOfCurrentVariation.find('.newqty').trigger('input');
            }
          });

   });

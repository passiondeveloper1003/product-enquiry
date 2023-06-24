jQuery(document).ready(function () {
  jQuery(document).on("quote_saved_successfully", function() {
    jQuery("#saveAdminProductsData").hide();
  });

  if (typeof dateData != 'undefined') {
    //Datepicker for expiration date.
    jQuery(".wdm-input-quote-send-deadline").datepicker({
        altFormat: "yy-mm-dd",
        altField: ".quote_send_deadline_hidden",
        minDate: 0,
        showButtonPanel: true,
        currentText: dateData.currentText,
        monthNames: dateData.monthNames,
        monthNamesShort: dateData.monthNamesShort,
        dayNames: dateData.dayNames,
        dayNamesShort: dateData.dayNamesShort,
        dayNamesMin: dateData.dayNamesMin,
        dateFormat: dateData.dateFormat,
        firstDay: dateData.firstDay,
        isRTL: dateData.isRTL,
        onSelect: function(dateText, inst) {
          var quoteSendDeadline = jQuery('.quote_send_deadline_hidden').val();
          var enquiry_id = jQuery('#enquiry_id').val();

          var data = {
            'action': 'quoteup_save_quote_send_dl',
            'enquiry_id': enquiry_id,
            'quote_send_deadline': quoteSendDeadline,
            'security': jQuery('#quoteNonce').val(),
          };

          var $statusBoxText   = jQuery('.wdm-status-box #text');
          var $statusBoxLoader = jQuery('.wdm-status-box #PdfLoad');
          $statusBoxText.text(quote_send_dl_data.saving_dl);
          $statusBoxText.css("visibility", "visible");
          $statusBoxLoader.css("visibility", "visible");

          jQuery.post(quote_data.ajax_url, data, function (response) {
            if ( response == 'SECURITY_ISSUE' ) {
              response = quote_send_dl_data.security_issue;
              processQutationSaveFailure(( response == 'SECURITY_ISSUE' ) ? quote_data.pdf_generation_aborted : response);
              return;
            } else if (response == "Saved Successfully") {
              response = quote_send_dl_data.successful;
            }
            
            $statusBoxText.text(response);
            $statusBoxLoader.css("visibility", "hidden");
          });
        }
      });
  }

  if (jQuery('#btnSaveVendorQuotePrice').length >= 1) {
    // When 'Save Price' button is cliked by vendor on enquiry edit page.
    jQuery('#btnSaveVendorQuotePrice').click(function() {
      jQuery(this).attr("disabled", true);
      saveProductsData('quoteup_save_vendor_quote_price');
    });
  }

  if (jQuery('#saveAdminProductsData').length >= 1) {
    // When 'Save' button is cliked by the admin on enquiry edit page.
    jQuery('#saveAdminProductsData').click(function() {
      jQuery(this).attr("disabled", true);
      saveProductsData('quoteup_save_admin_products_data');
    });
  }

  function saveProductsData(actionName) 
  {
      var quantity = [ ];
      var variation_id = [ ];
      var variation_index_in_enquiry = [ ];
      var variationDetails = [ ];
      var totalQuantity = parseInt("0");
      var newprice = [ ];
      var id = [ ];
      var old_price = [ ];
      var enquiry_id = jQuery('#enquiry_id').val();
      var quoteProductsData = {};
      var count = 0;
      var allSuccess = true;
      hasOtherProductType = false;
      hasDeletedProduct = false;

      // Change '#text' message to empty.
      displayAjaxResponseMessages("");
      // Make loader visible.
      jQuery("#PdfLoad").css("visibility", "visible");

      jQuery('#Quotation tr.wdmpe-detailtbl-content-row').each(function(e){
        if(jQuery(this).hasClass('quote-disableCheckBox')) {
          hasOtherProductType = true;
          return true
        }

        if(jQuery(this).hasClass('deleted-product')) {
          hasDeletedProduct = true;
          return true
        }

        var rowNumber = jQuery(this).data('row-num');
        old_price[rowNumber] = jQuery('#old-price-' + rowNumber).val();
        variation_index_in_enquiry[rowNumber] = rowNumber - 1;
        id[rowNumber] = jQuery('#content-ID-' + rowNumber).val();
        quantity[rowNumber] = jQuery('#content-qty-' + rowNumber).val();

        //Get all variations
        if ("undefined" != typeof jQuery('#variation-id-' + rowNumber).val()) {
          if ( !jQuery('#variation-id-' + rowNumber).val()) {
            var message = quote_data.invalid_variation + " " + jQuery('#product-title-' + rowNumber).text();
            processQutationSaveFailure(message);
            allSuccess = false;
            return;
          }
          var variationArray = [ ];
          variation_id[rowNumber] = jQuery('#variation-id-' + rowNumber).val();
          jQuery('#variation-' + rowNumber + '  select[name^=attribute_]').each(function ( ind, obj ) {
            name = jQuery(this).attr('name');
            name = name.substring(10);
            variation = name + " : " + jQuery(this).val();
            variationArray.push(variation);

            variationDetails[rowNumber] = variationArray;

          });
        } else {
          variation_id[rowNumber] = '';
          variationDetails[rowNumber] = "";
        }

        // Check if quantity is valid.
        if (quantity[rowNumber] % 1 !== 0) {
          processQutationSaveFailure(quote_data.quantity_invalid);
          allSuccess = false;
          return;
        }
            newprice[rowNumber] = jQuery('#content-new-' + rowNumber).val();
            totalQuantity = parseInt(totalQuantity + quantity[rowNumber]);
            singleProductData = {
              'productID' : id[rowNumber],
              'variationID' : variation_id[rowNumber],
              'variationDetails' : variationDetails[rowNumber],
              'productPrice' : old_price[rowNumber],
              'productQty' : quantity[rowNumber],
            }
            quoteProductsData[count] = singleProductData;
            count = parseInt(count) + 1;
          })
      
      if(!allSuccess)
      {
        return;
      }

      var variationLength = variationDetails.length;
      for (i = 0; i < variationLength; i++) {
        for (j = i + 1; j < variationLength; j++) {
          if ( variation_id[i] != 0 && parseInt(variation_id[i]) == parseInt(variation_id[j]) ) {
            if ( variationDetails[i].compare(variationDetails[j]) ) {
              displayAjaxResponseMessages(quote_data.same_variation);
              hasErrorOccurred = true;
              return;
            }
          }
        }
      }

      var show_price = jQuery('input[name="show_price"]:checked').val();
      var show_price_diff = jQuery('input[name="show_price_diff"]:checked').val();
      var cname = jQuery('input[name="cust_name"]').val();
      var cemail = jQuery('input[name="cust_email"]').val();
      var expiration_date = jQuery('.expiration_date_hidden').val();
      var language = jQuery('.quoteup-pdf-language').val();
      if ( show_price == "1" ) {
       show_price = 'yes'
     } else {
       show_price = 'no'
     }

      if ("1" == show_price_diff) {
        show_price_diff = 'yes'
      } else {
        show_price_diff = 'no'
      }

     var data = {
          'action': actionName, //Action to store quotation in database
          'enquiry_id': enquiry_id,
          'cname': cname,
          'email': cemail,
          'id': id,
          'newprice': newprice,
          'quantity': quantity,
          'old-price': old_price,
          'variations_id': variation_id,
          'variations': variationDetails,
          'show-price': show_price,
          'show-price-diff': show_price_diff,
          'expiration-date': expiration_date,
          'variation_index_in_enquiry' : variation_index_in_enquiry,
          'security': jQuery('#quoteNonce').val(),
          'language' : language,
          'quoteProductsData' : quoteProductsData
        };
        if ( totalQuantity <= 0 ) {
         processQutationSaveFailure(quote_data.quantity_less_than_0);
         return;
       }

      jQuery.post(quote_data.ajax_url, data, function ( response ) {
        response = response.trim();
        let $adminSavePriceBtn = jQuery('#saveAdminProductsData');

        jQuery("#PdfLoad").css("visibility", "hidden");
        $adminSavePriceBtn.attr("disabled", false);

        if ( response == 'SECURITY_ISSUE' || response != 'Saved Successfully.' ) {
          processQutationSaveFailure(( response == 'SECURITY_ISSUE' ) ? quote_data.pdf_generation_aborted : response);
          return;
        }

        if ( response == 'Saved Successfully.' ) {
          response = quote_data.saved_successfully;
        }

        jQuery("#text").css("visibility", "visible");
        displayAjaxResponseMessages(response);
      });
  }

  /**
   * This function displays the Response messages for the ajax requests.
   * @param string message
   */
  function displayAjaxResponseMessages( message )
  {
    // Display message below the 'Save Price' button.
    document.getElementById("text").innerHTML = message;
  }

  /**
   * Handles things to be done when Saving Quotation in datbase fails.
   * @param  {String} failureMessage Message to be displayed on the frontend
   */
  function processQutationSaveFailure( failureMessage )
  {
    jQuery("#text").css("visibility", "visible");
    displayAjaxResponseMessages(failureMessage);
  }
});


jQuery(document).ready(function ( $ ) {
  // For products search bar for product selection
  jQuery("body").on('click', '.quoteup-add-products-button', function(e){
    e.preventDefault();
    jQuery('.productLoad').addClass('loading');
    if(typeof globalTaskComplete != 'undefined'){
      if(globalTaskComplete == true) {
        jQuery('#btnQuoteSave').val(wdm_data.update_quotation_text);
        jQuery('#btnQuoteSend').css('display', 'none');
      }            
    }
    jQuery('.quoteup-add-products-button').attr('disabled', true);
    if(jQuery(".admin-quote-table tbody tr.quoteup-no-product").length>0) {
      jQuery(".admin-quote-table tbody tr.quoteup-no-product").remove();
    }
    // In admin quote creation during product selection, the previously selected products should not be shown in search bar.
    // i.e, oldExcludedProduct.
    // below both input.wc-product-search and [name='wc_products_selections'] part of Product search bar.

    var selectedProducts = jQuery( ':input.wc-product-search' ).select2('data');
    var oldExcludedProducts = jQuery("[name='wc_products_selections']").data('exclude');
    var productToExclude = '';
    jQuery.each(selectedProducts,function(index, value){
      if(productToExclude != ""){
        productToExclude = productToExclude + ',' + value['id'];
      }else {
        productToExclude = value['id'];
      }
    });
    //exclude previous selected products
    if(oldExcludedProducts != ""){
      var newExcludeProducts = oldExcludedProducts + ',' + productToExclude;
    }else {
      var newExcludeProducts = productToExclude;
    }
        //exclude previous selected products
        jQuery("[name='wc_products_selections']").data('exclude',newExcludeProducts);
        var productTotalPrice = 0;
        var count = 0;
        var totalRows = 0;
        if(typeof globalTaskComplete != 'undefined'){
          totalRows = jQuery('.quotetbl-content-row:last input.wdm-prod-price.input-text').data('row-num');
          if ("undefined" == typeof totalRows) {
            totalRows = 0;
          }
        } else {
          totalRows = jQuery('.wdmpe-quotation-table .wdmpe-detailtbl-content-row:last').data('row-num');
          if ("undefined" == typeof totalRows) {
            totalRows = 0;
          }
        }
    //Sends the selected products data through ajax
    jQuery.each(selectedProducts, function(key, value){
      var productID = variationID = productTitle = productPrice = variationDetails = url = product_image = sku = "";
      var variationAttributes = [];
      var rawVariationAttributes = [];
      count = parseInt(count)+1;

      $.each(value, function(key, value){
        switch (key) { 
          case 'id':
          ID = value;
          break;
          case 'product_id': 
          productID = value;
          break;
          case 'variation_id': 
          variationID = value;
          break;
          case 'title': 
          productTitle = value;
          break;      
          case 'price':
          if ('function' === typeof quoteupFetchCSPPrice) {
            // Fetch CSP price for single quantity.
            if ('' == variationID) {
              // If it is a simple product.
              quoteupFetchCSPPrice(productID, false);
              productPrice = quoteupCSPgetPriceFor(1, csp_prices[productID]['prices']);
            } else {
              // If it is a variation product.
              quoteupFetchCSPPrice(variationID, false);
              productPrice = quoteupCSPgetPriceFor(1, csp_prices[variationID]['prices']);
            }
          } else {
            productPrice = value;
          }
          if(productPrice == ''){
            productPrice = 0;                        
          }
          productTotalPrice = parseFloat(value);
          break;
          case 'variation_string': 
          variationDetails = value;
          break;
          case 'variation_attributes':
          $.each(value, function( index, variation ) {
            oldKey = index;
            value[index.replace("attribute_", "")] = variation;
            delete value[oldKey];
          });
          rawVariationAttributes = value;
          variationAttributes = JSON.stringify(value);
                    // rawVariationAttributes = variationAttributes;
                    variationAttributes = escapeHtml(variationAttributes);
                    break;
                    case 'url':
                    url = value;
                    break;
                    case 'product_image':
                    product_image = value;
                    break;
                    case 'sku' :
                    sku = value;
                    break;

                  }
                });

      $oldPriceData = {price: productPrice, variation: rawVariationAttributes}
      $oldPriceData = escapeHtml(JSON.stringify($oldPriceData));

      perProductDetail = {
        'ID' : ID,
        'productID' : productID,
        'variationID' : variationID,
        'productTitle' : productTitle,
        'productPrice' : productPrice,
        'variationDetails' :variationDetails,
        'variationAttributes' : variationAttributes,
        'rawVariationAttributes' :rawVariationAttributes,
        'productPriceSymbol' : quoteupFormatPrice(productPrice),
        'count' : totalRows+count,
        'url' : url,
        'product_image' : product_image,
        'sku' : sku,
        'oldPriceData' : $oldPriceData,
        'language': $('.quoteup-pdf-language').val(),
        'variationSelector' : '-',
      };

      if(perProductDetail['variationID'] !="") {
        var data = {
                    'action': 'get_variations', //Action to store quotation in database
                    'perProductDetail': perProductDetail,
                  };

                  $.ajax({
                    type: 'POST',
                    url: productsSelectionData.ajax_url,
                    data: data,
                  // success: success,
                  // dataType: dataType,
                  async:false
                }).done(function(response) {
                  perProductDetail['variationSelector'] =  JSON.parse(response);
                });
              }

              if(typeof globalTaskComplete != 'undefined'){
                template     = wp.template( 'quote-creation' );
                $template_html = template( {
                  perProductDetail : perProductDetail
                } );                
              } else {
                template     = wp.template( 'quote-edit' );
                $template_html = template( {
                  perProductDetail : perProductDetail
                } );
              }
              // Appends the page with table structure of products.
              jQuery(".admin-quote-table tbody.wdmpe-detailtbl-content").append($template_html);
              if(jQuery(".admin-quote-table tbody.wdmpe-detailtbl-content tr.wdmpe-detailtbl-content-row").length > 0){
                jQuery(".admin-quote-table tbody.wdmpe-detailtbl-content tr.wdmpe-detailtbl-content-row:last").find(".variations_form").wc_variation_form();
              } else {
                jQuery(".admin-quote-table tbody.wdmpe-detailtbl-content tr.quotetbl-content-row:last").find(".variations_form").wc_variation_form();
              }
              
              let grandTotalOgPrice  = jQuery('#database-amount').data('old-value');
              let grandTotalNewPrice = jQuery('#database-amount').val();

              grandTotalOgPrice  = parseFloat(productTotalPrice) + parseFloat(grandTotalOgPrice);
              grandTotalNewPrice = parseFloat(productTotalPrice) + parseFloat(grandTotalNewPrice);
              quoteupAlterGrandTotalPriceChange(grandTotalOgPrice, grandTotalNewPrice);
            });
jQuery('.select2-search-choice-close').click();
// jQuery('#productLoad').css('display', 'none');
jQuery('.productLoad').removeClass('loading');
jQuery('.quoteup-add-products-button').prop('disabled', false);
return;
});

//escape html syntax from data
function escapeHtml(unsafe) {
  return unsafe
  .replace(/&/g, "&amp;")
  .replace(/</g, "&lt;")
  .replace(/>/g, "&gt;")
  .replace(/"/g, "&quot;")
  .replace(/'/g, "&#039;");
}

// This function removes the product from list
jQuery("body").on('click', '.remove', function ( e ) {
  e.preventDefault();
  var oldExcludedProducts = jQuery("[name='wc_products_selections']").data('exclude');
  if(typeof oldExcludedProducts == "undefined") {
    return;
  } else {
    oldExcludedProducts = oldExcludedProducts.split(',');
  }
  let rowNumber = jQuery(this).data('row-num');
  let product_id = jQuery(this).data('product_id');
  let product_var_id = jQuery(this).data('variation_id');
  let id = jQuery(this).attr('data-id');
  
  newExcludedProducts = jQuery.grep(oldExcludedProducts, function(value) {
    return value != id;
  });
  
  jQuery("[name='wc_products_selections']").data('exclude',newExcludedProducts.join());
  
  let currentProductOldPrice = jQuery('#content-amount-'+rowNumber).data('old-value');
  let currentProductNewPrice = jQuery('#content-amount-'+rowNumber).val();
  let grandTotalOgPrice      = jQuery('#database-amount').data('old-value');
  let grandTotalNewPrice     = jQuery('#database-amount').val();
  
  grandTotalOgPrice   = grandTotalOgPrice - currentProductOldPrice;
  grandTotalNewPrice  = grandTotalNewPrice - currentProductNewPrice;
  quoteupAlterGrandTotalPriceChange(grandTotalOgPrice, grandTotalNewPrice)

  jQuery(this).closest('tr').remove();
  if(jQuery(".admin-quote-table tbody tr").length<1){
    jQuery(".admin-quote-table tbody").append(wdm_data.quoteup_no_products);
  }
})
// End of remove function from list

jQuery('#Quotation').delegate('.product', 'change', function(){
  var variationArray = [ ];
  var $rowOfCurrentVariation = jQuery(this).closest('.wdmpe-detailtbl-content-row');
  variationID = jQuery(this).find('.variation_id').val();
  var oldExcludedProducts = jQuery("[name='wc_products_selections']").data('exclude');
  if(typeof oldExcludedProducts == "undefined") {
    oldExcludedProducts = [];
  } else {
    oldExcludedProducts = oldExcludedProducts.split(',');
  }
  $encryptedOldID = $rowOfCurrentVariation.find('.remove').attr('data-id');
  if(variationID == "")
  {
    newExcludedProducts = jQuery.grep(oldExcludedProducts, function(value) {
      return value != $encryptedOldID;
    });
    jQuery("[name='wc_products_selections']").data('exclude',newExcludedProducts.join());
    $rowOfCurrentVariation.find('.remove').attr('data-id', "");
        // newExcludedProducts = oldExcludedProducts.replace($encryptedOldID+",", "");
        // jQuery("[name='wc_products_selections']").data('exclude',newExcludedProducts);
        return;
      }
      variationSelect = jQuery(this).find("select[name^=attribute_]");
      variation = "";
      variationSelect.each(function ( ind, obj ) {
        variation = variation+'_'+jQuery(this).val();
        // variationArray.push(variation);

      });
      $encryptedNewID = MD5(variationID+variation);
      
      $indexOfOldProduct = jQuery.inArray($encryptedOldID, oldExcludedProducts);
      
      if($indexOfOldProduct !== -1)
      {
        oldExcludedProducts[$indexOfOldProduct] = "";
      }
      newExcludedProducts = oldExcludedProducts;
      if(jQuery.inArray($encryptedNewID, newExcludedProducts) == -1)
      {
        newExcludedProducts.push($encryptedNewID);        
      }
      var newExcludedProducts = newExcludedProducts.filter(function(v){return v!==''});
      $rowOfCurrentVariation.find('.remove').attr('data-id', $encryptedNewID);
      jQuery("[name='wc_products_selections']").data('exclude',newExcludedProducts.join());
    })

jQuery('#Quotation').delegate('.product', 'change', function(){
      // Code to Update SKU
      SkuField = jQuery(this).find('.product_meta .sku_wrapper .sku');
      SkuField.closest('.wdmpe-detailtbl-content-row').find('.item-content-sku p').text(SkuField.text());
      // Code to Update IMAGE
      imageField = jQuery(this).find('.images .variation_image');
      if (empty(imageField.attr('src'))) {
       jQuery(imageField).closest('.wdmpe-detailtbl-content-row').find('.item-content-img img').attr('src', imageField.attr('data-o_src'));
     } else {
       jQuery(imageField).closest('.wdmpe-detailtbl-content-row').find('.item-content-img img').attr('src', imageField.attr('src'));
     }
   })

/**
      * When Variation Dropdown is changed, the following actions should be performed
      */
      jQuery(document).on('change', '.variations select', function () {
        disableSendDownloadBtn();
    /**
     * Check if selected variation was already present in the enquiry and if present, set old price
     * as price available during enquiry
     */
    var $shouldOriginalPriceBeRetrieved = true;
    var $rowOfCurrentVariation = jQuery(this).closest('.wdmpe-detailtbl-content-row');
    var $oldPriceCell = $rowOfCurrentVariation.find('.item-content-old-cost');
    var $oldPriceData = $oldPriceCell.data('old_price');

    /**
     * If variation is empty, retrieve the new price.
     */
    if (0 == $oldPriceData.variation.length) {
      $shouldOriginalPriceBeRetrieved = false;
    } else {
      /**
       * If value of all variation attributes match with the ones selected during enquiry, 
       * then show old price.
       */
      for (var variation_attribute in $oldPriceData.variation) {
        if ( $oldPriceData.variation.hasOwnProperty(variation_attribute) ) {
          if ( $rowOfCurrentVariation.find(".variations select[name='attribute_" + variation_attribute + "']").val() != $oldPriceData.variation[variation_attribute] ) {
            $shouldOriginalPriceBeRetrieved = false;
            break;
          }
        }
      }
    }

    if ( $shouldOriginalPriceBeRetrieved ) {
      $oldPriceCell.find('.amount').html(quoteupFormatPrice($oldPriceData.price));
      $oldPriceCell.find('input').val($oldPriceData.price);
    } else {
      /**
       * Finds out price of a selected variation saved on Product Edit page and set it as old
       * price
       */
      var $productData = jQuery(this).closest('.product');
      var $variationId = $productData.find('.variation_id').val();
      var $allVariations = $productData.find('.variations_form.cart').data('product_variations');
      /**
       * Find out data of current selected variation from $allVariations object and set that data
       * in 'Price' (i.e Old Price) column
       */
      for ($i = 0; $i < $allVariations.length; $i++) {
        if ( $allVariations[$i].variation_id == $variationId ) {
          $oldPriceCell.find('.amount').html(quoteupFormatPrice($allVariations[$i].display_regular_price));
          $oldPriceCell.find('input').val($allVariations[$i].display_regular_price);
          if ( !empty($allVariations[$i].display_price) ) {
            $oldPriceCell.find('.amount').html(quoteupFormatPrice($allVariations[$i].display_price));
            $oldPriceCell.find('input').val($allVariations[$i].display_price);
          }

          break;
        }
      }
    }

    if ('' != $rowOfCurrentVariation.find('.variation_id').val()) {
      $rowOfCurrentVariation.find('.newqty').trigger('input');
    }

  });
// When some other variation is being selected from dropdown disable the send and download button of PDF.
function disableSendDownloadBtn()
{
  // return;
  isDataChangedAfterLastPDFGeneration = true;
  jQuery("#btnPQuote").val(quote_data.save_and_preview_quotation);
  jQuery("#send").css('display', 'none');
  jQuery("#send").val(quote_data.save_and_send_quotation);
  if ( jQuery('#send').is(":disabled") ) {
    jQuery("#btnPQuote").prop('disabled', true);
  }
  jQuery("#downloadPDF").attr("disabled", true);
  jQuery("#downloadPDF").css("display", 'none');
}
})

/*
 *To set the global setting option of add_to_cart to individual product.
 * * To set the global setting option of quoteup_enquiry  to individual product. 
 */
 onload=function(){
    if(!!window.performance && window.performance.navigation.type === 2)
    {
        window.location.reload();
    }
}
jQuery(document).ready(function () {
    /* Apply bulk changes button click
    */
    jQuery('.button').click(function (e) {
        if (jQuery(this).attr('id') == "doaction") {
            var url = dashboard_quote_listing_bulk_actions.ajax_url;

            if (jQuery('#bulk-action-selector-top').val() == 'bulk-export') {
                var allVals = [];
                jQuery('input[name="bulk-select[]"]:checked').each(function () {
                    allVals.push(jQuery(this).attr('pr-id'));
                });
                //Check if array is empty and show alert if it is.
                if (allVals.length === 0) {
                    alert("Select atleast one enquiry to export");
                    return false;
                }
                jQuery.ajax({
                    type: 'POST',
                    url: url,
                    data: {
                        action: 'wdm_return_rows',
                        'ids': allVals,
                        'security': dashboard_quote_listing_bulk_actions.export_nonce,
                    },
                    success: function (response) {
                        if (response == 'SECURITY_ISSUE') {
                            alert(dashboard_quote_listing_bulk_actions.could_not_create_csv);
                            return false;
                        }
                        //sends the enquiry data in data variable through ajax response
                        jQuery('#data').val(response);
                        //form action to generate-csv which generates csv file
                        //with the required data in response.
                        jQuery('#csv_form').submit();
                    },
                    error: function (response) {
              // alert("error="+response);
          }
      });

                return false;
            }
        }

        if (jQuery(this).attr('id') == "doaction2") {
            var url = dashboard_quote_listing_bulk_actions.ajax_url;

            if (jQuery('#bulk-action-selector-bottom').val() == 'bulk-export') {
                var allVals = [];
                jQuery('input[name="bulk-select[]"]:checked').each(function () {
                    allVals.push(jQuery(this).attr('pr-id'));
                });
                //Check if array is empty and show alert if it is.
                if (allVals.length === 0) {
                    alert(dashboard_quote_listing_bulk_actions.select_one_enquiry);
                    return false;
                }
                jQuery.ajax({
                    type: 'POST',
                    url: url,
                    data: {
                        action: 'wdm_return_rows',
                        'ids': allVals,
                        'security': dashboard_quote_listing_bulk_actions.export_nonce,
                    },
                    success: function (response) {
                        if (response == 'SECURITY_ISSUE') {
                            alert(dashboard_quote_listing_bulk_actions.could_not_create_csv);
                            return false;
                        }
                        jQuery('#data').val(response);
                        jQuery('#csv_form').submit();
                    },
                    error: function (response) {
              // alert("error="+response);
          }
      });

                return false;
            }
        }

    });

//Apply the bulk actions on quotes list page.
jQuery('.button').click(function (e) {
  var url = dashboard_quote_listing_bulk_actions.ajax_url;
  if (jQuery(this).attr('id') == "doaction") {
    if (jQuery('#bulk-action-selector-top').val() == 'bulk-export-all') {
        jQuery.ajax({
            type: 'POST',
            url: url,
            data: {
                action: 'wdm_return_rows',
                'security': dashboard_quote_listing_bulk_actions.export_nonce,
                'status' : dashboard_quote_listing_bulk_actions.status,

            },
            success: function (response) {
                if (response == 'SECURITY_ISSUE') {
                    alert(dashboard_quote_listing_bulk_actions.could_not_create_csv);
                    return false;
                }
                jQuery('#data').val(response);
                jQuery('#csv_form').submit();
            },
            error: function (response) {
                      // alert("error="+response);
                  }
              });

        return false;
    }
}
//Bulk actions apply on bottom of the list.
if (jQuery(this).attr('id') == "doaction2") {
    if (jQuery('#bulk-action-selector-bottom').val() == 'bulk-export-all') {
        jQuery.ajax({
            type: 'POST',
            url: url,
            data: {
                action: 'wdm_return_rows',
                'security': dashboard_quote_listing_bulk_actions.export_nonce,
                'status' : dashboard_quote_listing_bulk_actions.status,
            },
            success: function (response) {
                if (response == 'SECURITY_ISSUE') {
                    alert(dashboard_quote_listing_bulk_actions.could_not_create_csv);
                    return false;
                }
                jQuery('#data').val(response);
                jQuery('#csv_form').submit();
            },
            error: function (response) {
                      // alert("error="+response);
                  }
              });

        return false;
    }
}
});
});
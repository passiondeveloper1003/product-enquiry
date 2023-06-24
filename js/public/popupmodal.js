jQuery(document).on('click', '.quotes', function () {

    var quoteid = jQuery(this).attr("id");


    jQuery.ajax({
        url: quoteupTableData.ajax_url,
        type: 'GET',
        data: {
            'id': quoteid,
            'action': 'popup_modal_enquiry'
        },
        success: function (data) {
            jQuery('.modal.fade.qmodal').remove();
            jQuery(".ajaxmodal").html(data);
            var id = "#wdm-quotes-modal-" + quoteid;
            jQuery("#hdnfld").val(quoteid);
            jQuery(id).css('display', 'block');
            jQuery(id).modal({
                show: true
            });
        }

    });
});

jQuery(document).on('click', '.close', function () {
    jQuery(".qmodal").css('display', 'none');
    jQuery(".qmodal").attr('aria-hidden', 'true');
});

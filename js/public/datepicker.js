jQuery(document).ready(function () {
// Datepicker for date field in enquiry form
jQuery('.date-field').datepicker({
    minDate: 0,
    altField: ".current-selected",
    showButtonPanel: false,
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
    autoclose: true,
});

    // when enquiry button is clicked add class current-selected to date field.
    jQuery(document).on("click",'.wdm_enquiry',function (event) {
        var id=jQuery(this).attr('id');
        var number = id.match("wdm-quoteup-trigger-(.*)");
        if (number) {
            var newdate= jQuery("#wdm-quoteup-modal-"+number[1]).find("#txtdate").addClass('current-selected');
        }
    });

    jQuery('.hasDatepicker').click(function(){
        jQuery('.ui-datepicker').css('z-index', 999999);
    });
});
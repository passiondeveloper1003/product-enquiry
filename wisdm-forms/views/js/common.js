jQuery(document).ready(function ( $ ) {
    jQuery(document).on('focus', '.datepicker', function(){
        jQuery(this).datepicker({
            dateFormat: dateData.date_format
        })
    })
})
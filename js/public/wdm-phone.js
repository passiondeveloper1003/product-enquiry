
// https://github.com/jackocnr/intl-tel-input

jQuery(document).ready(function ($) {
    let inputs = document.querySelectorAll("input.wdm-int-tel-input"),
        prefCountries = [];
    //    incCountryCodes = [];

    //if (typeof wdm_data != 'undefined' && typeof wdm_data.cf_phone_field_inc_cc != undefined) {
    //    incCountryCodes = wdm_data.cf_phone_field_inc_cc;
    //}

    if (typeof wdm_data != "undefined" && typeof wdm_data.cf_phone_field_pref_countries != "undefined") {
        prefCountries = wdm_data.cf_phone_field_pref_countries;
    }

    jQuery.each( inputs, function( index, input ) {
        window.intlTelInput(input, {
            separateDialCode: true,
            preferredCountries: prefCountries,
            initialCountry: "auto",
            geoIpLookup: function(callback) {
                $.get('https://ipinfo.io', function() {}, "jsonp").always(function(resp) {
                  var countryCode = (resp && resp.country) ? resp.country : "";
                  callback(countryCode);
                });
            }
        });
    });
});


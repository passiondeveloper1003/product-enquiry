<?php
    /**
     * @variable    $formsetting
     * @uses        Contains form element configurations
     * @origin      Controller: contactforms, Method: showform()
     */
    if (!defined('ABSPATH')) {
        die('!');
    }
    $url = get_permalink(isset($form_id) ? $form_id : get_the_ID());
    $sap = strpos($url, "?") ? "&" : "?";
    $purl = $url . $sap;
    $id = uniqid();
    $quoteupSettings = quoteupSettings();
    $phoneFieldsLabels = quoteupGetCustomFormPhoneFieldsLabel($quoteupSettings);
    ?>

    <!-- Start form -->
    <div class="w3eden">
        <div class="container-fluid">

        </div>
        <div class="container-fluid" id="wdm-container-fluid">
            <div id="method">
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="btn-group btn-breadcrumb">
                        <?php if (count($form_parts_names) > 1) { ?>
                            <?php foreach ($form_parts_names as $index => $crumb_text) { ?>
                                <li id='<?php echo $index . "_crumb" ?>' class='btn btn-default <?php if ($index == "form_part_0") {
                                    echo "active visited";
                                        } ?>' data-part="<?php echo $index ?>"><a disabled='disabled' class="breadcrumbs" href="" onclick='return false'><?php echo $crumb_text ?></a></li>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div id="formarea">
                <?php
                    $formClassAttr = 'wdm-quoteup-form wdm-custom-form';
                    if (quoteupIsMPEEnabled()) {
                        $formClassAttr .= 'wdm-mpe-form';
                    } else {
                        $formClassAttr .= 'wdm-spe-form';
                    }
                ?>
                <form id="form-<?php echo esc_attr($id); ?>"  action="" method="post" enctype="multipart/form-data" class="<?php echo esc_attr($formClassAttr); ?>">
                    <?php wp_nonce_field(NONCE_KEY, '__iswisdmform'); ?>
                    <input type="hidden" id="formid" name="form_id" value="<?php echo $form_id ?>" />
                    <?php

                    /**
                     * Before custom form fields are displayed.
                     *
                     * @param  int  $form_id  Custom form Id.
                     */
                    do_action('wisdmform-showform_before_form_fields', $form_id);
                    $part_count = 0;

                    foreach ($form_parts_html as $part) {
                        ?> <!-- part <?php echo $part_count ?> start --> <?php
                        echo $part;
                        ?> <!-- part <?php echo $part_count ?> end --> <?php
                        $part_count++;
                    }

                    /**
                     * After custom form fields are displayed.
                     *
                     * @param  int  $form_id  Custom form Id.
                     */
                    do_action('wisdmform-showform_after_form_fields', $form_id);
                    ?>
                </form>
            </div>
        </div>
    </div>
    <!-- End form -->
    <!--
    <script type='text/javascript'>
        var formObject = '';
        jQuery(document).ready(function($){
            $('.select2element').select2();
        });
    </script>
    -->
    <script type='text/javascript'>
        jQuery(document).ready(function($){
        $(function($) {
            var submit_btn_text;
            var next_part_id;
            var this_part_id;

            // nameErrMsg - name field invalid error message.
            // telErrMsg  - telephone field invalid error message.
            let nameErrMsg, telErrMsg;

            nameErrMsg = quoteup_cf_err_msg.name;
            telErrMsg  = quoteup_cf_err_msg.tel_err;

            $(document).ready(function() { //code
                // Show hard form partitions
                var set_show = {display: 'block'};
                var set_hide = {display: 'none'};
                var validator = $('#form-<?php echo $id; ?>').validate({
                    rules: {
                        "submitform[custname]": {
                            validateName: true
                        },
                        // Phone fields rules
                        <?php
                        foreach ($phoneFieldsLabels as $label) {
                            echo '"submitform[' . $label . ']"'; ?> : {
                            validatePhone: true,
                        },
                        <?php
                        }
                        ?>
                        // End for Phone fields rules
                    },
                    ignore: ":input:hidden:not([id^=Rating_])",
                    errorPlacement: function(error, element) {
                        if (element.is(':checkbox') || element.is(':radio')) {
                            error.appendTo(element.closest('div'));
                        } else if ('undefined' != typeof element.attr('id') && 'Rating_' == element.attr('id').match(/^Rating_/)) {
                            error.insertAfter(element.closest('div').find('div.form-group div[id^=Rating_]'));
                        }
                        else {
                            // element.nextAll('div:first').before(error);
                            element.closest('.form-group').children('div').last().before(error);
                        }
                    }
                });

                jQuery.extend( jQuery.validator.messages, {
                        email: quoteup_cf_err_msg.email,
                        url: quoteup_cf_err_msg.url,
                        date: quoteup_cf_err_msg.date,
                        dateISO: quoteup_cf_err_msg.dateISO,
                        number: quoteup_cf_err_msg.number,
                    }
                );

                jQuery.validator.addMethod("validateName", function (value, element) {
                    var validation = new RegExp('^([^0-9@#$%^&*()+{}:;\//"<>,.?*~`]*)$');
                    if(!validation.test(value)) {
                        // not valid input
                        return false;
                    }
                    else
                    {
                        // valid input
                        return true;
                    }
                }, nameErrMsg);

                // Validate phone number
                jQuery.validator.addMethod("validatePhone", function (value, element) {
                    let iti = window.intlTelInputGlobals.getInstance(element),
                        isValidNumber = iti.isValidNumber(),
                        validation = new RegExp('^\\+?[0-9\\s]+$');
                    
                    value = value.trim();

                    if (this.optional(element) || (isValidNumber && validation.test(value))) {
                        // Valid phone number.
                        return true;
                    } else {
                        // Invalid phone number.
                        return false;
                    }
                }, telErrMsg);
                // wdm code added commented
                // $("input[id^=rating_]").rules("add", {
                //     validateRating: true
                // });

                // jQuery.validator.addMethod("validateRating", function (value, element) {
                //     console.log("Hello world");
                //     var validation = new RegExp('^([^0-9@#$%^&*()+{}:;\//"<>,.?*~`]*)$');
                //     if(!validation.test(value)) {
                //         // not valid input
                //         return false;
                //     }
                //     else
                //     {
                //         // valid input
                //         return true;
                //     }
                // }, "Please provide rating");
                // wdm code added commented

                var validInput = true;

                //$('#form_part_0').css(set_show);
                $('#form-<?php echo $id; ?> .change-part').on('click', function(e) {
                    $('.formnotice').hide();
                    next_part_id = $(this).attr('data-next');
                    this_part_id = $(this).attr('data-parent');

                    id = $(this).attr('id');
                    prod_id = id.split('_')[1];;

                    // Pre validate
                    validInput = true;
                    var $form = $(this).closest('#' + this_part_id);
                    var $inputs = $(this).closest('#' + this_part_id).find(":input");

                    $inputs.each(function() {
                            if(!validator.element(this)) {
                                validInput = false;
                                $(this).closest('div.form-group').removeClass('has-success').addClass('has-error');
                            }
                            else {
                                $(this).closest('div.form-group').removeClass('has-error').addClass('has-success');
                            }
                    });

                    if (validInput == true) {
                        if (next_part_id != undefined) {
                            $('#' + this_part_id).css(set_hide);
                            $('#' + next_part_id).css(set_show);
                        }
                        $('#' + next_part_id + '_crumb').addClass('active');
                        $('#' + next_part_id + '_crumb').addClass('visited');
                        $('#' + this_part_id + '_crumb').removeClass('active');
                        $(this).closest('div.form-group').removeClass('has-error').addClass('has-success');

                    }
                });

                $('.breadcrumbs').on('click', function() {
                    var set_show = {display: 'block'};
                    var set_hide = {display: 'none'};
                    show_part_id = $(this).parent().attr('data-part');
                    hide_part_id = $('.breadcrumbli.active').attr('data-part');
                    if ($('#' + show_part_id + '_crumb').hasClass('visited')) {
                        $('.breadcrumbli.active').removeClass('active');
                        $(this).parent().addClass('active');
                        $('#' + hide_part_id).css(set_hide);
                        $('#' + show_part_id).css(set_show);
                    } else {
                        // Show the error
                        msgs = new Array();
                        msgs.push('Fill the current area to proceed');
                        showAlerts(msgs,'danger');
                    }

                });



                /*// ajax submit
                var options = {
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    // variation_id:variation_id,
                    // variation_detail:variation_detail,
                    resetForm: false,
                    beforeSubmit: function() {
                        submit_btn_text = $('#submit').html();
                        $('#submit').html("<i id='spinner' class='fa fa-spinner fa-spin'></i> Please wait");
                        $('#submit').prop('disabled', true);
                    }, // pre-submit callback
                    success: function(response) {
                        msgs = new Array();
                        $('#spinner').remove();
                        $('#submit').html(submit_btn_text);
                        $('#'+this_part_id).css(set_hide);
                        $('#form_part_0').css(set_show);
                        try {
                            response_vars = JSON.parse(response);
                        } catch (e) {
                            console.log(e);
                        }
                        if (response_vars.action == 'success' && validInput === true) {
                            msgs.push(response_vars.message);
                            showAlerts(msgs, 'success');
                        } else {

                                msgs.push(response_vars.message == '' ? 'Form submission failed, please check the entries again' : response_vars.message);
                                showAlerts(msgs, 'danger');

                        }
                    }
                };*/

                $('#form-<?php echo $id; ?>').on('submit', function(e) {
                    e.preventDefault();
                    $this = $('#form-<?php echo $id; ?>').find("button[type=submit]");
                    var wdm_checkbox_val = 0;
                    error_val= 0;
                    if($this.attr('id') != 'mpeSendCustom') {
                       id_send = $this.attr('id');
                       var id_array = id_send.match("btnSendCustom_(.*)");
                       if (jQuery("#" + id_send).closest('.row').siblings('.mpe_form_input').find("#contact-cc").is(":checked"))
                        {
                            wdm_checkbox_val = 'checked';
                        }
                    } else {
                        // sendRequestToUpdateCart(false, false);
                        if ( jQuery("#contact-cc").is(":checked") ) {
                            wdm_checkbox_val = 'checked';
                        }
                    }
                    if (validInput == true && error_val == 0) {
                        variation_id = '';
                        variation_detail = [];
                        var $variation_id_obj = '';

                        // Select variation Id element using setting value.
                        $variation_id_obj = jQuery(wdm_data.variation_id_selector);

                        // If variation Id element is not found.
                        if ($variation_id_obj.length == 0) {
                            $variation_id_obj = $('.variation_id');
    
                            if ("undefined" != typeof id_array)
                            {
                                $variation_id_obj = $('#wdm-quoteup-trigger-' + id_array[1]).closest('.summary.entry-summary').find('.variation_id:first-child');
                            }
                        }
                        
                        if ($variation_id_obj.length>0) {
                            variation_id = $variation_id_obj.val();
                            
                            $('select[name^=attribute_]').each(function(ind, obj){
                                name = $(this).attr('name');
                                name = name.substring(10);
                                variation = name + " : " + $(this).val();
                                variation_detail.push(variation);
                            });
                        }

                        quantity =1;
                        if($('input[name="quantity"]').length>0)
                        {
                            quantity =$('input[name="quantity"]').val();
                        }

                        $form_data = new FormData();

                        $form_data.append('action', 'submitCustomForm');
                        $form_data.append('submitform[variation_id]', variation_id);
                        $form_data.append('submitform[variation_detail]', variation_detail);

                        $form_data.append('submitform[product_quant]', quantity);
                        $form_data.append('submitform[cc]', wdm_checkbox_val);
                        var checkbox_names = {};

                        //Loop Through all input fields
                        $('#form-<?php echo $id; ?>').find(':input').each(function(){
                            if($(this).attr('type') == 'file') {
                                var attachedFiles = $(this).prop('files');
                                if(attachedFiles && attachedFiles.length > 0) {
                                    $(attachedFiles).each(function(index, value){
                                        $file = value;
                                        $file_size = $file.size;
                                        // $form_data.append( index, $file );
                                        $form_data.append( $file.name, $file );
                                    });
                                }
                            } else if($(this).attr('type') == 'checkbox') {

                                //Proceed only if checkbox is checked
                                if( !$(this).is(':checked') ){
                                    return true;
                                }
                                if($(this).attr('id') == 'contact-cc')
                                {
                                    return true;
                                }

                                let name = $(this).attr('name');
                                let current_value = $(this).val();

                                //Check if we have already started collecting current checkbox group
                                if(!(name in checkbox_names)) {
                                    checkbox_names[name] = current_value;
                                } else {
                                    checkbox_names[name] = checkbox_names[name] + "," + current_value;
                                }

                            } else if($(this).attr('type') == 'radio') {

                                //Proceed only if radio is checked
                                if( !$(this).is(':checked') ){
                                    return true;
                                }

                                $form_data.append($(this).attr('name'), $(this).val());

                            } else if ($(this).hasClass("g-recaptcha-response")) {
                                // If captcha version 2 and captcha field is visible.
                                if(!$(this).closest('div.form-group').is(":hidden")) {
                                    name = $(this).attr('name');
                                    value = $(this).val();
                                    $form_data.append(name, value);
                                }                                
                            } else if ($(this).hasClass('wdm-int-tel-input')) {
                                // If phone number field.
                                let iti = window.intlTelInputGlobals.getInstance(this),
                                    phoneNumber = iti.getNumber(),
                                    name = $(this).attr('name');                          
                                $form_data.append(name, phoneNumber);
                            } else {
                                name = $(this).attr('name');
                                value = $(this).val();
                                $form_data.append(name, value);
                            }
                        }); 

                        //append all checkboxes in $form_data
                        for (let name in checkbox_names) {
                            $form_data.append(name.replace('[]', ''), checkbox_names[name]);
                        }

                        // $('#form-<?php echo $id; ?>').find('select').each(function(){
                        //     name = $(this).attr('name');
                        //     value = $(this).val();
                        //     $form_data.append(name, value);
                        // });

                        $('#wdm-cart-count').hide();

                        let form_selector = '#form-<?php echo $id; ?>';
                        let $cookieConField = $(form_selector + ' input#cookie-consent-cb');
                        if($cookieConField.length > 0 && $cookieConField.is(":checked"))
                        {
                            let cname  = $(form_selector + ' input[name="submitform[custname]"]').val();
                            let cemail = $(form_selector + ' input[name="submitform[txtemail]"]').val();
                            fun_set_cookie(cname, cemail);
                        } else{
                            fun_remove_cookie();
                        }

                        if(typeof quoteup_captcha_data != 'undefined' && 'v3' == quoteup_captcha_data.captcha_version){
                            // captcha v3
                            let site_key = quoteup_captcha_data.site_key;
                            grecaptcha.execute(site_key, {action: 'quoteup_captcha'}).then(function(token) {
                                $form_data.append('g-recaptcha-response', token);
                                submitEnquiryFormAjax($form_data, $this, this_part_id, validInput, id_array, set_hide, set_show);
                            });
                        } else {
                            submitEnquiryFormAjax($form_data, $this, this_part_id, validInput, id_array, set_hide, set_show);
                        }
                    } else {
                        msgs = new Array();
                        msgs.push(err_string == '' ? quoteup_cf_err_msg.validation_err_msg : err_string);
                        if (jQuery('.g-recaptcha').length > 0){
                            grecaptcha.reset();
                        }
                        showAlerts(msgs, 'danger');
                    }
                    return false;
                });
            });
        });


        function submitEnquiryFormAjax($form_data, $this, this_part_id, validInput, id_array, set_hide,set_show)
        {
            let please_wait_text = wdm_data.please_wait_text;
            $.ajax({
                type: 'POST',
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                data: $form_data,
                contentType: false,
                processData: false,
                dataType: 'json',
                // async: false,
                cache: false,
                beforeSend: function() {
                    submit_btn_text = $this.html();
                    $this.html("<i id='spinner' class='fa fa-spinner fa-spin'></i> " + please_wait_text);
                    $this.prop('disabled', true);
                }, // pre-submit callback
                success: function(response) {
                    msgs = new Array();
                    $('#spinner').remove();
                    $this.html(submit_btn_text);
                    $('#'+this_part_id).css(set_hide);
                    $('#form_part_0').css(set_show);
                    $this.prop('disabled', false);

                    if (response['action'] == 'success' && validInput === true) {
                        msgs.push(response['message']);
                        showAlerts(msgs, 'success');
                        if($('.wdm-quoteup-woo').length>0) {
                            jQuery('.quoteup-quote-cart').slideUp();

                            /**
                             * Use this trigger event to perform the actions/ manipulations before
                             * the enquiry form is hidden when enquiry is successful.
                             *
                             * @param object $this      The jQuery object of the button clicked to submit the form.
                             * @param object $form_data Object of FormData containing the form values.
                             * @param object $reponse   Ajax response.
                             */
                            jQuery(document).trigger('quoteupEnquirySuccessBeforeFormHidden', [ $this, $form_data, response ]);

                            setTimeout(function(){
                                $('.success').slideDown();
                                $('html, body').animate({ scrollTop: $("#success").offset().top - 700 }, 0);

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
                        } else {
                            $this.closest('.wdm-quoteup-form').hide();
                            $('#success_' + id_array[1]).show();

                            /**
                             * Use this trigger event to perform the actions/ manipulations before
                             * the enquiry form is hidden when enquiry is successful.
                             *
                             * @param object $this      The jQuery object of the button clicked to submit the form.
                             * @param object $form_data Object of FormData containing the form values.
                             */
                            jQuery(document).trigger('quoteupEnquirySuccessBeforeFormHidden', [ $this, $form_data, response ]);

                            setTimeout(function() {
                                id = $this.attr('id');
                                number = id.match("btnSendCustom_(.*)");
                                modal_id = "#wdm-quoteup-modal-" + number[1];
                                jQuery(modal_id).modal('hide');
                                jQuery('.wdm-quoteup-form').css('display', 'none');
                                jQuery('.wdm-modal-footer').css('display', 'none');
                                jQuery('#error').css('display', 'none');
                                jQuery('#nonce_error').css('display', 'none');
                                jQuery('#success_' + number[1]).css('display', 'none'); 

                                /**
                                 * Use this trigger event to perform the actions/ manipulations after
                                 * timeout when enquiry is successful.
                                 *
                                 * @param object $this      The jQuery object of the button clicked to submit the form.
                                 * @param object $form_data Object of FormData containing the form values.
                                 * @param object $reponse   Ajax response.
                                 */
                                jQuery(document).trigger('quoteupEnquirySuccessAfterTimeout', [ $this, $form_data, response ]);
                            }, 2000)
                        }
                        <?php
                        $form_data = quoteupSettings();
                        $redirect_url = isset($form_data[ 'redirect_user' ])? $form_data[ 'redirect_user' ] : 'n';
                        ?>

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

                        if ( wdm_data.redirect != 'n' ) {
                            /**
                             * Use this trigger event before PEP redirects to another page.
                             *
                             * @param object $this      The jQuery object of the button clicked to submit the form.
                             * @param object $form_data Object of FormData containing the form values.
                             * @param object $reponse   Ajax response.
                             */
                            jQuery(document).trigger('quoteupBeforeRedirect', [ $this, $form_data, response ]);
                            window.location = "<?php echo esc_url($redirect_url); ?>";
                        }
                    } else {           
                        msgs.push(response['message'] == '' ? 'Form submission failed, please check the entries again' : response['message']);
                        if (jQuery('.g-recaptcha').length > 0){
                            grecaptcha.reset();
                        }
                        showAlerts(msgs, 'danger');
                    }
                }
            });
        }

        function conditionalHandling() {
            $('.conditioned').each(function(){
                var cur_field_id = $(this).attr('id');
                cur_conditioned_fields = $(this).attr('data-cond-fields');
                cur_cond_fields = cur_conditioned_fields.split('|');
                var form_fields_wrapper = $(this).closest('#form_part_0');
                for (i=0 ; i<cur_cond_fields.length ; i++) {
                    var cond_field      = cur_cond_fields[i].split(':');
                    let cond_field_0    = cond_field[0];
                    let cond_field_2    = cond_field[2].replace(/([!"#$%&'()*+,./:;<=>?@[\]^`{|}~])/g, "\\$1");

                    if ($('#'+cond_field_0).length>0) {
                        addConditionClass(form_fields_wrapper.find('#'+cond_field_0), cur_field_id, form_fields_wrapper);
                    }

                    if ($('#'+cond_field_2).length>0) {
                        addConditionClass(form_fields_wrapper.find('#'+cond_field_2), cur_field_id, form_fields_wrapper);
                    }

                }
                form_fields_wrapper.find('.cond_filler_'+cur_field_id).each(function(){
                    let curr_field_obj = $(this);
                    applyRule(cur_field_id);

                    // If current field is 'checkbox', 'radio' or 'select' field, then add 'change'
                    // event on the field.
                    if (curr_field_obj.attr('type') == 'checkbox' || curr_field_obj.attr('type') == 'radio' || curr_field_obj.is('select')) {
                        $(this).on('change', function(){
                            applyRule(cur_field_id);
                        });
                    } else if (curr_field_obj.attr('type') == 'text' || curr_field_obj.is(':input')) {
                        curr_field_obj.on('keyup', function(){
                            applyRule(cur_field_id);
                        });
                        curr_field_obj.on('focusout', function(){
                            applyRule(cur_field_id);
                        });

                        if ('undefined' != typeof curr_field_obj.attr('id') && 'Rating_' == curr_field_obj.attr('id').match(/^Rating_/)) {
                            curr_field_obj.on('change', function(){
                                applyRule(cur_field_id);
                            });
                        }
                    } else {
                        curr_field_obj.on('change', function(){
                            applyRule(cur_field_id);
                        });
                    }
                });
            });                
        }

        $(document).ready(function($){
            conditionalHandling();            
        });

        function showAlerts(msgs, type, form) {
            if(form === undefined)
            { 
                form = '';
            }

            $('.formnotice').slideUp();
            alert_box = '<div style="margin-top: 20px" class="alert formnotice alert-' + type + ' disappear"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
            for (i = 0; i < msgs.length; i++) {
                alert_box += '' + msgs[i] + '<br/>';
            }
            alert_box += '</div>';
            if(form != '') {
                form.closest('form').append(alert_box);
            } else {
                $('#form-<?php echo $id; ?>').append(alert_box);
            }

        }

        function addConditionClass(field_id, cond_class, form_fields_wrapper) {
            $(field_id).each(function(){
                if ($(this).is(':input') || $(this).is('select'))
                    $(this).addClass('cond_filler_'+cond_class);
                $(this).children().each(function(){
                    addConditionClass($(this), cond_class, form_fields_wrapper);
                })
            });
            return false;
        }

        function compareRule(objs, cmp_operator, cmp_value, cmp_id, $form_part_0) {
            var comp_res = false;
            var areOperandsCb = false; // Stores true if both operands are checkboxes.
            switch(cmp_operator) {
                case 'is':
                    if (cmp_value.startsWith('Checkbox_')) {
                        test = objs.closest('#form_part_0').find('#'+cmp_value+' :input:checked');
                        areOperandsCb = cmp_id.startsWith('Checkbox_') ? true : false;
                        if (areOperandsCb && objs.length != test.length) {
                            break;
                        }
                    } else {
                        test = objs.closest('#form_part_0').find('#'+cmp_value+' :input');
                    }

                    $(objs).each(function(){
                        if (areOperandsCb) {
                            comp_res = false;
                        }
                        $cmp1 = $(this).val();
                        $(test).each(function(){
                            $cmp2 = $(this).val();
                            if ($cmp1 == $cmp2) {
                                comp_res = true;
                                if (!areOperandsCb) {
                                    return;
                                }
                            }
                        });

                        if (areOperandsCb && false == comp_res) {
                            return;
                        }
                    });
                    break;
                case 'is-not':
                    if (cmp_value.startsWith('Checkbox_')) {
                        test = $form_part_0.find('#'+cmp_value+' :input:checked');
                        areOperandsCb = cmp_id.startsWith('Checkbox_') ? true : false;
                        if (areOperandsCb && objs.length != test.length) {
                            return true;
                        }
                    } else {
                        test = objs.closest('#form_part_0').find('#'+cmp_value+' :input');
                    }
                    
                    $.each(objs, function(obsIndex, objsElement) {
                        comp_res = false;
                        $cmp1 = $(objsElement).val();
                        $.each(test, function(testIndex, testElement) {
                            $cmp2 = $(testElement).val();
                            if ($cmp1 != $cmp2) {
                                comp_res = true;
                                // return;
                            } else if(areOperandsCb) {
                                comp_res = false;
                                return false;
                            }
                        });

                        if(areOperandsCb && true == comp_res) {
                            return false;
                        }
                    });
                    break;
                case 'less-than':
                    $(objs).each(function(){
                        // Return if current element is non-relevant input field inside 'Rating' field.
                        if ('undefined' != typeof $(this).attr('id') && 'Rating_' != $(this).attr('id').match(/^Rating_/) && $(this).closest('div[id^=Rating_]').length > 0) {
                            return;
                        }

                        // if cmp_value is number, convert it into number type data.
                        if (!isNaN(cmp_value)) {
                            cmp_value = Number(cmp_value);
                        }
                        if ($(this).val() < cmp_value) {
                            comp_res = true;
                            return;
                        }
                    });
                    break;
                case 'greater-than':
                    $(objs).each(function(){
                    // if cmp_value is number, convert it into number type data.
                        if (!isNaN(cmp_value)) {
                            cmp_value = Number(cmp_value);
                        }
                        if ($(this).val() > cmp_value) {
                            comp_res = true;
                            return;
                        }
                    });
                    break;
                case 'starts-with':
                    $(objs).each(function(){
                        if ($(this).val().indexOf(cmp_value) == 0) {
                            comp_res = true;
                            return;
                        }
                    });
                    break;
                case 'contains':
                    $(objs).each(function(){
                        if ($(this).val().indexOf(cmp_value) != -1) {
                            comp_res = true;
                            return;
                        }
                    });
                    break;
                case 'ends-with':
                    $(objs).each(function(){
                        indexPoint = ($(this).val().length - cmp_value.length);
                        if (indexPoint >=0 && $(this).val().indexOf(cmp_value, indexPoint) == indexPoint) {
                            comp_res = true;
                            return;
                        }
                    });
                    break;
                default:
                    comp_res = false;
                    break;

            }

            return comp_res;
        }

        function applyRule(field_id) {
            $('.cond_filler_'+field_id).each(function(){
                var this_conditions = $('#'+field_id).attr('data-cond-fields').split('|');
                var this_action = $('#'+field_id).attr('data-cond-action').split(':');
                var cmp_res = this_action[1] == 'all' ? true : false;
                for (i=0 ; i<this_conditions.length ; i++) {
                    var this_condition = this_conditions[i].split(':'),
                        $form_part_0    = null;
                    cmp_id = this_condition[0];
                    cmp_objs = null;
                    $form_part_0 = $(this).closest('#form_part_0');
                    if (cmp_id.indexOf('Checkbox_') == 0 || cmp_id.indexOf('Radio_') == 0) {
                        cmp_objs = $(this).closest('#form_part_0').find('#'+cmp_id).find(':checked');
                    } else {
                        cmp_objs = $(this).closest('#form_part_0').find('#'+cmp_id+' :input');
                    }
                    cmp_operator = this_condition[1];
                    cmp_value = this_condition[2];
                    tmp_res = compareRule(cmp_objs, cmp_operator, cmp_value, cmp_id, $form_part_0);
                    if ('all' == this_action[1]) cmp_res = (Number(cmp_res) + Number(tmp_res) == 2);
                    else cmp_res = cmp_res || tmp_res;
                }
                if (cmp_res == true) {
                    if(this_action[0] == 'show') {
                        $(this).closest('#form_part_0').find('#'+field_id).removeClass('hide');                        
                    } else {
                        $(this).closest('#form_part_0').find('#'+field_id).addClass('hide');//$('#'+field_id).addClass('hide');
                    }
                } else {
                    if(this_action[0] == 'show') {
                        $(this).closest('#form_part_0').find('#'+field_id).addClass('hide');
                    } else {
                        $(this).closest('#form_part_0').find('#'+field_id).removeClass('hide');
                    }
                }
            });

        }

        // if (!String.prototype.startsWith) {
        //     String.prototype.startsWith = function(searchString, position) {
        //         position = position || 0;
        //         return this.indexOf(searchString, position) === position;
        //     };
        // }
        
        });
    </script>


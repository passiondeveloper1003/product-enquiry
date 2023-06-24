<?php

if (isset($_GET['page']) && $_GET['page'] == "quoteup-create-quote") {
    add_action('wisdmform-showform_before_form_fields', 'addHiddenFieldsBeforeInDashboard');
    add_action('wisdmform-showform_after_form_fields', 'addHiddenFieldsAfterInDashboard');
} else {
    add_action('wisdmform-showform_before_form_fields', 'addHiddenFields');
}

function addHiddenFieldsBeforeInDashboard()
{
    $ajax_nonce = wp_create_nonce('quoteup');
        $url = admin_url();
    ?>
    <input type='hidden' name='quote_ajax_nonce' id='quote_ajax_nonce' value='<?php echo $ajax_nonce;
    ?>'>
    <input type='hidden' name='submit_value' id='submit_value'>
    <input type='hidden' name='site_url' id='site_url' value='<?php echo $url;
    ?>'>
    <input type='hidden' name='tried' id='tried' value='yes' />
    <div id='wdm_nonce_error'>
        <div  class='wdmquoteup-err-display'>
            <span class='wdm-quoteupicon wdm-quoteupicon-exclamation-circle'></span><?php _e('Unauthorized enquiry', QUOTEUP_TEXT_DOMAIN) ?>
        </div>
    </div>
    <?php
}

function addHiddenFieldsAfterInDashboard()
{
    ?>
    <div id="text"></div>
    <div class='form-errors-wrap mpe_form_input' id='wdm-quoteupform-error'>
        <div class='mpe-left' style='height: 1px;'></div>
        <div class='mpe-right form-errors'>
            <ul class='error-list'>
            </ul>
        </div>
    </div>
    <?php
}

function addHiddenFields()
{
    global $product;
    $form_data = quoteupSettings();
    $currentLocale = get_locale();
    $arr = explode("_", $currentLocale, 2);
    $currentLocale = $arr[0];
    if (quoteupIsWpmlActive()) {
        global $sitepress;
        $currentLocale = $sitepress->get_current_language();
    }
    if ((!isset($form_data[ 'enable_disable_mpe' ]) || $form_data[ 'enable_disable_mpe' ] != 1) && $product) {
        global $wpdb;
        $prod_id = $product->get_id();

        $query = "select user_email from {$wpdb->posts} as p join {$wpdb->users} as u on p.post_author=u.ID where p.ID=%d";
        $uemail = $wpdb->get_var($wpdb->prepare($query, $prod_id));
        $img_url = wp_get_attachment_url(get_post_thumbnail_id($prod_id));
        $prod_price = $product->get_price_html();
        $prod_price = strip_tags($prod_price);
        $price = $prod_price;
        $url = get_permalink($prod_id);
        ?>
        <input type='hidden' name='submitform[wdmLocale]' id='wdmLocale' value='<?php echo $currentLocale ?>'>
        <input type='hidden' name='submitform[submit_value]' id='submit_value'>

        <input type='hidden' name="submitform[product_name]" id="product_name_<?php echo $prod_id ?>" value='<?php echo get_the_title() ?>'>
        <input type='hidden' name="submitform[product_type]" id="product_type_<?php echo $prod_id; ?>">
        <input type='hidden' name="submitform[variation]" id="variation_<?php echo $prod_id; ?>">
        <input type='hidden' name='submitform[product_id]' id='product_id_<?php echo $prod_id ?>' value='<?php echo $prod_id ?>'>
        <input type='hidden' name='submitform[uemail]' id='author_email' value='<?php echo $uemail ?>'>
        <input type='hidden' name='submitform[product_img]' id='product_img_<?php echo $prod_id ?>' value='<?php echo $img_url ?>'>
        <input type='hidden' name='submitform[product_price]' id='product_price_<?php echo $prod_id ?>' value='<?php echo $price ?>'>
        <input type='hidden' name='submitform[product_url]' id='product_url_<?php echo $prod_id ?>' value='<?php echo $url ?>'>
        <input type='hidden' name='submitform[site_url]' id='site_url' value='<?php echo admin_url() ?>'>
        <?php
    } else {
        echo "<input type='hidden' name='submitform[wdmLocale]' id='wdmLocale' value='{$currentLocale}'>";
    }
}


function getIsCondSelectorOptions()
{
    ?>
    <option value='is'><?php _e('Is Equal To', QUOTEUP_TEXT_DOMAIN) ?></option>
    <option value='is-not'><?php _e('Is not equal to', QUOTEUP_TEXT_DOMAIN) ?></option>
    <option value='less-than'><?php _e('Less than', QUOTEUP_TEXT_DOMAIN) ?></option>
    <option value='greater-than'><?php _e('Greater than', QUOTEUP_TEXT_DOMAIN) ?></option>
    <option value='contains'><?php _e('Contains', QUOTEUP_TEXT_DOMAIN) ?></option>
    <option value='starts-with'><?php _e('Starts with', QUOTEUP_TEXT_DOMAIN) ?></option>
    <option value='ends-with'><?php _e('Ends with', QUOTEUP_TEXT_DOMAIN) ?></option>
    <?php
}


function getFormLabelDiv($fieldindex, $field_infos)
{
    ?>
    <div class="form-group">
        <label><?php _e('Label', QUOTEUP_TEXT_DOMAIN) ?>:</label>
        <input class="form-control form-field-label" data-target="#label_<?php echo $fieldindex; ?>" type="text" value="<?php echo $field_infos[$fieldindex]['label'] ?>" name="contact[fieldsinfo][<?php echo $fieldindex ?>][label]" />
    </div>
    <?php
}


function getConfTempLabelDiv()
{
    ?>
    <div class="form-group">
        <label><?php _e('Label', QUOTEUP_TEXT_DOMAIN) ?>:</label>
        <input class="form-control form-field-label" data-target="#label_{{ID}}" type="text" value="{{title}}" name="contact[fieldsinfo][{{ID}}][label]"/>
    </div>
    <?php
}

function getFormNoteDiv($fieldindex, $field_infos)
{
    ?>
    <div class="form-group">
        <label><?php _e('Note', QUOTEUP_TEXT_DOMAIN) ?></label>
        <textarea class="form-control" type="text" value="" name="contact[fieldsinfo][<?php echo $fieldindex ?>][note]"><?php echo $field_infos[$fieldindex]['note'] ?></textarea>
    </div>
    <?php
}

function getConfTempNoteDiv()
{
    ?>
    <div class="form-group">
        <label><?php _e('Note', QUOTEUP_TEXT_DOMAIN) ?></label>
        <textarea class="form-control" type="text" value="" name="contact[fieldsinfo][{{ID}}][note]"></textarea>
    </div>
    <?php
}

function getFormConditionalDiv($fieldindex, $field_infos)
{
    ?>
    <div class='form-group'>
        <label><input rel='condition-params' class='cond' type='checkbox' name='contact[fieldsinfo][<?php echo $fieldindex ?>][conditioned]' value='1' <?php if (isset($field_infos[$fieldindex]['conditioned'])) {
            echo 'checked="checked"';
                                                                                                    } ?>/><?php _e('Conditional logic', QUOTEUP_TEXT_DOMAIN) ?></label>
        <div id="cond_<?php echo $fieldindex ?>" class='cond-params' style='display:none'>
            <div class="form-group">
                <div class="row row-bottom-buffer">
                    <div class="col-md-12">
                        <select class="select" name="contact[fieldsinfo][<?php echo $fieldindex ?>][condition][action]">
                            <option <?php if (isset($field_infos[$fieldindex]['condition']) && $field_infos[$fieldindex]['condition']['action'] == 'show') {
                                echo 'selected="selected"';
                                    } ?> value="show"><?php _e('Show', QUOTEUP_TEXT_DOMAIN) ?></option>
                            <option <?php if (isset($field_infos[$fieldindex]['condition']) && $field_infos[$fieldindex]['condition']['action'] == 'hide') {
                                echo 'selected="selected"';
                                    } ?> value="hide"><?php _e('Hide', QUOTEUP_TEXT_DOMAIN) ?></option>
                        </select> 
                        <?php _e('this field if', QUOTEUP_TEXT_DOMAIN) ?>
                        <select class="select" name="contact[fieldsinfo][<?php echo $fieldindex ?>][condition][boolean_op]">
                            <option <?php if (isset($field_infos[$fieldindex]['condition']) && $field_infos[$fieldindex]['condition']['boolean_op'] == 'all') {
                                echo 'selected="selected"';
                                    } ?> value="all"><?php _e('All', QUOTEUP_TEXT_DOMAIN) ?></option>
                            <option <?php if (isset($field_infos[$fieldindex]['condition']) && $field_infos[$fieldindex]['condition']['boolean_op'] == 'any') {
                                echo 'selected="selected"';
                                    } ?> value="any"><?php _e('Any', QUOTEUP_TEXT_DOMAIN) ?></option>
                        </select>
                        <?php _e('of these conditions are met', QUOTEUP_TEXT_DOMAIN) ?>
                    </div>
                </div>
                <?php $cond_list = isset($field_infos[$fieldindex]['condition']) ? $field_infos[$fieldindex]['condition']['value'] : array(); ?>
                <?php
                foreach ($cond_list as $key => $value) { ?>
                <div class='row row-bottom-buffer' rel="row">
                    <div class='col-md-4'>
                        <select class='form-control cond-field-selector' data-selection='<?php echo isset($field_infos[$fieldindex]['condition']['field'][$key]) ? $field_infos[$fieldindex]['condition']['field'][$key] : ''  ?>' name='contact[fieldsinfo][<?php echo $fieldindex ?>][condition][field][]'>
                            <option value=""><?php _e('Select a field', QUOTEUP_TEXT_DOMAIN) ?></option>
                        </select>
                    </div>
                    <div class='col-md-3'>
                        <select class='form-control cond-operator' data-selection='<?php echo isset($field_infos[$fieldindex]['condition']['op'][$key]) ? $field_infos[$fieldindex]['condition']['op'][$key] : ''  ?>' name='contact[fieldsinfo][<?php echo $fieldindex ?>][condition][op][]'>
                        <?php getIsCondSelectorOptions(); ?>
                        </select>
                    </div>
                    <div class='col-md-4'>
                        <select class='form-control is-cond-selector' data-selection='<?php echo isset($field_infos[$fieldindex]['condition']['value'][$key]) ? $field_infos[$fieldindex]['condition']['value'][$key] : ''  ?>'>
                            <option value='email'>Email</option>
                            <option value='phone'>Phone</option>
                        </select>
                        <input type='text' class='form-control is-cond-text hide' data-selection='<?php echo isset($field_infos[$fieldindex]['condition']['value'][$key]) ? $field_infos[$fieldindex]['condition']['value'][$key] : ''  ?>' placeholder="<?php _e('Enter a value', QUOTEUP_TEXT_DOMAIN) ?>" value=''/>
                        <input type='hidden' value='<?php echo isset($field_infos[$fieldindex]['condition']['value'][$key]) ? $field_infos[$fieldindex]['condition']['value'][$key] : ''  ?>' class="is-cond-data" name='contact[fieldsinfo][<?php echo $fieldindex ?>][condition][value][]'/>
                    </div>
                    <div class="col-md-1">
                        <a href="#" class="add-cond-option"
                           rel="<?php echo $fieldindex ?>"><i
                                class="glyphicon glyphicon-plus-sign pull-left"></i></a>
                        <a href="#" class="del-cond-option"
                           rel="<?php echo $fieldindex ?>"><i
                                class="glyphicon glyphicon-minus-sign pull-left"></i></a>

                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php
}


function getConfTempConditionalDiv()
{
    ?>
    <div class='form-group'>
        <label><input rel='condition-params' class='cond' type='checkbox' name='contact[fieldsinfo][{{ID}}][conditioned]' value='1'/> <?php _e('Conditional logic', QUOTEUP_TEXT_DOMAIN) ?></label>
        <div id="cond_{{ID}}" class='cond-params' style='display:none'>
            <div class='form-group'>
                <div class="row row-bottom-buffer">
                    <div class="col-md-12">
                        <select class="select" name="contact[fieldsinfo][{{ID}}][condition][action]">
                            <option value="show"><?php _e('Show', QUOTEUP_TEXT_DOMAIN) ?></option>
                            <option value="hide"><?php _e('Hide', QUOTEUP_TEXT_DOMAIN) ?></option>
                        </select> 
                        <?php _e('this field if', QUOTEUP_TEXT_DOMAIN) ?>
                        <select class="select" name="contact[fieldsinfo][{{ID}}][condition][boolean_op]">
                            <option value="all"><?php _e('All', QUOTEUP_TEXT_DOMAIN) ?></option>
                            <option value="any"><?php _e('Any', QUOTEUP_TEXT_DOMAIN) ?></option>
                        </select>
                        <?php _e('of these conditions are met', QUOTEUP_TEXT_DOMAIN) ?>
                    </div>
                </div>
                <div class='row row-bottom-buffer' rel='row'>
                    <div class='col-md-4'>
                        <select class='form-control cond-field-selector' data-selection='' name='contact[fieldsinfo][{{ID}}][condition][field][]'>
                            <option value=""><?php _e('Select a field', QUOTEUP_TEXT_DOMAIN) ?></option>
                        </select>
                    </div>
                    <div class='col-md-3'>
                        <select class='form-control cond-operator' name='contact[fieldsinfo][{{ID}}][condition][op][]'>
                            <?php getIsCondSelectorOptions(); ?>
                        </select>
                    </div>
                    <div class='col-md-4'>
                        <select class='form-control is-cond-selector'>
                            <option value='email'>Email</option>
                            <option value='phone'>Phone</option>
                        </select>
                        <input type='text' class='form-control is-cond-text hide' placeholder="<?php _e('Enter a value', QUOTEUP_TEXT_DOMAIN) ?>" value=''/>
                        <input type='hidden' value='' class='is-cond-data' name='contact[fieldsinfo][{{ID}}][condition][value][]'/>
                    </div>
                    <div class="col-md-1">
                        <a href="#" class="add-cond-option"
                           rel="{{ID}}"><i
                                class="glyphicon glyphicon-plus-sign pull-left"></i></a>
                        <a href="#" class="del-cond-option"
                           rel="{{ID}}"><i
                                class="glyphicon glyphicon-minus-sign pull-left"></i></a>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function getFormRequiredDiv($fieldindex, $field_infos)
{
    ?>
    <div class="form-group">
        <label>
        <?php
        if (!isset($field_infos[$fieldindex]['primary']) || $field_infos[$fieldindex]['primary'] != 'yes') {
            ?>
            <input rel="req-params" class="req" type="checkbox" name="contact[fieldsinfo][<?php echo $fieldindex ?>][required]" value="1" <?php echo (isset($field_infos[$fieldindex]['required']) ? "checked=checked" : "") ?> />
            <?php
        }
            _e('Required', QUOTEUP_TEXT_DOMAIN);
        ?>
        </label>
        <div class="req-params" <?php echo (!isset($field_infos[$fieldindex]['required']) ? "style='display: none'" : ""); ?> >
            <input type="text" name="contact[fieldsinfo][<?php echo $fieldindex ?>][reqmsg]" placeholder="<?php _e('Field Required Message', QUOTEUP_TEXT_DOMAIN); ?>" value="<?php echo $field_infos[$fieldindex]['reqmsg'] ?>" class="form-control"/>
        </div>
    </div>
    <?php
}

function getConfTempRequiredDiv()
{
    ?>
    <div class="form-group">
        <label><input rel="req-params" class="req" type="checkbox" name="contact[fieldsinfo][{{ID}}][required]" value="1"/>
            <?php _e('Required', QUOTEUP_TEXT_DOMAIN) ?>
        </label>

        <div class="req-params" style="display: none">
            <input type="text" name="contact[fieldsinfo][{{ID}}][reqmsg]"
                   placeholder="<?php _e('Field Required Message', QUOTEUP_TEXT_DOMAIN) ?>" value="" class="form-control"/>
        </div>
    </div>
    <?php
}

/**
 * Returns the phone fields' labels created in the custom form.
 *
 * @since 6.3.4
 *
 * @return array Phone fields' labels if exist, otherwise returns an empty
 *               array.
 */
function quoteupGetCustomFormPhoneFieldsLabel($quoteupSettings)
{
    static $phoneLabels = array();

    if (empty($phoneLabels)) {
        $quoteupSettings = quoteupSettings();
        $formId          = $quoteupSettings['custom_formId'];
        $customFormData  = get_post_meta($formId, 'form_data', true);
        $fieldsInfo      = $customFormData['fieldsinfo'];

        foreach ($customFormData['fields'] as $key => $value) {
            if ('Phone' == $value) {
                $phoneLabels[] = $fieldsInfo[$key]['label'];
            }
        }
    }

    return $phoneLabels;
}

/**
 * Check if Rating field exists in the currenct active custom form.
 *
 * @since 6.5.0
 *
 * @return  bool  Return true if Rating field exists, false otherwise.
 */
function quoteupRatingFieldExists()
{
    static $ratingFieldExists = null;

    if (null === $ratingFieldExists) {
        $ratingFieldExists = false;
        $quoteupSettings   = quoteupSettings();
        $formId            = $quoteupSettings['custom_formId'];
        $customFormData    = get_post_meta($formId, 'form_data', true);

        foreach ($customFormData['fields'] as $value) {
            if ('Rating' == $value) {
                $ratingFieldExists = true;
            }
        }
    }

    return $ratingFieldExists;
}

/**
 * Check if Date field exists in the current active custom form.
 *
 * @since 6.5.0
 *
 * @return  bool  Return true if Date field exists, false otherwise.
 */
function quoteupDateFieldExists()
{
    static $dateFieldExists = null;

    if (null === $dateFieldExists) {
        $dateFieldExists = false;
        $quoteupSettings = quoteupSettings();
        $formId          = $quoteupSettings['custom_formId'];
        $customFormData  = get_post_meta($formId, 'form_data', true);

        foreach ($customFormData['fields'] as $value) {
            if ('Date' == $value || 'Daterange' == $value) {
                $dateFieldExists = true;
            }
        }
    }

    return $dateFieldExists;
}

/**
 * Enqueues the style and scripts required to custom form phone fields.
 * Checks whether custom form is enabled and form has atleast one phone
 * field.
 *
 * @since 6.3.4
 * @param  bool  $shouldEnqueueScripts  If set to true, the styles and scripts are
 *               enqueued without checking the conditions. Default false.
 *
 * @return void
 */
function quoteupEnqueuePhoneFieldsScripts($shouldEnqueueScripts = false)
{
    $quoteupSettings      = quoteupSettings();

    /**
     * Enqueue styles and scripts after performing following checks:
     * 1. Custom Form should be enabled.
     * 2. There should be atleast one phone field in the active custom form.
     * 3. If Multi Product Enquiry mode is enabled, then the current page
     *    should be enquiry cart page.
     */
    $shouldEnqueueScripts = $shouldEnqueueScripts || (quoteupIsCustomFormEnabled($quoteupSettings) && !empty(quoteupGetCustomFormPhoneFieldsLabel($quoteupSettings)) && (!quoteupIsMPEEnabled($quoteupSettings) || quoteupIsEnquiryCartPage($quoteupSettings))) ? true : false;

    /**
     * Filter to decide whether the scripts required for the Phone field present in the Custom Form should be enqueued.
     *
     * @since 6.3.4
     *
     * @param   bool    True if scripts required for phone field in custom form should be enqueued, false otherwise.
     */
    $shouldEnqueueScripts = apply_filters('quoteup_should_enqueue_cf_pf_scripts', $shouldEnqueueScripts);

    if ($shouldEnqueueScripts) {
        // Enqueue style
        wp_enqueue_style('quoteup-int-tel-input-css', QUOTEUP_PLUGIN_URL . '/css/public/quoteup-intlTelInput.css', array(), filemtime(QUOTEUP_PLUGIN_DIR.'/css/public/quoteup-intlTelInput.css'));

        // Enqueue scripts
        wp_enqueue_script('quoteup-int-tel-input-js', QUOTEUP_PLUGIN_URL . '/js/public/intlTelInput.min.js', array('jquery'), filemtime(QUOTEUP_PLUGIN_DIR.'/js/public/intlTelInput.min.js'), true);
        wp_enqueue_script('quoteup-int-tel-input-util-js', QUOTEUP_PLUGIN_URL . '/js/public/utils.js', array('jquery'), filemtime(QUOTEUP_PLUGIN_DIR.'/js/public/utils.js'), true);
        wp_enqueue_script('quoteup-custom-phone-js', QUOTEUP_PLUGIN_URL . '/js/public/wdm-phone.js', array('jquery', 'quoteup-int-tel-input-js', 'quoteup-int-tel-input-util-js'), filemtime(QUOTEUP_PLUGIN_DIR.'/js/public/wdm-phone.js'), true);
    }
}

/**
 * This function returns the preferred country codes to be displayed
 * in custom form phone field.
 *
 * @since 6.3.4
 *
 * @return array Return preferred counry codes.
 */
function quoteupReturnPreferredCountryCodes()
{
    $prefCountryCodes = array();
    $prefCountryCodes = apply_filters('cf_phone_field_pref_countries', $prefCountryCodes);

    return $prefCountryCodes;
}

/**
 * Check whether the label for a particular custom form field should be shown
 * or not.
 *
 * @return  bool  Return true if the label for the form field should be shown,
 *                false otherwise.
 */
function quoteupCFFieldsLabel($fieldId)
{
    static $shouldShowLabel = null;
    
    if (null === $shouldShowLabel) {
        $quoteupSettings = quoteupSettings();
        $formId          = $quoteupSettings['custom_formId'];

        // Fetch custom form fields labels status from the database.
        $labels_status = get_post_meta($formId, 'show_labels_to_fields', true);
    
        if ('1' == $labels_status) {
            $shouldShowLabel = true;
        } else {
            $shouldShowLabel = false;
        }
    }

    /**
     * Use the filter to show/ hide the custom form fields label.
     *
     * @since 6.4.4
     *
     * @param   bool    $shouldShowLabel  Boolean value to show or hide the custom form field label.
     * @param   string  $fieldId  Custom form field ID.
     */
    return apply_filters('quoteup_show_cf_fields_label', $shouldShowLabel, $fieldId);
}

/**
 * This function returns the country codes which are included in the custom
 * form phone field.
 *
 * @since 6.3.4
 *
 * @return array Return the includes country codes.
 */
function quoteupReturnOnlyAllowedCC()
{
    $incCountryCodes = array();
    $allCountryCodes = quoteupReturnAllCountryCodes();
    $incCountryCodes = apply_filters('quoteup_cf_phone_field_inc_cc', $allCountryCodes);

    return $incCountryCodes;
}

/**
 * Returns all country codes.
 *
 * @since 6.3.4
 *
 * @return array Return all country codes.
 */
function quoteupReturnAllCountryCodes()
{
    $allCountryCodes = array('AF','AL','DZ','AS','AD','AO','AI','AG','AR','AM','AW','AU',
    'AT','AZ','BS','BH','BD','BB','BY','BE','BZ','BJ','BM','BT','BO','BA','BW','BR','IO',
    'VG','BN','BG','BF','BI','KH','CM','CA','CV','BQ','KY','CF','TD','CL','CN','CX','CC',
    'CO','KM','CD','CG','CK','CR','CI','HR','CU','CW','CY','CZ','DK','DJ','DM','DO','EC',
    'EG','SV','GQ','ER','EE','ET','FK','FO','FJ','FI','FR','GF','PF','GA','GM','GE','DE',
    'GH','GI','GR','GL','GD','GP','GU','GT','GG','GN','GW','GY','HT','HN','HK','HU','IS',
    'IN','ID','IR','IQ','IE','IM','IL','IT','JM','JP','JE','JO','KZ','KE','KI','XK','KW',
    'KG','LA','LV','LB','LS','LR','LY','LI','LT','LU','MO','MK','MG','MW','MY','MV','ML',
    'MT','MH','MQ','MR','MU','YT','MX','FM','MD','MC','MN','ME','MS','MA','MZ','MM','NA',
    'NR','NP','NL','NC','NZ','NI','NE','NG','NU','NF','KP','MP','NO','OM','PK','PW','PS',
    'PA','PG','PY','PE','PH','PL','PT','PR','QA','RE','RO','RU','RW','BL','SH','KN','LC',
    'MF','PM','VC','WS','SM','ST','SA','SN','RS','SC','SL','SG','SX','SK','SI','SB','SO',
    'ZA','KR','SS','ES','LK','SD','SR','SJ','SZ','SE','CH','SY','TW','TJ','TZ','TH','TL',
    'TG','TK','TO','TT','TN','TR','TM','TC','TV','VI','UG','UA','AE','GB','US','UY','UZ',
    'VU','VA','VE','VN','WF','EH','YE','ZM','ZW','AX');

    return $allCountryCodes;
}

/**
 * Localize the script.
 * These messages are used in custom form error messages.
 * To change the custom form error messages, use the filter added in the
 * method.
 *
 * https://github.com/jquery-validation/jquery-validation/tree/master/src/localization
 *
 * @since   6.4.0
 */
function quoteupCustomFormErrorMessages()
{
    $messages = array(
        'name' => __('Please enter valid name.', QUOTEUP_TEXT_DOMAIN),
        'email' => __('Please enter a valid email address.', QUOTEUP_TEXT_DOMAIN),
        'url' => __('Please enter a valid URL.', QUOTEUP_TEXT_DOMAIN),
        'date' => __('Please enter a valid date.', QUOTEUP_TEXT_DOMAIN),
        'number' => __('Please enter a valid number.', QUOTEUP_TEXT_DOMAIN),
        'tel_err' => __('Please enter valid telephone no.', QUOTEUP_TEXT_DOMAIN),
    );

    if (is_admin()) {
        $messages['validation_err_msg'] = __('Please fill this section properly before proceeding', QUOTEUP_TEXT_DOMAIN);
        $scriptHandle = 'jquery-validation-plugin';
    } else {
        $messages['validation_err_msg'] = __('Validation Error', QUOTEUP_TEXT_DOMAIN);
        $scriptHandle = 'jquery-validate';
    }
    
    $messages = apply_filters('quoteup_cf_err_messages', $messages);
    wp_localize_script($scriptHandle, 'quoteup_cf_err_msg', $messages);
}

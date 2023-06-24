<?php

/**
 * Check and return the HTML attributes for required, required-message
 * and validation type.
 * These attributes will be added to the custom form field HTML elements.
 *
 * @param  array  $reqparams  A custom form field data.
 *
 * @return  string  Return the string containing the required, required-message
 *                  and validation type HTML attributes.
 */
function required($reqparams)
{
    $str = '';
    if (isset($reqparams['required'])) {
        $fieldId  = $reqparams['id'];
        $msg      = ('' != $reqparams['reqmsg']) ? $reqparams['reqmsg'] : __('Please fill out this field', QUOTEUP_TEXT_DOMAIN);
        /**
         * Filter to modify the custom form field's required message.
         *
         * @param  string  $msg  Required message for custom form field.
         */
        $msg      = apply_filters('quoteup_change_cf_req_msg_'.$fieldId, $msg);
        $msg      = __($msg, QUOTEUP_TEXT_DOMAIN);
        $msg      = esc_attr($msg);
        $str     .= " required='required' data-msg-required='{$msg}' ";
    }

    if (isset($reqparams['validation'])) {
        $type = $reqparams['validation'];
        $str .= " data-rule-{$type}='true' ";
    }


//    $patterns['numeric'] = '[0-9]';
//    $patterns['email'] = '*@-.-';
//    $patterns['url'] = 'https?://.+';
//    $patterns['creditcard'] = '[0-9]{13,16}';
//    $patterns['text'] = '*[a-zA-Z0-9-_.';
    return $str;
}

function quoteupReturnCustomFormFieldNote($note) {
    $note = __($note, QUOTEUP_TEXT_DOMAIN);
    return $note;
}

function quoteupReturnCustomFormFieldPlaceholder($placeholder) {
    $placeholder = __($placeholder, QUOTEUP_TEXT_DOMAIN);
    return $placeholder;
}

function quoteupReturnCustomFormFieldLabel($label) {
    $label = __($label, QUOTEUP_TEXT_DOMAIN);
    return $label;
}

function is_valid_email($email, $skipDNS = true) {
    $isValid = true;
    if (!is_string($email))
        return false;
    $atIndex = strrpos($email, "@");
    if (is_bool($atIndex) && !$atIndex) {
        $isValid = false;
    } else {
        // $domain = substr($email, $atIndex + 1);
        // $local = substr($email, 0, $atIndex);
        // $localLen = strlen($local);
        // $domainLen = strlen($domain);
        // if ($localLen < 1 || $localLen > 64) {
        //     // local part length exceeded
        //     $isValid = false;
        // } else if ($domainLen < 1 || $domainLen > 255) {
        //     // domain part length exceeded
        //     $isValid = false;
        // } else if ($local[0] == '.' || $local[$localLen - 1] == '.') {
        //     // local part starts or ends with '.'
        //     $isValid = false;
        // } else if (preg_match('/\\.\\./', $local)) {
        //     // local part has two consecutive dots
        //     $isValid = false;
        // } else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
        //     // character not valid in domain part
        //     $isValid = false;
        // } else if (preg_match('/\\.\\./', $domain)) {
        //     // domain part has two consecutive dots
        //     $isValid = false;
        // } else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\", "", $local))) {
        //     // character not valid in local part unless
        //     // local part is quoted
        //     if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\", "", $local))) {
        //         $isValid = false;
        //     }
        // }
        
        //$regex = '/(?:[a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])+$/';

        $isValid = filter_var($email, FILTER_VALIDATE_EMAIL);
        if(!$isValid) {
            $isValid = false;
        }
        
        //$isValid = (bool) (preg_match('/^[^\/\:\*\?\"\<\>\|\.]+$/', $filename))




        if (!$skipDNS) {
            if ($isValid && !(checkdnsrr($domain, "MX") || checkdnsrr($domain, "A"))) {
                // domain not found in DNS
                $isValid = false;
            }
        }
    }
    return $isValid;
}

function eWdmForm($var, $def = "") {
    return ( isset($var) ? $var : $def );
}

function ph($str) {
    return "placeholder='{$str}' ";
}

/**
 * This function is used to get css settings
 * @param  [array] $form_data [settings stored in database]
 * @return [int]            [1 if manual css is selected]
 */
function getCssSettings($form_data)
{
    $manual_css = '';
    if (isset($form_data[ 'button_CSS' ]) && $form_data[ 'button_CSS' ] == 'manual_css') {
        $manual_css = 1;
    }

    return $manual_css;
}

/**
 * 
 * @param type $formsetting Contains form data and fields info. Basically a definition of the form's structure.
 *                                 - Which field takes which type of input
 *                                 - If a field is required
 *                                 - What message to show if not filled
 *                                 - Field label
 * @param type $field_defs Predefined structure of each of the field types. Serves the HTML used to render each field
 *                                 - Definition of field types
 *                                 - HTML for field types
 * @return type 
 *                 array(
 *                     array( strings containing HTML of the form partitions, each partion in a separate index ),
 *                     array( strings conaining each breadcrumb for individual form parts )
 *                 )
 */
function paginate_form($formsetting, $field_defs) {
    global $product;
    $formsetting_raw = $formsetting;
    $forms_view = $formsetting_raw['fields'];
    $forms_pref = $formsetting_raw['fieldsinfo'];
    $quoteupSettings = quoteupSettings();
    $buttonID = '';
    if (isset($quoteupSettings[ 'enable_disable_mpe' ]) && $quoteupSettings[ 'enable_disable_mpe' ] == 1) {
        $buttonID = 'mpeSendCustom';
    } elseif (isset($_GET['page']) && $_GET['page'] == "quoteup-create-quote") {
        $buttonID = 'mpeSendCustom';
    } elseif($product) {
        $prod_id = $product->get_id();
        $buttonID = "btnSendCustom_$prod_id";
    }

    $manual_css = getCssSettings($quoteupSettings);
    $css = '';
    if ($manual_css == 1) {
        $css = getManualCSS($quoteupSettings);
    }

    // $formfields = new formfields();
    // $advancedfields = new advancedfields();
    // $commonfields = $field_defs['fields_common'];
    // $generic_fields = $field_defs['fields_generic'];
    // $advanced_fields = $field_defs['fields_advanced'];
    $form_parts_html = array();
    $part_number = 0;
    $part_html = "<div style='display: block' id='form_part_{$part_number}'>";
    $form_parts_names = array("form_part_{$part_number}" => 'Start');
    foreach ($forms_view as $id => $type) {
        $cur_pref = $forms_pref[$id];
        $cur_pref['id'] = $id;
        if ($type == 'Pageseparator') {
            $prev_part = $part_number - 1;
            $tmp_obj = new $type();
            $part_name = $tmp_obj->field_render_html($cur_pref);
            $parent_part = $part_number;

            $part_number++;
            $back_button_html = "<a id='goto_part_{$prev_part}' data-parent='form_part_{$parent_part}' data-next='form_part_{$prev_part}' class='btn pull-left change-part'><< Back</a>";
            $next_button_html = "<a id='goto_part_{$part_number}' data-parent='form_part_{$parent_part}' data-next='form_part_{$part_number}' class='btn pull-right change-part'>Next >></a>";
            $change_part_button_html = ($prev_part < 0 ? "<div class='col-md-12'>" : "<div class='col-md-6'>{$back_button_html}</div><div class='col-md-6'>") . $next_button_html . "</div>";
            $form_parts_html[] = $part_html . "<div class='row'>{$change_part_button_html}</div></div>";
            $part_html = "<div style='display: none' id='form_part_{$part_number}'>";
            if (empty($part_name))
                $part_name = 'Untitled';
            $form_parts_names["form_part_{$part_number}"] = $part_name;
            continue;
        }

        //if ($type != 'Password')
            //$part_html .= "<label for='field_' style='display: block;clear: both'>{$cur_pref['label']}</label>";
            //
        
        $className = 'Wisdmforms\\'.$type;
        $tmp_obj = new $className();
        $part_html .= $tmp_obj->field_render_html($cur_pref);

    }

    if (!empty($part_html)) {
        $prev_part = $part_number - 1;
        $parent_part = $part_number;
        $back_button_html = "<a id='goto_part_{$prev_part}' data-parent='form_part_{$parent_part}' data-next='form_part_{$prev_part}' class='btn pull-left change-part'><< Back</a>";
        $submit_button_html = "<button " . $css . " type='submit' id='$buttonID' class='submit-btn btn pull-right change-part button' data-parent='form_part_{$parent_part}'>" . (false == isset($formsetting_raw['buttontext']) || '' == $formsetting_raw['buttontext'] ? __("Submit", QUOTEUP_TEXT_DOMAIN) : __($formsetting_raw['buttontext'], QUOTEUP_TEXT_DOMAIN)). "</button>";
        $submit_section_html = "<div class='row'>" . ($part_number > 0 ? "<div class='col-md-6'>{$back_button_html}</div><div class='col-md-6'>" : "<div class='col-md-12'>") . $submit_button_html . "</div></div>";
        $enable_mc = '';
        if (isset($quoteupSettings[ 'enable_custom_send_mail_copy' ])) {
            $enable_mc = $quoteupSettings[ 'enable_custom_send_mail_copy' ];
        }
        $sendCopy = '';
        if ($enable_mc == 1 && (!isset($_GET['page']) || $_GET['page'] !== 'quoteup-create-quote')) {
            $sendCopy .= "<div class='ck mpe_form_input'>";
            $sendCopy .= "<label class='mpe-right contact-cc-wrap'";
            if (isset($dialogue_text_color)) {
                $sendCopy .= " style=' color: ".$dialogue_text_color.";'";
            }
            $sendCopy .= "><input type='checkbox' id='contact-cc'  name='cc' value='yes' /> <span class='contact-cc-txt'>".__('Send me a copy', QUOTEUP_TEXT_DOMAIN).'</span> </label>';
            $sendCopy .= '</div>';
        }

        $isQuoteCreatePage = (!isset($_GET['page']) || $_GET['page'] !== 'quoteup-create-quote') ? false : true;

        $sendCopy = apply_filters("quoteup_wisdmform_alter_send_copy", $sendCopy, $isQuoteCreatePage);

        $form_parts_html[] = $part_html . $sendCopy . $submit_section_html . "</div>";
    }

    return array(
        'form_parts_html' => $form_parts_html,
        'form_parts_names' => $form_parts_names
    );
}

function get_currency_symbolised_amount($amount, $currency) {
    $symbol = '';
    switch ($currency) {
        case 'USD':
            $symbol = '$';
            break;
        case 'EUR':
            $symbol = '€';
            break;
    }
    return $symbol . $amount;
}

/**
 * Convet and object into an array (Recursively) 
 * @param type $object
 * @return type
 */
function make_array($object) {
    if (!is_object($object) && !is_array($object)) {
        return $object;
    } else {
        $object = get_object_vars($object);
    }
    return array_map('make_array', $object);
}

/**
 * Get the client's IP address
 * 
 */
function get_client_ip() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if (getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if (getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if (getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if (getenv('HTTP_FORWARDED'))
        $ipaddress = getenv('HTTP_FORWARDED');
    else if (getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';

    return $ipaddress;
}

if (!function_exists('my_pagination')) :

    function my_pagination() {

        global $wp_query;

        $big = 999999999; // need an unlikely integer

        echo paginate_links(array(
            'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
            'format' => '?paged=%#%',
            'current' => max(1, get_query_var('paged')),
            'total' => $wp_query->max_num_pages
        ));
    }

endif;

function get_concatenated_string($var) {
    if (is_object($var)) {
        $array = make_array($var);
    } else {
        $array = $var;
    }
    $string = implode(' ', $array);

    return $string;
}

if (!function_exists('add_url_fragment')) {
    function add_url_fragment($url, $fragments = array()) {
        $fragments_str = '';
        if (is_array($fragments)) {
            foreach($fragments as $frag_key => $frag_val) {
               $fragments_str .= ($frag_key . '=' . $frag_val . '&');
            }
            $fragments_str = trim($fragments_str, '&');
        } else {
            $fragments_str = $fragments;
        }

        if (strstr($url, '?')) {
            $url = $url . '&' . $fragments_str;
        } else {
            $url = $url . '?' . $fragments_str;
        }

        return str_replace('#','',$url);
    }
}

?>

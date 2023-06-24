<?php
namespace Includes;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/**
 * This class is responsible for adding the fields selected by admin in settings on the quote form.
 * Gets the custom fields HTML .
 * $fields array custom fields on form.
 * $temp_fields array temporary field
 * $meta_key string meta keys from meta table
 *
 * @static instance Object of class
 */
class QuoteUpAddCustomField
{
    protected static $instance = null;
    public $fields = array();
    public $temp_fields = array();
    public $meta_key;

    /**
     * Action to enqueue scripts on admin and front-end for custom fields.
     * Action to add custom fields in form
     * Action to update meta table with keys of fields included.
     * Action to create the custom fields.
     * Action to add the fields recursively on form.
     * Action to include the fields in admin and customer emails.
     * Action to add fields in MPE form in front-end and dashboard.
     */
    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueueMultiSelectScript'));
        add_action('quoteup_add_custom_field_in_form', array($this, 'addCustomFields'));
        add_action('quoteup_add_custom_field_in_db', array($this, 'addCustomFieldsDb'), 10, 2);
        add_action('quoteup_add_dashboard_custom_field_in_db', array($this, 'addCustomFieldsDb'), 10, 2); // Used to add custom fields from dashboard in DB
        add_action('quoteup_create_custom_field', array($this, 'createCustomFields'));
        add_action('quoteup_create_dashboard_custom_field', array($this, 'createCustomFields'));
        add_filter('quoteup_get_custom_field', array($this, 'getCustomFields'));
        add_action('quoteup_add_custom_field_admin_email', array($this, 'addCustomFieldsAdminEmail'));
        add_action('quoteup_add_custom_form_field_in_email', array($this, 'addCustomFormFieldsAdminEmail'), 10, 2);
        add_action('quoteup_add_custom_field_customer_email', array($this, 'addCustomFieldsCustomerEmail'), 20, 2);
        add_action('quoteup_custom_fields_header', array($this, 'quoteupCustomFieldsHeader'));
        add_action('quoteup_custom_fields_data', array($this, 'quoteupCustomFieldsData'));
        add_action('quoteup_custom_fields_customer_data_section', array($this, 'displayCustomFieldsDataEnquiryEdit'));
        add_action('quoteup_delete_custom_fields', array($this, 'deleteCustomFields'));
        add_action('mpe_add_custom_field_in_form', array($this, 'addCustomFieldsOnMPEForm'));
        add_action('quote_add_custom_field_in_form', array($this, 'addCustomFieldsOnQuoteForm'));
    }


    /**
     * Function to create a singleton instance of class and return the same.
     *
     * @return object -Object of the class
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Add fields for admin email.
     *
     * @param string $email_content email content
     * @param array  $data          Enquiry details
     */
    public function addCustomFormFieldsAdminEmail($data, $source)
    {
        $email = '';
        $data  = apply_filters('quoteup_custom_form_fields_in_enq_email', $data, $source);

        foreach ($data as $key => $value) {
            switch ($key) {
                case 'wdmLocale':
                case 'product_quant':
                case 'submit_value':
                case 'product_name':
                case 'product_type':
                case 'variation':
                case 'product_id':
                case 'uemail':
                case 'product_img':
                case 'product_price':
                case 'product_url':
                case 'site_url':
                case 'variation_id':
                case 'variation_detail':
                case 'action':
                case 'quoteProductsData':
                case 'show-price':
                case 'security':
                case 'globalEnquiryID':
                case '__iswisdmform':
                case '_wp_http_referer':
                case 'form_id':
                case 'quote_ajax_nonce':
                case 'tried':
                case 'cc':
                case 'mc-subscription-checkbox':
                    break;
                case 'custname':
                    $email .= "<tr>
                        <th style='width:35%;text-align:left'>".__('Customer Name', QUOTEUP_TEXT_DOMAIN)." </th>
                        <td style='width:65%'>: ".stripslashes($value).'</td>
                        </tr>';
                    break;

                case 'txtemail':
                    $email .= "<tr>
                        <th style='width:35%;text-align:left'>".__('Email', QUOTEUP_TEXT_DOMAIN)." </th>
                        <td style='width:65%'>: ".stripslashes($value).'</td>
                        </tr>';
                    break;
                
                default:
                    $value  = stripslashes($value);
                    $email .= "<tr>
                        <th style='width:35%;text-align:left'>".quoteupReturnWPMLVariableStrTranslation($key)." </th>
                        <td style='width:65%'>: ".quoteupReturnWPMLVariableStrTranslation($value).'</td>
                        </tr>';
                    break;
            }
        }
        echo $email;
    }

    /**
     * This function is used to load multi-select script.
     *
     * @return void
     */
    public function enqueueMultiSelectScript()
    {
        wp_enqueue_script('multipleSelectJs', QUOTEUP_PLUGIN_URL.'/js/public/multiple-select.js', array('jquery'), filemtime(QUOTEUP_PLUGIN_DIR . '/js/public/multiple-select.js'), true);
    }

    /**
     * Enqueue the default form style.
     *
     * @since 6.5.0
     *
     * @return void
     */
    public function enqueueDefaultFormStyle()
    {
        wp_enqueue_style('multipleSelectCss', QUOTEUP_PLUGIN_URL.'/css/public/multiple-select.css', array(), filemtime(QUOTEUP_PLUGIN_DIR . '/css/public/multiple-select.css'));
    }

    /**
     * This function is used to make field readonly.
     *
     * @param [array] $val        [description]
     * @param string  $product_id [product id]
     *
     * @return [string] [set read only]
     */
    public function makeFieldReadonly($val)
    {
        $temp = '';
        if (isset($val[ 'id' ]) && $val[ 'id' ] == 'txtdate') {
            $temp = " readonly='readonly'";
        }

        return $temp;
    }

    /**
     * Gets the template for custom field.
     *
     * @param  string $type type of field
     * @param  array  $val  Array to create field
     * @return string html $temp Template for custom field
     */
    private function getCustomFieldWrapper($val, $type)
    {
        $temp = '<div class="form_input">';
        $temp .= "<div class='form-wrap'><div class='form-wrap-inner'>";
        $temp .= "<input type='". $type ."'";
        if (isset($val[ 'id' ])) {
            $temp .= " name='".$val[ 'id' ]."'";
        }
        $temp .= " id='".$val[ 'id' ]."'";
        if (isset($val[ 'required' ])) {
            $temp .= ' '.(($val[ 'required' ] == 'yes') ? 'required' : '');
        }

        $temp .= $this->addClassToField($val);
        $temp .= $this->addValueToField($val);

        return $temp;
    }

    /**
     * This function is used to add text field on single product enquiry form.
     *
     * @param array $val        Array to create field
     * @param int   $product_id Product ID
     *
     * @return string HTML string for text field
     */
    public function customTextField($val, $product_id)
    {
        $temp = $this->getCustomFieldWrapper($val, 'text');
        $temp = '<div class="form_input">';
        $temp .= "<div class='form-wrap'><div class='form-wrap-inner'>";
        $temp .= "<input type='text'";
        if (isset($val[ 'id' ])) {
            $temp .= " name='".$val[ 'id' ]."'";
        }

        if (isset($val[ 'id' ]) && 'txtdate' == $val[ 'id' ]) {
            $temp .= " id='".$val[ 'id' ].'-'.$product_id."'";
        } else {
            $temp .= " id='".$val[ 'id' ]."'";
        }
        if (isset($val[ 'required' ])) {
            $temp .= ' '.(($val[ 'required' ] == 'yes') ? 'required' : '');
        }

        $temp .= $this->addClassToField($val);
        $temp .= $this->addValueToField($val);

        $temp .= ' placeholder="'.$val[ 'placeholder' ].(($val[ 'required' ] == 'yes') ? '*' : '').'"';

        $temp .= $this->makeFieldReadonly($val);

        $temp .= '/>';

        return $temp.'</div></div></div>';
    }

    /**
     * This function is used to add textarea field on single product enquiry form.
     *
     * @param array $val Array to create field
     *
     * @return string HTML string for text field
     */
    public function customTextareaField($val)
    {
        $temp = '<div class="form_input">';
        $temp .= "<div class='form-wrap'><div class='form-wrap-inner'>";
        $temp .= '<textarea';
        if (isset($val[ 'id' ])) {
            $temp .= " name='".$val[ 'id' ]."'";
            $temp .= " id='".$val[ 'id' ]."'";
        }
        if (isset($val[ 'required' ])) {
            $temp .= ' '.(($val[ 'required' ] == 'yes') ? 'required' : '');
        }
        $temp .= $this->addClassToField($val);

        $temp .= ' placeholder="'.$val[ 'placeholder' ].(($val[ 'required' ] == 'yes') ? '*' : '').'"';
        if (isset($val[ 'id' ]) && isset($val[ 'id' ]) == 'txtmsg') {
            $temp .= "maxlength='500'";
        }
        $temp .= " rows='5'>";
        if (isset($val[ 'value' ])) {
            $temp .= $val[ 'value' ];
        }

        $temp .= '</textarea>';
        if (isset($val[ 'id' ]) && isset($val[ 'id' ]) == 'txtmsg') {
            $temp .= "<label class='lbl-char' id='lbl-char'><span class='wdmRemainingCount'>500 </span> ". __('characters remaining', QUOTEUP_TEXT_DOMAIN) ."</label>";
        }

        return $temp.'</div></div></div>';
    }

    /**
     * This function is used to add Radio field on single product enquiry form.
     *
     * @param array $val Array to create field
     *
     * @return string HTML string for text field
     */
    public function customRadioField($val)
    {
        $temp = '<div class="form_input ';
        if (isset($val[ 'class' ])) {
            $temp .= $val[ 'class' ];
        }
        $temp .= '">';
        $temp .= "<div class='form-wrap'><div class='form-wrap-inner'>";
        $temp .= $val[ 'label' ].(($val[ 'required' ] == 'yes') ? '<sup class="req">*</sup>' : '').':&nbsp&nbsp';
        if (count($val[ 'options' ]) > 0) {
            $temp = $this->forEachRadioField($val, $temp);

            return $temp.'</div></div></div>';
        }
    }

    /**
     * For each radio field set the name, value to the selected radio field.
     * Also find if it is required field or not and add the same to html.
     *
     * @param  array  $val  Array to create field
     * @param  string $temp HTML for radio
     * @return string $temp HTML for radio
     */
    private function forEachRadioField($val, $temp)
    {
        foreach ($val[ 'options' ] as $key => $value) {
            $temp .= "<input type='radio' ";
            if (isset($val[ 'id' ])) {
                $temp .= " name='".$val[ 'id' ]."'";
                $temp .= " id='".$val[ 'id' ]."'";
            }
            if (isset($val[ 'required' ])) {
                $temp .= ' '.(($val[ 'required' ] == 'yes') ? 'required' : '');
            }
            $temp .= ' placeholder="'.$val[ 'placeholder' ].(($val[ 'required' ] == 'yes') ? '*' : '').'"';
            if (isset($value)) {
                $temp .= " value='".$value."'/>".$value;
            } else {
                $temp .= '/>';
            }
            unset($key);
        }

        return $temp;
    }

    /**
     * This function is used to add Select field on single product enquiry form.
     *
     * @param array $val Array to create field
     *
     * @return string HTML string for text field
     */
    public function customSelectField($val)
    {
        $temp = '<div class="form_input ';
        $temp .= $this->addClassToField($val);
        $temp .= '">';
        $temp .= "<div class='form-wrap'><div class='form-wrap-inner'>";
        $temp .= $val[ 'label' ].(($val[ 'required' ] == 'yes') ? '<sup class="req">*</sup>' : '').':&nbsp&nbsp<select ';
        if (isset($val[ 'id' ])) {
            $temp .= " name='".$val[ 'id' ]."'";
        }
        if (isset($val[ 'id' ])) {
            $temp .= " id='".$val[ 'id' ]."'";
        }
        $temp .= ' >';

        if (count($val[ 'options' ]) > 0) {
            if (isset($val['default_text'])) {
                $temp .= "<option value='#'>".$val['default_text'].'</option>';
            }
            foreach ($val[ 'options' ] as $key => $value) {
                if (isset($value)) {
                    $temp .= "<option value='".$value."'>".$value.'</option>';
                }

                unset($key);
            }
        }
        $temp .= '</select>';

        return $temp.'</div></div></div>';
    }

    /**
     * This function is used to add Checkbox field on single product enquiry form.
     *
     * @param array $val Array to create field
     *
     * @return string HTML string for text field
     */
    public function customcheckboxField($val)
    {
        return $this->checkboxRadioFieldWrapper($val, 'checkbox');
    }

    /**
     * Add the wrapper field HTML Dom structure for Radio or checkbox field.
     * Adds the options available for radio or checkbox.
     *
     * @param  array  $val  Array to create field
     * @param  string $type radio or checkbox
     * @return string html DOM structure for radio and checkbox
     */
    private function checkboxRadioFieldWrapper($val, $type)
    {
        $temp = '<div class="mpe_form_input ';

        if (isset($val[ 'class' ])) {
            $temp .= $val[ 'class' ];
        }

        $temp .= '">';
        $temp .= '<label class="mpe-left wdm-enquiry-form-label">';
        $temp .= $val[ 'label' ].(($val[ 'required' ] == 'yes') ? '<sup class="req">*</sup>' : '');
        $temp .= '</label> <div class="mpe-right"><div class="mpe-right-inner">';
        if (count($val[ 'options' ]) <= 0) {
            return $temp.'</div></div></div>';
        }
        
        foreach ($val[ 'options' ] as $key => $value) {
            $temp .= "<input type='". $type ."' ";
            if (isset($val[ 'id' ])) {
                $temp .= " name='".$val[ 'id' ]."[]'";
                $temp .= " id='".$val[ 'id' ]."'";
            }
            if (isset($val[ 'required' ])) {
                $temp .= ' '.(($val[ 'required' ] == 'yes') ? 'required' : '');
            }
            if (isset($value)) {
                $temp .= " value='".$value."'>".$value;
            }
            unset($key);
        }

        return $temp.'</div></div></div>';
    }

    /**
     * Gets the template of checkbox for the options provided for enquiries.
     * Sets the value for checkbox.
     *
     * @param  array  $val   options values
     * @param  string $value value for checkbox
     * @return string $temp template for checkbox
     */
    public function forEachOption($val, $value)
    {
        $temp = "<input type='checkbox' ";
        if (isset($val[ 'id' ])) {
            $temp .= " name='".$val[ 'id' ]."[]'";
            $temp .= " id='".$val[ 'id' ]."'";
        }

        if (isset($val[ 'required' ])) {
            $temp .= ' '.(($val[ 'required' ] == 'yes') ? 'required' : '');
        }

        if (isset($value)) {
            $temp .= " value='".$value."'>".$value;
        }

        return $temp;
    }

    /**
     * This function is used to add Multiple field on single product enquiry form.
     *
     * @param array $val Array to create field
     *
     * @return string HTML string for text field
     */
    public function customMultipleField($val)
    {
        $this->enqueueMultiSelectScript();

        $temp = '<div class="form_input ';
        if (isset($val[ 'class' ])) {
            $temp .= $val[ 'class' ];
        }
        $temp .= '">';
        $temp .= $val[ 'label' ].(($val[ 'required' ] == 'yes') ? '<sup class="req">*</sup>' : '');
        $temp .= '<select class="wdm-custom-multiple-fields" ';
        if (isset($val[ 'id' ])) {
            $temp .= " name='".$val[ 'id' ]."'";
        }
        if (isset($val[ 'id' ])) {
            $temp .= " id='".$val[ 'id' ]."'";
        }
        $temp .= ' multiple>';
        if (count($val[ 'options' ]) > 0) {
            foreach ($val[ 'options' ] as $key => $value) {
                if (isset($value)) {
                    $temp .= "<option value='".$value."'>".$value.'</option>';
                }
                unset($key);
            }
        }
        $temp .= '</select>';

        return $temp.'</div>';
    }

    /**
     * Gets the template for custom file upload field.
     *
     * @param  array $val Array to create field
     * @return string html for file upload field
     */
    public function customFileUploadField($val)
    {
        $temp = $this->getCustomFieldWrapper($val, 'file');
        $temp .= ' placeholder="'.$val[ 'placeholder' ].(($val[ 'required' ] == 'yes') ? '*' : '').'"';
        $temp .= ' multiple />';

        return $temp.'</div></div></div>';
    }

    /**
     * This function is used to add custom fields on single product enquiry form.
     *
     * @param int $product_id Product id
     */
    public function addCustomFields($product_id)
    {
        $this->enqueueDefaultFormStyle();

        $temp = '';
        foreach ($this->fields as $key => $val) {
            if (isset($val[ 'type' ])) {
                if ($val[ 'type' ] == 'text') {
                    $temp .= $this->customTextField($val, $product_id);
                } elseif ($val[ 'type' ] == 'textarea') {
                    $temp .= $this->customTextareaField($val);
                } elseif ($val[ 'type' ] == 'radio') {
                    $temp .= $this->customRadioField($val);
                } elseif ($val[ 'type' ] == 'select') {
                    $temp .= $this->customSelectField($val);
                } elseif ($val[ 'type' ] == 'checkbox') {
                    $temp .= $this->customcheckboxField($val);
                } elseif ($val[ 'type' ] == 'multiple') {
                    $temp .= $this->customMultipleField($val);
                } elseif ($val[ 'type' ] == 'file') {
                    $temp .= $this->customFileUploadField($val);
                }
            }
            unset($key);
        }
        echo $temp;
    }

    /**
     * Adds custom fields selected in MPE form by admin include in front-end and dashboard.
     *
     * @param  array $v fields selected to be included.
     * @return string html template of field.
     */
    public function addFieldsInMpeAndDashboard($v, $temp)
    {
        if (isset($v[ 'type' ])) {
            if ($v[ 'type' ] == 'text') {
                $temp .= $this->addTextField($v);
            } elseif ($v[ 'type' ] == 'textarea') {
                $temp .= $this->addTextareaField($v);
            } elseif ($v[ 'type' ] == 'radio') {
                $temp .= $this->addRadioField($v);
            } elseif ($v[ 'type' ] == 'select') {
                $temp .= $this->addSelectField($v);
            } elseif ($v[ 'type' ] == 'checkbox') {
                $temp .= $this->customcheckboxField($v);
            } elseif ($v[ 'type' ] == 'multiple') {
                $temp .= $this->addMultipleField($v);
            } elseif ($v[ 'type' ] == 'file') {
                $temp .= $this->addFileField($v);
            }
        }

        return $temp;
    }

    /**
     * This function is used to add fields on MPE form.
     */
    public function addCustomFieldsOnMPEForm()
    {
        $this->enqueueDefaultFormStyle();
        
        $temp = '';

        foreach ($this->fields as $key => $v) {
            $temp = $this->addFieldsInMpeAndDashboard($v, $temp);
        }
        echo $temp;
        unset($key);
    }

    /**
     * This function is used to add fields on Quote form.
     */
    public function addCustomFieldsOnQuoteForm()
    {
        $temp = '';

        foreach ($this->fields as $key => $v) {
            if (!isset($v[ 'include_in_quote_form' ]) || isset($v[ 'include_in_quote_form' ]) && $v[ 'include_in_quote_form' ] == 'yes') {
                $temp = $this->addFieldsInMpeAndDashboard($v, $temp);
            }
        }
        echo $temp;
        unset($key);
    }

    //This functions are used to add different types of fields on MPE form

    /**
     * This function is used to add Text field on MPE form.
     *
     * @param array $val Array to create field
     */
    public function addTextField($val)
    {
        $temp = '<div class="mpe_form_input">';
        $temp .= '<label class="mpe-left wdm-enquiry-form-label">';
        $temp .= $val[ 'label' ].(($val[ 'required' ] == 'yes') ? '<sup class="req">*</sup>' : '');
        $temp .= '</label>
                            <div class="mpe-right"><div class="mpe-right-inner">';
        $temp .= "<input type='text'";
        if (isset($val[ 'id' ])) {
            $temp .= " name='".$val[ 'id' ]."'";
        }
        $temp .= " id='".$val[ 'id' ]."'";
        if (isset($val[ 'required' ])) {
            $temp .= ' '.(($val[ 'required' ] == 'yes') ? 'required' : '');
        }
        $temp .= ' placeholder="'.$val[ 'placeholder' ].'"';

        $temp .= $this->addClassToField($val);
        $temp .= $this->addValueToField($val);

        $temp .= $this->makeFieldReadonly($val);
        $temp .= '>';

        return $temp.'</div></div></div>';
    }

    /**
     * This function is used to add Textarea field on MPE form.
     *
     * @param array $val Array to create field
     */
    public function addTextareaField($val)
    {
        $temp = '<div class="mpe_form_input">';
        $temp .= '<label class="mpe-left wdm-enquiry-form-label">';
        $temp .= $val[ 'placeholder' ].(($val[ 'required' ] == 'yes') ? '<sup class="req">*</sup>' : '');
        $temp .= '</label>
                            <div class="mpe-right"><div class="mpe-right-inner">';
        $temp .= '<textarea';
        if (isset($val[ 'id' ])) {
            $temp .= " name='".$val[ 'id' ]."'";
            $temp .= " id='".$val[ 'id' ]."'";
        }

        if (isset($val[ 'required' ])) {
            $temp .= ' '.(($val[ 'required' ] == 'yes') ? 'required' : '');
        }

        $temp .= ' placeholder="'.$val[ 'placeholder' ].'"';

        $temp .= $this->addClassToField($val);

        if (isset($val[ 'id' ]) && isset($val[ 'id' ]) == 'txtmsg') {
            $temp .= "maxlength='500'";
        }
        $temp .= " rows='5'>";
        if (isset($val[ 'value' ])) {
            $temp .= $val[ 'value' ];
        }
        $temp .= '</textarea>';
        if (isset($val[ 'id' ]) && isset($val[ 'id' ]) == 'txtmsg') {
            $temp .= "<label class='lbl-char' id='lbl-char'><span class='wdmRemainingCount'>500 </span> ".__('characters remaining', QUOTEUP_TEXT_DOMAIN) ."</label>";
        }

        return $temp.'</div></div></div>';
    }

    /**
     * This function is used to add Radio field on MPE form.
     *
     * @param array $val Array to create field
     */
    public function addRadioField($val)
    {
        return $this->checkboxRadioFieldWrapper($val, 'radio');
    }

    /**
     * This function is used to add Select field on MPE form.
     *
     * @param array $val Array to create field
     */
    public function addSelectField($val)
    {
        $temp = '<div class="mpe_form_input">';
        $temp .= '<label class="mpe-left wdm-enquiry-form-label">';
        $temp .= $val[ 'label' ].(($val[ 'required' ] == 'yes') ? '<sup class="req">*</sup>' : '');
        $temp .= '</label>
                            <div class="mpe-right"><div class="mpe-right-inner">';
        $temp .= '<select';
        if (isset($val[ 'id' ])) {
            $temp .= " name='".$val[ 'id' ]."'";
            $temp .= " id='".$val[ 'id' ]."'";
        }
        if (isset($val[ 'required' ])) {
            $temp .= ' '.(($val[ 'required' ] == 'yes') ? 'required' : '');
        }

        $temp .= $this->addClassToField($val);
        $temp .= ' >';

        $temp .= $this->getOptions($val);

        $temp .= '</select>';

        return $temp.'</div></div></div>';
    }

    /**
     * This function is used to add Multiple field on MPE form.
     *
     * @param array $val Array to create field
     */
    public function addMultipleField($val)
    {
        $this->enqueueMultiSelectScript();

        $temp = '<div class="mpe_form_input ';
        if (isset($val[ 'class' ])) {
            $temp .= $val[ 'class' ];
        }
        $temp .= '">';
        $temp .= '<label class="mpe-left wdm-enquiry-form-label">';
        $temp .= $val[ 'label' ].(($val[ 'required' ] == 'yes') ? '<sup class="req">*</sup>' : '');
        $temp .= '</label>
                            <div class="mpe-right"><div class="mpe-right-inner">';
        $temp .= '<select class="wdm-custom-multiple-fields" ';
        if (isset($val[ 'id' ])) {
            $temp .= " name='".$val[ 'id' ]."'";
            $temp .= " id='".$val[ 'id' ]."'";
        }
        $temp .= ' multiple>';
        if (count($val[ 'options' ]) > 0) {
            foreach ($val[ 'options' ] as $key => $value) {
                if (isset($value)) {
                    $temp .= "<option value='".$value."'>".$value.'</option>';
                }
                unset($key);
            }
        }
        $temp .= '</select>';

        return $temp.'</div></div></div>';
    }

    /**
     * This function is used to add Text field on MPE form.
     *
     * @param array $val Array to create field
     */
    public function addFileField($val)
    {
        $temp = '<div class="mpe_form_input">';
        $temp .= '<label class="mpe-left wdm-enquiry-form-label">';
        $temp .= $val[ 'label' ].(($val[ 'required' ] == 'yes') ? '<sup class="req">*</sup>' : '');
        $temp .= '</label>
                            <div class="mpe-right"><div class="mpe-right-inner">';
        $temp .= "<input type='file'";
        if (isset($val[ 'id' ])) {
            $temp .= " name='".$val[ 'id' ]."'";
        }
        $temp .= " id='".$val[ 'id' ]."'";
        if (isset($val[ 'required' ])) {
            $temp .= ' '.(($val[ 'required' ] == 'yes') ? 'required' : '');
        }

        $temp .= $this->addClassToField($val);
        $temp .= $this->addValueToField($val);
        $temp .= ' multiple >';

        return $temp.'</div></div></div>';
    }

    /**
     * End of functions adding fields on MPE form
     *
     * @param array $val Array to create field
     */

    private function addClassToField($val)
    {
        $temp = '';
        if (isset($val[ 'class' ])) {
            $temp = " class='".$val[ 'class' ]."'";
        }

        return $temp;
    }

    /**
     * Adds value to custom field
     *
     * @param array $val Array to create field
     */
    private function addValueToField($val)
    {
        $temp = '';
        if (isset($val[ 'value' ]) && $val[ 'value' ] != '') {
                $temp = " value='".$val[ 'value' ]."'";
        }

        return $temp;
    }

    /**
     * This function is used to get options of select field in mpe form.
     *
     * @param array $val Array to create field
     *
     * @return [string html] $temp template for options
     */
    private function getOptions($val)
    {
        $temp = '';
        if (count($val[ 'options' ]) > 0) {
            if (isset($val['default_text'])) {
                $temp .= "<option value='#'>".$val['default_text'].'</option>';
            }
            foreach ($val[ 'options' ] as $key => $value) {
                if (isset($value)) {
                    $temp .= "<option value='".$value."'>".$value.'</option>';
                }
                unset($key);
            }
        }

        return $temp;
    }

    /**
     * This function is used to get name and id of MPE from checkbox field.
     *
     * @param array $val Value to create field
     *
     * @return string HTML string
     */
    public function getNameID($val)
    {
        $temp = '';
        if (isset($val[ 'id' ])) {
            $temp = " name='".$val[ 'id' ]."[]'";
        }
        if (isset($val[ 'id' ])) {
            $temp .= " id='".$val[ 'id' ]."'";
        }

        return $temp;
    }

    //adding custom fields to enquiry_meta table
    /**
     * This function is used to add default and custom fields if any in enquiry meta table.
     *
     * @param int $enquiryID Enquiry ID
     */
    public function addCustomFieldsDb($enquiryID, $data)
    {
        global $wpdb;
        $tbl = getEnquiryMetaTable();
        $settings = quoteupSettings();

        if ('custom' != $settings['enquiry_form']) {
            foreach ($this->fields as $key => $v) {
                switch ($v[ 'id' ]) {
                    case 'custname':
                    case 'txtemail':
                    case 'txtphone':
                    case 'txtsubject':
                    case 'txtmsg':
                    case 'txtdate':
                    case 'wdmFileUpload':
                        break;
                    
                    default:
                        if (isset($_POST['globalEnquiryID']) && $_POST['globalEnquiryID'] != 0) {
                            $wpdb->update(
                                $tbl,
                                array(
                                'meta_value' => $this->getDefaultFormMetaValue($v),
                                ),
                                array(
                                'enquiry_id' => $_POST['globalEnquiryID'],
                                'meta_key' => stripcslashes($v['label']),
                                ),
                                array(
                                '%s',
                                ),
                                array('%d', '%s')
                            );
                        } else {
                            $wpdb->insert(
                                $tbl,
                                array(
                                'enquiry_id' => $enquiryID,
                                'meta_key' => stripcslashes($v['label']),
                                'meta_value' => $this->getDefaultFormMetaValue($v),
                                ),
                                array(
                                '%d',
                                '%s',
                                '%s',
                                )
                            );
                        }
                        break;
                }
                unset($key);
            }
        } else {
            foreach ($data as $key => $value) {
                switch ($key) {
                    case 'custname':
                    case 'txtemail':
                    case 'wdmLocale':
                    case 'product_quant':
                    case 'submit_value':
                    case 'product_name':
                    case 'product_type':
                    case 'variation':
                    case 'product_id':
                    case 'uemail':
                    case 'product_img':
                    case 'product_price':
                    case 'product_url':
                    case 'site_url':
                    case 'variation_id':
                    case 'variation_detail':
                    case 'action':
                    case 'quoteProductsData':
                    case 'show-price':
                    case 'security':
                    case 'globalEnquiryID':
                    case '__iswisdmform':
                    case '_wp_http_referer':
                    case 'form_id':
                    case 'quote_ajax_nonce':
                    case 'tried':
                    case 'expiration-date':
                    case 'undefined':
                    case 'language':
                    case 'cc':
                        break;
                    
                    default:
                        if (isset($_POST['globalEnquiryID']) && $_POST['globalEnquiryID'] != 0) {
                            $key = str_replace("_", " ", $key);
                            $wpdb->update(
                                $tbl,
                                array(
                                'meta_value' => $this->getCustomFormMetaValue($value),
                                ),
                                array(
                                'enquiry_id' => $_POST['globalEnquiryID'],
                                'meta_key' => stripcslashes($key),
                                ),
                                array(
                                '%s',
                                ),
                                array('%d', '%s')
                            );
                        } else {
                            $key = str_replace("_", " ", $key);
                            $wpdb->insert(
                                $tbl,
                                array(
                                'enquiry_id' => $enquiryID,
                                'meta_key' => stripcslashes($key),
                                'meta_value' => $this->getCustomFormMetaValue($value),
                                ),
                                array(
                                '%d',
                                '%s',
                                '%s',
                                )
                            );
                        }
                        break;
                }
            }
        }
    }

    public function getDefaultFormMetaValue($v)
    {
        return (isset($_POST[$v['id']]) ? stripcslashes($_POST[$v['id']]) : '');
    }

    public function getCustomFormMetaValue($value)
    {
        return (isset($value) ? stripcslashes($value) : '');
    }

    /**
     * This function is used to get customer name and email field array.
     *
     * @param [string] $name  [Name of the customer]
     * @param [string] $email [Email of the customer]
     *
     * @return [array] [array of customer name and email field]
     */
    public function getCustNameEmail($name, $email)
    {
        return array(
            array(
                'id' => 'custname',
                'class' => 'wdm-modal_text',
                'type' => 'text',
                'placeholder' => __('Name', QUOTEUP_TEXT_DOMAIN),
                'required' => 'yes',
                'required_message' => __('Please Enter Name', QUOTEUP_TEXT_DOMAIN),
                'validation' => '^([^0-9@#$%^&*()+{}:;\//"<>,.?*~`]*)$', //^[a-zA-Z\u00C0-\u00ff\' ]+$
                'validation_message' => __('Please Enter Valid Name', QUOTEUP_TEXT_DOMAIN),
                'include_in_admin_mail' => 'yes',
                'include_in_customer_mail' => 'no',
                'include_in_quote_form' => 'yes',
                'label' => __('Customer Name', QUOTEUP_TEXT_DOMAIN),
                'value' => $name,
            ),
            array(
                'id' => 'txtemail',
                'class' => 'wdm-modal_text',
                'type' => 'text',
                'placeholder' => __('Email', QUOTEUP_TEXT_DOMAIN),
                'required' => 'yes',
                'required_message' => __('Please Enter Email', QUOTEUP_TEXT_DOMAIN),
                'validation' => '^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$',
                'validation_message' => __('Please Enter Valid Email Address', QUOTEUP_TEXT_DOMAIN),
                'include_in_admin_mail' => 'yes',
                'include_in_customer_mail' => 'no',
                'include_in_quote_form' => 'yes',
                'label' => __('Email', QUOTEUP_TEXT_DOMAIN),
                'value' => $email,
            ),
        );
    }

    /**
     * This function is used to get Telephone field on mpe form.
     *
     * @param [array] $form_data [settings stored in database]
     * @param [array] $custname  [previous fields array]
     *
     * @return [array] $custname [Customer name attributes]
     */
    public function getTelephoneField($form_data, $custname)
    {
        if (isset($form_data[ 'enable_telephone_no_txtbox' ]) && $form_data[ 'enable_telephone_no_txtbox' ] == '1') {
            $ph_req = 'no';

            $ph_req = $this->phoneMandatory($ph_req, $form_data);

            $custname = array_merge(
                $custname,
                array(
                array(
                    'id' => 'txtphone',
                    'class' => 'wdm-modal_text',
                    'type' => 'text',
                    'placeholder' => __('Phone Number', QUOTEUP_TEXT_DOMAIN),
                    'required' => $ph_req,
                    'required_message' => __('Please Enter Phone Number', QUOTEUP_TEXT_DOMAIN),
                    'validation' => '^[0-9(). \-+]{1,16}$',
                    'validation_message' => __('Please Enter Valid Telephone No', QUOTEUP_TEXT_DOMAIN),
                    'include_in_admin_mail' => 'yes',
                    'include_in_customer_mail' => 'no',
                    'include_in_quote_form' => 'yes',
                    'label' => __('Phone Number', QUOTEUP_TEXT_DOMAIN),
                    'value' => '',
                ),
                )
            );
        }

        return $custname;
    }

    /**
     * This function is used to get Telephone field on mpe form.
     *
     * @param [array] $form_data [settings stored in database]
     * @param [array] $custname  [previous fields array]
     *
     * @return [array] $custname [date attributes]
     */
    private function getDateField($form_data, $custname)
    {
        if (isset($form_data[ 'enable_date_field' ]) && $form_data[ 'enable_date_field' ] == '1') {
            enqueueDatePickerFiles();
            $dt_req = 'no';

            $dt_req = $this->dateMandatory($dt_req, $form_data);
            $label = isset($form_data[ 'date_field_label' ]) ? quoteupReturnWPMLVariableStrTranslation($form_data[ 'date_field_label' ]) : __('Date', QUOTEUP_TEXT_DOMAIN);

            if ($label == "") {
                $label = __('Date', QUOTEUP_TEXT_DOMAIN);
            }

            $custname = array_merge(
                $custname,
                array(
                array(
                    'id' => 'txtdate',
                    'class' => 'wdm-modal_text date-field',
                    'type' => 'text',
                    'placeholder' => $label,
                    'required' => $dt_req,
                    'required_message' => __('Please Enter Date', QUOTEUP_TEXT_DOMAIN),
                    'include_in_admin_mail' => 'yes',
                    'include_in_customer_mail' => 'yes',
                    'include_in_quote_form' => 'yes',
                    'label' => $label,
                    'value' => '',
                ),
                )
            );
        }

        return $custname;
    }

    /**
     * This function is used to add Quantity field on SPE form.
     *
     * @param array $form_data Settings stored in
     * @param array $custname  Previous Field array
     *
     * @return array Array with quantity field
     */
    public function getQuantityField($custname)
    {
        return array_merge(
            $custname,
            array(
                array(
                    'id' => 'txtQty',
                    'class' => 'wdm-modal_text quantity-field',
                    'type' => 'text',
                    'placeholder' => __('Product Quantity', QUOTEUP_TEXT_DOMAIN),
                    'required' => 'yes',
                    'required_message' => __('Please Enter Quantity', QUOTEUP_TEXT_DOMAIN),
                    'validation' => '^[0-9]*$',
                    'validation_message' => __('Please Enter Valid Quantity', QUOTEUP_TEXT_DOMAIN),
                    'include_in_admin_mail' => 'yes',
                    'include_in_customer_mail' => 'yes',
                    'include_in_quote_form' => 'yes',
                    'label' => __('Product Quantity', QUOTEUP_TEXT_DOMAIN),
                    'value' => '',
                ),
                )
        );
    }

    /**
     * This function is used to add upload File field in form.
     *
     * @param array $form_data Settings stored in database
     * @param array $custname  Previous Field array
     *
     * @return array Array with quantity field
     */
    public function getUploadField($form_data, $custname)
    {
        $attachReq = 'no';

        $attachReq = $this->attachMandatory($attachReq, $form_data);
        $label = isset($form_data[ 'attach_field_label' ]) ? quoteupReturnWPMLVariableStrTranslation($form_data['attach_field_label']) : __('Attach File', QUOTEUP_TEXT_DOMAIN);

        if ('' == $label) {
            $label = __('Attach File', QUOTEUP_TEXT_DOMAIN);
        }

        return array_merge(
            $custname,
            array(
                array(
                    'id' => 'wdmFileUpload',
                    'class' => 'wdm-modal_text upload-field',
                    'type' => 'file',
                    'placeholder' => __('Add File', QUOTEUP_TEXT_DOMAIN),
                    'required' => $attachReq,
                    'required_message' => __('Please Upload minimum 1 and maximum 10 Files', QUOTEUP_TEXT_DOMAIN),
                    'validation' => '',
                    'validation_message' => __('Please Upload Valid File', QUOTEUP_TEXT_DOMAIN),
                    'include_in_admin_mail' => 'yes',
                    'include_in_customer_mail' => 'yes',
                    'include_in_quote_form' => 'yes',
                    'label' => $label,
                    'value' => '',
                ),
                )
        );
    }

    /**
     * This function is used to add default and custom fields to the enquiry form.
     * Provides filters for the user to add any custom field according to his requirement.
     */
    public function createCustomFields()
    {
        $this->fields = array();
        $custname = array();
        $default_vals = array('after_add_cart' => 1,
            'button_CSS' => 0,
            'pos_radio' => 0,
            'show_powered_by_link' => 0,
            'enable_send_mail_copy' => 0,
            'enable_telephone_no_txtbox' => 0,
            'dialog_product_color' => '#3079ED',
            'dialog_text_color' => '#000000',
            'dialog_color' => '#F7F7F7',
        );
        $form_data = get_option('wdm_form_data', $default_vals);

        $userData = $this->getUsernameEmail();

        if (is_admin()) {
            $custname = $this->getCustNameEmail('', '');
        } else {
            $custname = $this->getCustNameEmail($userData['name'], $userData['email']);
        }
        $custname = $this->getTelephoneField($form_data, $custname);
        $custname = $this->getDateField($form_data, $custname);

        if (isset($form_data['enable_disable_mpe']) && $form_data['enable_disable_mpe'] != 1 && !is_admin()) {
            $custname = $this->getQuantityField($custname);
        }


        if (isset($form_data['enable_attach_field']) && $form_data['enable_attach_field'] == 1) {
            $custname = $this->getUploadField($form_data, $custname);
        }

        $custname = array_merge(
            $custname,
            array(
            array(
                'id' => 'txtsubject',
                'class' => 'wdm-modal_text',
                'type' => 'text',
                'placeholder' => __('Subject', QUOTEUP_TEXT_DOMAIN),
                'required' => 'no',
                'required_message' => __('Please Enter Subject', QUOTEUP_TEXT_DOMAIN),
                'validation' => '',
                'validation_message' => __('Please Enter Valid Subject', QUOTEUP_TEXT_DOMAIN),
                'include_in_admin_mail' => 'yes',
                'include_in_customer_mail' => 'no',
                'include_in_quote_form' => 'yes',
                'label' => __('Subject', QUOTEUP_TEXT_DOMAIN),
                'value' => '',
            ),
            )
        );

        $custname = array_merge(
            $custname,
            array(
            array(
                'id' => 'txtmsg',
                'class' => 'wdm-modal_textarea',
                'type' => 'textarea',
                'placeholder' => __('Message', QUOTEUP_TEXT_DOMAIN),
                'required' => 'yes',
                'required_message' => __('Please Enter Message', QUOTEUP_TEXT_DOMAIN),
                'validation' => '',
                'validation_message' => __('Message length must be between 15 to 500 characters', QUOTEUP_TEXT_DOMAIN),
                'include_in_admin_mail' => 'yes',
                'include_in_customer_mail' => 'yes',
                'include_in_quote_form' => 'yes',
                'label' => __('Message', QUOTEUP_TEXT_DOMAIN),
                'value' => '',
            ),
            )
        );

        foreach ($custname as $single_custname) {
            $single_custname = apply_filters('pep_fields_'.$single_custname[ 'id' ], $single_custname);

            if (isset($single_custname[ 'id' ])) {
                $single_custname = apply_filters('quoteup_fields_'.$single_custname[ 'id' ], $single_custname);
            }

            if (!empty($single_custname)) {
                if (isset($single_custname[ 'id' ])) {
                    $this->fields[] = $single_custname;
                } else {
                    $this->fields = array_merge($this->fields, $this->addFieldsRecursively($single_custname));

                    unset($this->temp_fields);
                }
            }
        }
    }

    public function getUsernameEmail()
    {
        $name = $email = '';
        if (is_user_logged_in()) {
            global $current_user;
            wp_get_current_user();
            $email = $current_user->user_email;
            $name = $current_user->user_firstname.' '.$current_user->user_lastname;
            if ($name == ' ') {
                $name = $current_user->user_login;
            }
        } else {
            if (isset($_COOKIE[ 'wdmusername' ])) {
                $name = filter_var($_COOKIE[ 'wdmusername' ], FILTER_SANITIZE_STRING);
            }
            if (isset($_COOKIE[ 'wdmuseremail' ])) {
                $email = filter_var($_COOKIE[ 'wdmuseremail' ], FILTER_SANITIZE_EMAIL);
            }
        }

        return array(
            'name'=>$name,
            'email' => $email
            );
    }

    /**
     * Checks if phone field mandatory
     *
     * @param  string $ph_req    phone request
     * @param  array  $form_data Settings stored in database
     * @return string $ph_req phone request
     */
    public function phoneMandatory($ph_req, $form_data)
    {
        if (isset($form_data[ 'make_phone_mandatory' ])) {
            $phone_mandate = $form_data[ 'make_phone_mandatory' ];
            if ($phone_mandate == 1) {
                $ph_req = 'yes';
            }
        }

        return $ph_req;
    }
    /**
     * Checks if date field mandatory
     *
     * @param  string $dt_req    date request
     * @param  array  $form_data Settings stored in database
     * @return string $dt_req date request
     */
    public function dateMandatory($dt_req, $form_data)
    {
        if (isset($form_data[ 'make_date_mandatory' ])) {
            $phone_mandate = $form_data[ 'make_date_mandatory' ];
            if ($phone_mandate == 1) {
                $dt_req = 'yes';
            }
        }

        return $dt_req;
    }

    /**
     * Checks if attach field mandatory
     *
     * @param  string $attachReq date request
     * @param  array  $form_data Settings stored in database
     * @return string $attachReq date request
     */
    public function attachMandatory($attachReq, $form_data)
    {
        if (isset($form_data[ 'make_attach_mandatory' ])) {
            $attach_mandate = $form_data[ 'make_attach_mandatory' ];
            if ($attach_mandate == 1) {
                $attachReq = 'yes';
            }
        }

        return $attachReq;
    }

    //get custom fields array
    public function getCustomFields()
    {
        return $this->fields;
    }

    /**
     * Adds fields recursively for the single customer enquiry
     *
     * @param  array $single_custname Fields for enquiry form
     * @return array Fields for enquiry form
     */
    public function addFieldsRecursively($single_custname)
    {
        foreach ($single_custname as $single_array) {
            if (is_array($single_array)) {
                if (isset($single_array[ 'id' ])) {
                    $this->temp_fields[] = $single_array;
                } else {
                    $this->addFieldsRecursively($single_array);
                }
            } else {
                return $this->temp_fields;
            }
        }

        return $this->temp_fields;
    }

    /**
     * Gets the customer name Id
     *
     * @param  array $val  Value to create field
     * @param  array $data Enquiry details
     * @return string $email HTMl for cust name
     */
    public function custnameID($val, $data)
    {
        $email = '';
        if ($val[ 'id' ] == 'custname' && $val[ 'include_in_admin_mail' ] == 'yes') {
            $email = "
           <tr >
            <th style='width:35%;text-align:left'>".__('Customer Name', QUOTEUP_TEXT_DOMAIN)." </th>
                <td style='width:65%'>: ".stripslashes($data[ $val[ 'id' ] ]).'</td>
           </tr>';
        }

        return $email;
    }


    /**
     * Gets the html template and fields included in mails
     *
     * @param  array $val  value for create field
     * @param  array $data Enquiry details
     * @return string HTML for other fields in email
     */
    private function getOtherFields($val, $data)
    {
        $email = '';
        switch ($val[ 'id' ]) {
            case 'custname':
            case 'txtemail':
            case 'txtphone':
            case 'txtsubject':
            case 'txtmsg':
            case 'txtdate':
            case 'wdmFileUpload':
                break;
            
            default:
                if ($val[ 'include_in_admin_mail' ] == 'yes') {
                    $email .= "
                <tr >
                <th style='width:35%;text-align:left'>".__($val[ 'label' ], QUOTEUP_TEXT_DOMAIN)."</th>
                <td style='width:65%'>: ".stripslashes($data[ $val[ 'id' ] ]).'</td>
               </tr>';
                }
                break;
        }

        unset($source);
        return $email;
    }

    /**
     * Gets the custom field to be included in admin email
     *
     * @param  array $val  value for create field
     * @param  array $data Enquiry details
     * @return string html html email template
     */
    public function forEachFieldAdminEmail($val, $data)
    {
        $email = '';
        $email .= $this->custnameID($val, $data);

        if ($val[ 'id' ] == 'txtemail' && $val[ 'include_in_admin_mail' ] == 'yes') {
            $email .= "
           <tr >
            <th style='width:35%;text-align:left'>".__('Customer Email', QUOTEUP_TEXT_DOMAIN)." </th>
                <td style='width:65%'>: ".stripslashes($data[ $val[ 'id' ] ]).'</td>
           </tr>';
        }
        if ($val[ 'id' ] == 'txtphone' && $val[ 'include_in_admin_mail' ] == 'yes') {
            $email .= "
           <tr >
            <th style='width:35%;text-align:left'>".__('Telephone', QUOTEUP_TEXT_DOMAIN)." </th>
                <td style='width:65%'>: ".stripslashes($data[ $val[ 'id' ] ]).'</td>
           </tr>';
        }
        if ($val[ 'id' ] == 'txtmsg' && $val[ 'include_in_admin_mail' ] == 'yes') {
            $email .= "
           <tr >
            <th style='width:35%;text-align:left'>".__('Message', QUOTEUP_TEXT_DOMAIN)." </th>
                <td style='width:65%'>: ".stripslashes($data[ $val[ 'id' ] ]).'</td>
           </tr>';
        }
        if ($val[ 'id' ] == 'txtdate' && $val[ 'include_in_admin_mail' ] == 'yes') {
            $email .= "
           <tr >
            <th style='width:35%;text-align:left'>".__($val[ 'label' ], QUOTEUP_TEXT_DOMAIN)." </th>
                <td style='width:65%'>: ".stripslashes($data[ $val[ 'id' ] ]).'</td>
           </tr>';
        }
        return $email.$this->getOtherFields($val, $data);
    }

    /**
     * Add custom field in admin email
     *
     * @param  string $email_content empty at first
     * @param  array  $data          Enquiry details
     * @return string email fields
     */
    public function addCustomFieldsAdminEmail($data)
    {
        $email = '';
        foreach ($this->fields as $key => $v) {
            $email .= $this->forEachFieldAdminEmail($v, $data);
            unset($key);
        }

        echo $email;
    }

    // fetching meta fields header for data table
    public function quoteupCustomFieldsHeader()
    {
        global $wpdb;
        $tbl = getEnquiryMetaTable();
        $header = '';
        $sql = 'SELECT distinct meta_key FROM '.$tbl;
        $results = $wpdb->get_results($sql);
        if (count($results) > 0) {
            foreach ($results as $key => $v) {
                $this->meta_key[] = $v->meta_key;
                $header .= apply_filters('pep_meta_key_header_in_table', "<th class='td_norm'>".$v->meta_key.'</th>', $v->meta_key);
                $header = apply_filters('quoteup_meta_key_header_in_table', $header, $v->meta_key);
                unset($key);
            }
        }
        echo $header;
    }

    /**
     * Display the custom fields in customer data section on enquiry edit
     * page.
     *
     * @param int $enquiry_id ID of enquiry.
     */
    public function displayCustomFieldsDataEnquiryEdit($enquiry_id)
    {
        global $wpdb;
        $tbl = getEnquiryMetaTable();
        $custom_field_data = '';
        $sql = "SELECT meta_key,meta_value FROM {$tbl} WHERE enquiry_id='$enquiry_id'";
        $sql = apply_filters('quoteup_customer_meta_data_query', $sql, $enquiry_id);
        $results = $wpdb->get_results($sql);
        if (count($results) <= 0) {
            return;
        }
        
        foreach ($results as $key => $v) {
            if ('Product Quantity' == $v->meta_key || 'quotation_lang_code' == $v->meta_key || 'mc-subscription-checkbox' == $v->meta_key || '_'  === substr($v->meta_key, 0, 1)) {
                continue;
            }

            if ($v->meta_key == 'enquiry_lang_code') {
                if (quoteupIsWpmlActive()) {
                    $currentLanguageName = icl_get_languages('skipmissing=0');
                    $currentLanguageName = isset($currentLanguageName[$v->meta_value]['translated_name']) ? $currentLanguageName[$v->meta_value]['translated_name'] : $currentLanguageName[$v->meta_value]['native_name'];

                    $v->meta_key = __('Enquiry Language', QUOTEUP_TEXT_DOMAIN);
                    $v->meta_value = $currentLanguageName;
                } else {
                    continue;
                }
            }

            $this->meta_key[] = $v->meta_key;
            $meta_key_name = apply_filters('pep_meta_key_header_in_table', $v->meta_key);
            $meta_key_name = apply_filters('quoteup_meta_key_header_in_table', $meta_key_name);
            $custom_field_data .= "<div class='wdm-user-custom-info' title='" . esc_attr($v->meta_value)."'>";
            $custom_field_data .= "<textarea type='text' class='wdm-input-custom-info wdm-input' disabled>" . esc_textarea($v->meta_value) . "</textarea>";
            $custom_field_data .= '<label placeholder="'.esc_attr(__($meta_key_name, QUOTEUP_TEXT_DOMAIN)).'" alt="'.esc_attr($meta_key_name).'"></label></div>';
            unset($key);
        }

        echo $custom_field_data;
    }

    /**
     * Find a value associated with meta key of particular enquiry
     *
     * @param  int    $enquiry_id ID of enquiry
     * @param  string $meta_key   Meta Key whose value to be found
     * @return mixed If value is found, it is returned. Else NULL is returned.
     */
    public static function quoteupGetCustomFieldData($enquiry_id, $meta_key)
    {
        global $wpdb;
        $tbl = getEnquiryMetaTable();
        return $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$tbl} WHERE meta_key LIKE %s AND enquiry_id = %d", $meta_key, $enquiry_id));
    }

    /**
     * fetching meta fields data for data table
     *
     * @param int $enquiryID Enquiry Id
     */
    public function quoteupCustomFieldsData($enquiryID)
    {
        global $wpdb;
        $tbl = getEnquiryMetaTable();
        $data = '';
        $term = $this->meta_key;
        $temp = '';
        if (count($term) <= 0) {
            return;
        }

        foreach ($term as $key => $v) {
            if ($key == 0) {
                $temp_array[] = $v;
                $temp = 'SELECT MAX(IF(meta_key = %s, meta_value, NULL)) as %s';
                $temp_array[] = $v;
            } else {
                $temp_array[] = $v;
                $temp .= ',MAX(IF(meta_key = %s, meta_value, NULL)) as %s';
                $temp_array[] = $v;
            }
        }
        $temp .= " FROM {$tbl} WHERE enquiry_id = %d";
        $temp_array[] = $enquiryID;

        $result = $wpdb->get_results($wpdb->prepare($temp, $temp_array));
        if (!isset($result[ 0 ])) {
            return;
        }

        foreach ($result[ 0 ] as $key => $v) {
            $current_meta_key = $key;
            $data .= apply_filters('pep_meta_key_data_in_table', "<td class='enq_td td_norm'>".((isset($v)) ? $v : '').'</td>', $current_meta_key);
            $data = apply_filters('quoteup_meta_key_data_in_table', $data, $current_meta_key);
        }

        echo $data;
    }

    /**
     * Deleting meta fields data.
     *
     * @param int $enquiryID Enquiry Id
     */
    public function deleteCustomFields($enquiryID)
    {
        global $wpdb;
        $tbl = getEnquiryMetaTable();
        $query = 'DELETE FROM '.$tbl." WHERE enquiry_id='".$enquiryID."'";
        $wpdb->query($query);
    }

    /**
     * Adds the custom fields in customer email.
     *
     * @return string html table format for fields to be included in email
     */
    public function addCustomFieldsCustomerEmail($dataObtainedFromForm, $source)
    {
        $email = '';
        foreach ($this->fields as $key => $v) {
            if ($v[ 'id' ] == "wdmFileUpload") {
                continue;
            }
            $inlcudeField = $v[ 'include_in_customer_mail' ] == 'yes' ? true : false;
            $inlcudeField = apply_filters('quoteup_include_field_in_cust_email_df', $inlcudeField, $v, $source);
            if ($inlcudeField) {
                $email .= "
           <tr >
            <th style='width:35%;text-align:left'>".__($v[ 'label' ], QUOTEUP_TEXT_DOMAIN)." </th>
                <td style='width:65%'>: ".stripslashes($_POST[ $v[ 'id' ] ]).'</td>
           </tr>';
            }
            unset($key);
        }
        unset($dataObtainedFromForm);
        echo $email;
    }
}

QuoteUpAddCustomField::getInstance();

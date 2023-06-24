<?php

function set_commonfields() {
    $fields = array();
    $fields['name'] = array('type' => 'text', 'label' => 'Name');
    $fields['email'] = array('type' => 'email', 'label' => 'Email', 'template' => 'email');
    $fields['subject'] = array('type' => 'text', 'label' => 'Subject');
    $fields['message'] = array('type' => 'textarea', 'label' => 'Message');

    $fields = array(
        'Wisdmforms\Name', 'Wisdmforms\Email', 'Wisdmforms\Subject', 'Wisdmforms\Message'
    );

    return $fields;
}

add_filter("common_fields", "set_commonfields");

function set_genericfields() {
    // $generic_fields = array();
    // $generic_fields = array(
    //     'text' => array(
    //         'type' => 'text',
    //         'label' => 'Text'
    //     ),
    //     'password' => array(
    //         'type' => 'password',
    //         'label' => 'Password'
    //     ),
    //     'radio' => array(
    //         'type' => 'radio',
    //         'label' => 'Radio',
    //         'options' => true
    //     ),
    //     'checkbox' => array(
    //         'type' => 'checkbox',
    //         'label' => 'Checkbox',
    //         'options' => true
    //     ),
    //     'select' => array(
    //         'type' => 'select',
    //         'label' => 'Select',
    //         'options' => true
    //     ),
    //     'textarea' => array(
    //         'type' => 'textarea',
    //         'label' => 'Textarea'
    //     )
    // );
    return array('Wisdmforms\Text', 'Wisdmforms\Textarea', 'Wisdmforms\Number', 'Wisdmforms\Radio', 'Wisdmforms\Select', 'Wisdmforms\Checkbox');
    // removed fields 'Password'
    // return $generic_fields;
}

add_filter("generic_fields", "set_genericfields");

function set_advancedfields() {
    // $advanced_fields = array();
    // $advanced_fields = array(
    //     'file' => array(
    //         'type' => 'file',
    //         'label' => 'File Upload',
    //         'template' => 'file'
    //     ),
    //     'captcha' => array(
    //         'type' => 'captcha',
    //         'label' => 'Captcha',
    //         'template' => 'captcha'
    //     ),
    //     'fullname' => array(
    //         'type' => 'fullname',
    //         'label' => 'Full name'
    //     ),
    //     'address' => array(
    //         'type' => 'address',
    //         'label' => 'Address'
    //     ),
    //     'pageseparator' => array(
    //         'type' => 'pageseparator',
    //         'label' => 'Page Separator',
    //         'template' => 'separator'
    //     ),
    //     'paymentmethods' => array(
    //         'type' => 'paymentmethods',
    //         'label' => 'Payment methods',
    //         'template' => 'paymentmethods'
    //     ),
    //     'url' => array(
    //         'type' => 'url',
    //         'label' => 'Website',
    //         'template' => 'url'
    //     ),
    //     'date' => array(
    //         'type' => 'date',
    //         'label' => 'Date',
    //         'template' => 'date'
    //     ),
    //     'daterange' => array(
    //         'type' => 'daterange',
    //         'label' => 'Date range',
    //         'template' => 'daterange'
    //     ),
    //     'location' => array(
    //         'type' => 'location',
    //         'label' => 'Location',
    //         'template' => 'location'
    //     ),
    //     'phone' => array(
    //         'type' => 'phone',
    //         'label' => 'Phone',
    //         'template' => 'phone'
    //     ),
    //     'paragraph_text' => array(
    //         'type' => 'paragraph_text',
    //         'label' => 'Paragraph text',
    //         'template' => 'paratext'
    //     ),
    //     'rating' => array(
    //         'type' => 'rating',
    //         'label' => 'Rating',
    //         'template' => 'rating'
    //     )
    // );
    return array('Wisdmforms\File', 'Wisdmforms\Captcha', 'Wisdmforms\FullName', 'Wisdmforms\Rating', 'Wisdmforms\Url', 'Wisdmforms\Paratext', 'Wisdmforms\Phone', 'Wisdmforms\Date', 'Wisdmforms\Daterange');
    //removed fields 'Location', 'Address', 'Pageseparator', 'PaymentMethods'
    // return $advanced_fields;
}

add_filter("advanced_fields", "set_advancedfields");

function set_methods() {
    global $methods_set;
    return $methods_set;
}

add_filter("method_set", "set_methods");

function get_validation_ops($type = '') {
    $optional_validation_ops = array(
        'url' => array('url' => __('URL', QUOTEUP_TEXT_DOMAIN)),
        'email' => array('email' => __('Email', QUOTEUP_TEXT_DOMAIN)),
        'date' => array('date' => __('Date', QUOTEUP_TEXT_DOMAIN)),
        'text' => array('text' => __('Text', QUOTEUP_TEXT_DOMAIN)),
        'number' => array('number' => __('Number', QUOTEUP_TEXT_DOMAIN))
    );

    $default_validation_ops = array(
        'text' => __('Text', QUOTEUP_TEXT_DOMAIN),
        'number' => __('Number', QUOTEUP_TEXT_DOMAIN),
        'email' => __('Email', QUOTEUP_TEXT_DOMAIN),
        'url' => __('URL', QUOTEUP_TEXT_DOMAIN),
        'date' => __('Date', QUOTEUP_TEXT_DOMAIN)
        );
    if ($type == '') {
        $validation_ops = $default_validation_ops;
    } else {
        $validation_ops = $optional_validation_ops[$type];
    }

    return $validation_ops;
}

function currencies() {
    $currencies_list = array(
        'USD' => __('Dollar', QUOTEUP_TEXT_DOMAIN),
        'EUR'    => __('Euro', QUOTEUP_TEXT_DOMAIN)
    );
    return $currencies_list;
}
?>
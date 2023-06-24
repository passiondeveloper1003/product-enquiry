<?php
/**
 * enquiry-form
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/enquiry-modal/default-form/enquiry-form.php.
 *
 * HOWEVER, on occasion QuoteUp will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author  WisdmLabs
 * @version 6.1.0
 */
?>
<form method='post' id='frm_enquiry' name='frm_enquiry' class="wdm-quoteup-form wdm-spe-form wdm-default-form" enctype="multipart/form-data" >
    <?php
    $ajax_nonce = wp_create_nonce('nonce_for_enquiry');
    $currentLocale = get_locale();
    $arr = explode("_", $currentLocale, 2);
    $currentLocale = $arr[0];
    if (quoteupIsWpmlActive()) {
        global $sitepress;
        $currentLocale = $sitepress->get_current_language();
    }
    ?>
    <input type='hidden' name='ajax_nonce' id='ajax_nonce' value='<?php echo $ajax_nonce ?>'>
    <input type='hidden' name='wdmLocale' id='wdmLocale' value='<?php echo $currentLocale ?>'>
    <input type='hidden' name='submit_value' id='submit_value'>
    <input type='hidden' name="product_name_<?php echo $prod_id ?>" id="product_name_<?php echo $prod_id ?>" value='<?php echo get_the_title() ?>'>
    <input type='hidden' name="product_type_<?php echo $prod_id; ?>" id="product_type_<?php echo $prod_id; ?>">
    <input type='hidden' name="variation_<?php echo $prod_id; ?>" id="variation_<?php echo $prod_id; ?>">
    <input type='hidden' name='product_id_<?php echo $prod_id ?>' id='product_id_<?php echo $prod_id ?>' value='<?php echo $prod_id ?>'>
    <input type='hidden' name='author_email' id='author_email' value='<?php echo $uemail ?>'>
    <input type='hidden' name='product_img_<?php echo $prod_id ?>' id='product_img_<?php echo $prod_id ?>' value='<?php echo $img_url ?>'>
    <input type='hidden' name='product_price_<?php echo $prod_id ?>' id='product_price_<?php echo $prod_id ?>' value='<?php echo $price ?>'>
    <input type='hidden' name='product_url_<?php echo $prod_id ?>' id='product_url_<?php echo $prod_id ?>' value='<?php echo $url ?>'>
    <input type='hidden' name='site_url' id='site_url' value='<?php echo admin_url() ?>'>
    <input type="hidden" name="tried" id="tried" value="yes" />
    <?php
    /**
     * Add hidden form fields in the default form.
     *
     * @param  int  $product_id  Current product Id.
     */
    do_action('quoteup_add_hidden_fields_in_form', $product_id);
    do_action_deprecated('pep_add_hidden_fields_in_form', array($product_id), '6.5.0');
    ?>
    <div id="error" class="error" >
    </div>
    <div id="nonce_error" style="text-align: center; background-color: #f2dede; ">
        <div  class='wdmquoteup-err-display' style='background-color:transparent;'>
            <span class="wdm-quoteupicon wdm-quoteupicon-exclamation-circle"></span><?php _e('Unauthorized enquiry', QUOTEUP_TEXT_DOMAIN) ?>
        </div>
    </div>
    <div class="wdm-quoteup-form-inner">
        <?php
        /**
         * Add form fields in the default form.
         *
         * @param  int  $product_id  Current product Id.
         */
        do_action('quoteup_add_custom_field_in_form', $product_id);
        do_action('pep_add_custom_field_in_form');
        quoteupGetPublicTemplatePart('enquiry-modal/default-form/captcha', '', $args);
        $enable_mc = '';
        if (isset($form_data[ 'enable_send_mail_copy' ]) && 1 == $form_data[ 'enable_send_mail_copy' ]) {
            $enable_mc = $form_data[ 'enable_send_mail_copy' ];
            quoteupGetPublicTemplatePart('enquiry-modal/default-form/send-me-a-copy', '', $args);
        }
        /**
         * After rendering all form fields in the default form.
         *
         * @param  array  $args  Array containing data required to fill in the
         *                       modal.
         */
        do_action('quoteup_after_all_form_fields', $args);
        ?>
    </div>
    <div class="form_input btn_div wdm-enquiryform-btn-wrap wdm-quoteupform-btn-wrap">
        <div class='form-wrap'>
            <input type='submit' value='<?php _e('Send', QUOTEUP_TEXT_DOMAIN); ?>' name="btnSend"  id="btnSend_<?php echo $prod_id ?>" class="button_example" <?php echo ($manual_css == 1) ? getManualCSS($form_data) : ''; ?>>
            <div class="wdmquoteup-loader" style='display: none'>
                <?php $url = QUOTEUP_PLUGIN_URL.'/images/loading.gif'; ?>
                <img src='<?php echo $url ?>' >
            </div>
        </div>
    </div>
    <div class='form-errors-wrap single-enquiry-form'>
        <div class="form-errors">
            <ul class="error-list">
            </ul>
        </div>
    </div>
</form>

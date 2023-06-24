<?php
/**
 * send button
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/enquiry-cart/send-button.php.
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
<div class='form_input btn_div wdm-enquiryform-btn-wrap clearfix'>
    <div class='mpe-left' style='height: 1px;'>
    </div>
    <div class='mpe-right'>
        <input type='submit' value='<?php _e('Send', QUOTEUP_TEXT_DOMAIN); ?>' name='btnSend' id='btnMPESend' class='button_example' <?php echo $manualCss; ?> >
        <span class='load-send-quote-ajax'>
        </span>
    </div>
</div>
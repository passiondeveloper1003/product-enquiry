<?php
/**
 * default enquiry form
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/enquiry-cart/default-enquiry-form.php.
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
<div class='wdm-enquiry-form'>
    <?php
    
    /**
     * quoteup_before_form_enquiry_cart hook.
     *
     * @hooked displayTitle - 10 (outputs form title)
     */
    do_action('quoteup_before_form_enquiry_cart', $args);
    
    ?>
    <form method='post' id='frm_mpe_enquiry' name='frm_enquiry' class='wdm-quoteup-form wdm-mpe-form wdm-default-form' >
        <?php

        /**
         * quoteup_before_fields_enquiry_cart hook.
         *
         * @hooked renderHiddenFields - 10 (outputs hidden fields)
         * @hooked renderNonceErrorDiv - 15 (outputs nonce error div)
         */
        do_action('quoteup_before_fields_enquiry_cart', $args);

        do_action('mpe_add_custom_field_in_form');

        /**
         * quoteup_after_fields_in_form hook.
         *
         * @hooked displaySendMeCopy - 5 (outputs send me a copy checkbox)
         * @hooked displayCaptcha - 10 (outputs captcha field)
         * @hooked displaysendButton - 15 (outputs send button)
         * @hooked displayErrorDiv - 20 (outputs error div)
         */
        do_action('quoteup_after_fields_in_form', $args);

        ?>
    </form>
</div>
<!-- </div> closing extra div of quoteup-quote-cart -->

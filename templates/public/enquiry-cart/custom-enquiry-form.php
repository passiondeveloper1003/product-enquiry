<?php
/**
 * custom enquiry form.
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/enquiry-cart/custom-enquiry-form.php.
 *
 * HOWEVER, on occasion QuoteUp will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author  WisdmLabs
 *
 * @version 6.1.0
 */
$formID = $form_data['custom_formId'];
if (isset($formID) && !empty($formID)) {
    ?>
    <div class='wdm-enquiry-form'>
        <?php
        /**
         * quoteup_before_form_enquiry_cart hook.
         *
         * @hooked displayTitle - 10 (outputs form title)
         */
        do_action('quoteup_before_form_enquiry_cart', $args);

        echo do_shortcode("[wisdmform form_id=$formID]");
    ?>
    </div>
    <?php
}

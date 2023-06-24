<?php
/**
 * Enquiry form body
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/enquiry-modal/enquiry-form-body.php.
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
<div class="wdm-modal-body">
    <?php
    if (!isset($form_data['enquiry_form']) || $form_data['enquiry_form'] == 'default' || $form_data['enquiry_form'] == '') {
        quoteupGetPublicTemplatePart('enquiry-modal/default-form/enquiry-form', '', $args);
    } else {
        quoteupGetPublicTemplatePart('enquiry-modal/custom-form/enquiry-form', '', $args);
        ?>
        <?php
    }
    /**
     * After form fields content in enquiry modal when SPE mode is active.
     *
     * @param  array  $args  Array containing data required to fill in the
     *                       enquiry modal.
     */
    do_action('quoteup_after_single_product_form', $args);
    ?>
</div>

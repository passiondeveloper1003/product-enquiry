<?php
/**
 * Enquiry form header
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/enquiry-modal/enquiry-form-header.php.
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
<div class="wdm-modal-header">
    <button type="button" class="close" data-dismiss="wdm-modal" aria-hidden="true">
        &times;
    </button>
    <h4 class="wdm-modal-title" id="myModalLabel" <?php echo $dialogue_text_color;?> >
        <span>
            <?php
            $sendEnquiryHeading = apply_filters('quoteup_send_enquiry_label', __('Send Enquiry for', QUOTEUP_TEXT_DOMAIN));
            echo $sendEnquiryHeading;
            ?>
        </span>
        <span class='pr_name' <?php echo $dialogue_product_color; ?>>
            <?php echo $title; ?>
        </span>
    </h4>
</div>

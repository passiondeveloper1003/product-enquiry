<?php
/**
 * Quote Rejected Mail Footer
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/quote-rejected-mail/footer.php.
 *
 * HOWEVER, on occasion QuoteUp will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author  WisdmLabs
 * @version 6.5.0
 */

// Link to PEP quote edit page
$editEnquiryLink = admin_url('admin.php?page=quoteup-details-edit&id=' . $args['enquiry_id']);
?>
<div class="edit-quote-link">
    <p>
        <?php echo sprintf(__('Click <a href="%s">here</a> to edit quote.', QUOTEUP_TEXT_DOMAIN), esc_url($editEnquiryLink)); ?>
    </p>
</div>
<?php
do_action('woocommerce_email_footer');

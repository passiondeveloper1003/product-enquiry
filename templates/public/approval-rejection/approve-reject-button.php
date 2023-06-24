<?php
/**
 * Approve Reject Button
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/approval-rejection/approve-reject-button.php.
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
<form  action="" method="POST">
    <input type="hidden" name="_quoteupApprovalRejectionNonce" value="<?php echo wp_create_nonce('approveRejectionNonce'); ?>">
    <input type="hidden" name="quoteupHash" value="<?php echo $_GET[ 'quoteupHash' ] ?>">
    <input type="hidden" name="enquiryEmail" value="<?php echo $_GET[ 'enquiryEmail' ] ?>">
    <input <?php echo $customCSS; ?> type="submit" class="button approve-quote-button" name="approvalQuote" value=" <?php echo apply_filters('quoteup_approve_quote_text', $approveButtonLabel) ?>">
    <a href="#" class="reject-quote-button">
        <?php echo $rejectButtonLabel ?>
    </a>
    <div class="quote-rejection-reason-div">
        <textarea class="quote-rejection-reason-textbox" name="quoteRejectionReason" placeholder="<?php echo apply_filters('quoteup_add_quote_rejection_reason_placeholder', __('Please add your reason behind rejecting the quote', QUOTEUP_TEXT_DOMAIN)); ?>"></textarea>
        <input <?php echo $customCSS; ?> type="submit" class="button reject-quote-button-with-message" name="rejectQuote" value=" <?php echo apply_filters('quoteup_reject_quote_text', __('Reject This Quote', QUOTEUP_TEXT_DOMAIN)); ?>">
    </div>
</form>
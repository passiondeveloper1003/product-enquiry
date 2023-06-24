<?php
/**
 * Approval rejection validation
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/approval-rejection/approval-rejection-validation.php.
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

global $quoteup, $quoteup_enough_stock, $quoteup_enough_stock_product_id, $quoteup_enough_stock_variation_details;

$GLOBALS['approvalRejectionValid'] = true;


if ($result != null || $result === 0) {
    // This template displays quotation expired message.
    quoteupGetPublicTemplatePart('approval-rejection/quotation-expired', '', $args);
    $GLOBALS['approvalRejectionValid'] = false;
    return;
}

if (!$quoteup_enough_stock) {
    // This template displays if one of the product in quotation is out of stock
    quoteupGetPublicTemplatePart('approval-rejection/quotation-enough-stock', '', $args);
    $GLOBALS['approvalRejectionValid'] = false;
    return;
}

$quotation_check = $quoteup->wcCartSession->get('quotationProducts');

if ($quotation_check) {
    // This template displays error if any other quote session is running already
    quoteupGetPublicTemplatePart('approval-rejection/quotation-check', '', $args);
    $GLOBALS['approvalRejectionValid'] = false;
    return;
}


if (isset($_POST[ '_quoteupApprovalRejectionNonce' ]) &&
!empty($_POST[ '_quoteupApprovalRejectionNonce' ]) && isset($quoteup->quoteApprovalRejection->isQuoteRejected) && $quoteup->quoteApprovalRejection->isQuoteRejected) {

    /**
     * Display content when quote is rejected.
     *
     * @param  array  $args  {
     *     An array of arguments.
     *
     *     @type  int  $result  Order id of quote if presents. Default 0.
     *     @type  string  $expiredText  Message for expired quotation.
     * }
     */
    do_action('quoteup_quote_rejected', $args);
    $GLOBALS['approvalRejectionValid'] = false;
    return;
}

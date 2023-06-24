<?php
/**
 * Approval rejection view
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/approval-rejection/approval-rejection-view.php.
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

/**
 * quoteup_approval_rejection_form hook.
 *
 * @hooked displayApprovalRejectionForm - 10 (Quotation approval/rejection form)
 */
do_action('quoteup_approval_rejection_form', $args);

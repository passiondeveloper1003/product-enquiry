<?php
/**
 * Success Message
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/enquiry-modal/success-message.php.
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
<div id="success_<?php echo $prod_id ?>" class="wdmquoteup-success-wrap">
    <div class='success_msg'>
        <span class="wdm-quoteupicon wdm-quoteupicon-done">
        </span>
        <strong>
            <?php echo $thankyou; ?>
        </strong>
    </div>
</div>

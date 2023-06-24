<?php
/**
 * Head
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/approval-rejection/quotation/head.php.
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
<div id="head">
    <h3>
        <?php
        _e('Quote Request', QUOTEUP_TEXT_DOMAIN) ?> #<?php echo "$enquiry_id";
        ?>
    </h3>
</div>
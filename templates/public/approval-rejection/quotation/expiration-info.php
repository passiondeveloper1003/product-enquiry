<?php
/**
 * Expiration Info
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/approval-rejection/quotation/expiration-info.php.
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
<div class="expiration-info">
    <div class="expiration-title">
        <?php
            _e('Expiration Date', QUOTEUP_TEXT_DOMAIN);
        ?>
    </div>
    <div class="expiration-data">
        <?php
        echo $expiration_date.'<br>';
        ?>
    </div>
</div>
<div class="clear">
</div>
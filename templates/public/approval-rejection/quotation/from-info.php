<?php
/**
 * From Info
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/approval-rejection/quotation/from-info.php.
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
<div class="from-info">
    <div class="from-title">
        <?php _e('From', QUOTEUP_TEXT_DOMAIN) ?>
    </div>
    <div class="from-data">
    <?php
        echo $company_name.'<br>';
        echo $company_address.'<br>';
        echo $admin_mail.'<br>';
    ?>
    </div>
</div>
<div class="clear">
</div>
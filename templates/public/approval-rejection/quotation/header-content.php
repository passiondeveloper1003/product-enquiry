<?php
/**
 * Head Content
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/approval-rejection/quotation/header-content.php.
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
<div class="content">
    <?php
    quoteupGetPublicTemplatePart('approval-rejection/quotation/from-info', '', $args);
    quoteupGetPublicTemplatePart('approval-rejection/quotation/to-info', '', $args);
    
    if (!empty($expiration_date)) {
        quoteupGetPublicTemplatePart('approval-rejection/quotation/expiration-info', '', $args);
    }
    ?>
</div>
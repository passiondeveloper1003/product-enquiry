<?php
/**
 * Quotation View
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/approval-rejection/quotation/quotation-view.php.
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
<div id='header'>
    <?php
    quoteupGetPublicTemplatePart('approval-rejection/quotation/pdf-logo', '', $args);
    quoteupGetPublicTemplatePart('approval-rejection/quotation/header-content', '', $args);
    ?>
</div>

<?php
quoteupGetPublicTemplatePart('approval-rejection/quotation/head', '', $args);

quoteupGetPublicTemplatePart('approval-rejection/quotation/quote-table', '', $args);

quoteupGetPublicTemplatePart('approval-rejection/quotation/quote-notes', '', $args);

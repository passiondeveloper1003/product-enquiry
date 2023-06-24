<?php
/**
 * Quote Table
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/approval-rejection/quotation/quote-table.php.
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
<div id="Enquiry">
    <table align="center" class="quoteup-quote-table generated_for_desktop">
        <?php
        quoteupGetPublicTemplatePart('approval-rejection/quotation/quotation-table-head', '', $args);

        /**
         * quoteup_quote_table_row hook.
         *
         * @hooked displayQuoteTableRow - 10 (Displays products rows in quotation)
         */
        do_action('quoteup_quote_table_row', $args);

        quoteupGetPublicTemplatePart('approval-rejection/quotation/quotation-table-total', '', $args);
        ?>
    </table>
</div>
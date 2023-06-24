<?php
/**
 * Quotation Table Total
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/approval-rejection/quotation/quotation-table-total.php.
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
<tr border="1">
    <?php
    if ($show_price == 'yes') {
    ?>
        <td colspan="5" class="final-total">
            <?php _e('TOTAL', QUOTEUP_TEXT_DOMAIN); ?>
        </td>
    <?php
    } else {
    ?>
        <td colspan="4" class="final-total">
            <?php _e('TOTAL', QUOTEUP_TEXT_DOMAIN); ?>
        </td>
    <?php
    }
    ?>
    <td align="center" >
        <?php echo wc_price($GLOBALS['total_price']); ?>
    </td>
</tr>
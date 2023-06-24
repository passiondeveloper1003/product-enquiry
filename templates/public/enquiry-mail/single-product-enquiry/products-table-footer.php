<?php
/**
 * Enquiry mail multi products table footer
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/enquiry-mail/multi-product-enquiry/products-table-footer.php.
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
<tr class="mail-total">
    <td colspan="2">
    </td>
    <td class='total'>
        <?php _e('Total', QUOTEUP_TEXT_DOMAIN); ?>
    </td>
    <td class='total-amount'>
        <?php
        if ($enable_price == 'yes'  || $source == 'admin') {
            echo wc_price($price*$productQuantity);
        } else {
            echo '-';
        }
        ?>
    </td>
</tr>

<?php
/**
 * Enquiry cart total row
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/enquiry-cart/enquiry-cart-total-row.php.
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

global $quoteup;

$quoteupSettings = quoteupSettings();
$colspan         = quoteupReturnColSpanEnqCartUpdateButton($quoteupSettings);

/*
   $colspan variable contains the total number of columns in the enquiry cart.
   The possible values for $colspan variable are 4, 5, 6.
   The first three columns are constant and are not changed in any case.
   Hence we are decreasing the $colspan value by 3 so variable gets the remaining
   number of columns which will be covered by the element .cart-total.
*/
$colspan = $colspan - 3;
?>
<tr class="quoteup-cart-total-row">
    <td colspan="2"></td>
    <td><b><?php echo __('Total', QUOTEUP_TEXT_DOMAIN); ?></b></td>
    <td class="cart-total" colspan="<?php echo esc_attr($colspan); ?>"><?php echo $quoteup->quoteupEnquiryCart->getEnquiryCartTotal(); ?></td>
</tr>

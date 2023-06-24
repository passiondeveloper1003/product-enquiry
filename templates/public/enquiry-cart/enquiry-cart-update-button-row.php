<?php
/**
 * Enquiry cart update button row
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/enquiry-cart/enquiry-cart-update-button-row.php.
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

$updateButtonColSpan = quoteupReturnColSpanEnqCartUpdateButton($form_data);
?>
<tr>
    <td colspan="<?php echo $updateButtonColSpan;?>" class='td-btn-update'>
        <?php
        if (isset($form_data['enable_disable_quote']) && $form_data['enable_disable_quote'] == 1) {
            ?>
            <input type='button' class='update wdm-update' value=' <?php _e('Update Enquiry Cart', QUOTEUP_TEXT_DOMAIN); ?>'>
            <?php
        } else {
            ?>
            <input type='button' class='update wdm-update' value=' <?php _e('Update Enquiry & Quote Cart', QUOTEUP_TEXT_DOMAIN); ?>'>
            <?php
        }
        ?>
        <span class='load-ajax'></span>
    </td>
</tr>

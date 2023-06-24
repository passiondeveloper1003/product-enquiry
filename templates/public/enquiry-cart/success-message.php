<?php
/**
 * success message
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/enquiry-cart/success-message.php.
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
<div class='success' id='success'>
    <div class='wdm-enquiry-success'>
        <span class='wdm-quoteupicon wdm-quoteupicon-done wdm-enquiry-success-icon'>
        </span>
        <?php echo esc_html($thankyou); ?>
    </div>
    <div class='woocommerce'>
        <p class='return-to-shop'>
            <a class='button wc-backward' href='<?php echo esc_url($shopPageUrl); ?>'>
                <?php echo esc_html($returnToShop); ?>
            </a>
        </p>
    </div>
</div>

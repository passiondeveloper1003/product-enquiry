<?php
/**
 * Enquiry cart empty
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/enquiry-cart/enquiry-cart-empty.php.
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
<div class='woocommerce'>
    <p class='cart-empty'>
        <?php echo $emptyMsg; ?>
    </p>
    <p class='return-to-shop'>
        <a class='button wc-backward' href='<?php echo $shopPageUrl; ?>'>
            <?php echo $returnToShop; ?>
        </a>
    </p>
</div>

<?php
/**
 * Quote Rejected
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/approval-rejection/quote-rejected.php.
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
echo $rejectionMessage;
?>
<p class="quoteup-return-to-shop">
    <a class="wc-backward" href="<?php echo esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))); ?>">
        <button <?php echo $customCSS;?>>
            <?php _e('Return To Shop', QUOTEUP_TEXT_DOMAIN) ?>
        </button>
    </a>
</p>
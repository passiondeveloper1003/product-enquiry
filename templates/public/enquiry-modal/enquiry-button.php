<?php
/**
 * Enquiry button
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/enquiry-modal/enquiry-button.php.
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

$product = wc_get_product($prod_id);
$enquiryButtonDisabled = $product->is_type('variable') ? 'disabled' : '';

?>
<!--contact form or btn-->
<div class="quote-form">
    <!-- Button trigger modal -->
    <?php
    if (isset($form_data[ 'show_button_as_link' ]) && $form_data[ 'show_button_as_link' ] == 1) {
        ?>
        <a id="wdm-quoteup-trigger-<?php echo esc_attr($prod_id); ?>" data-toggle="wdm-quoteup-modal" data-target="#wdm-quoteup-modal" href='#' style='font-weight: bold;<?php echo esc_attr($button_text_color); ?>'>
            <?php echo esc_html($instance->returnButtonText($form_data)); ?>
        </a>
        <?php
    } else {
        ?>
        <button type="button" class="<?php echo esc_attr($btn_class); ?>" id="wdm-quoteup-trigger-<?php echo esc_attr($prod_id); ?>" data-toggle="wdm-quoteup-modal" data-target="#wdm-quoteup-modal" <?php echo esc_attr($enquiryButtonDisabled); ?> <?php echo (1 == $manual_css) ? getManualCSS($form_data) : ''; ?>>
            <?php echo esc_html($instance->returnButtonText($form_data)); ?>
        </button>
        <?php
    } ?>
</div><!--/contact form or btn-->

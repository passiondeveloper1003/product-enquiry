<?php
global $quoteup;
$cart_count = $quoteup->wcCartSession->get('wdm_product_count');
$cart_total = $quoteup->quoteupEnquiryCart->getEnquiryCartTotal();
$form_data  = quoteupSettings();

if (isset($form_data[ 'mpe_cart_page' ])) {
    $url = get_permalink($form_data[ 'mpe_cart_page' ]);
} else {
    $url = '';
}

if (!isQuotationModuleEnabled()) {
    $heading = __('Enquiry Cart', QUOTEUP_TEXT_DOMAIN);
} else {
    $heading = __('Quote Cart', QUOTEUP_TEXT_DOMAIN);
}
$heading = apply_filters('quoteup_mini_cart_heading', $heading, $cart_count, $cart_total);

$classes = '';
$icon = '<img src='.plugin_dir_url(__DIR__).'images/collapse-right.svg>';
if (isset($form_data[ 'mini_cart_position' ]) &&  'left' == $form_data[ 'mini_cart_position' ]) {
    $classes = 'left';
    $icon = '<img src='.plugin_dir_url(__DIR__).'images/collapse-left.svg>';
}
$classes = apply_filters('quoteup_mc_wrapper_classes', $classes, $cart_count, $cart_total);

// View Cart Text
$viewCartText = __('View Cart', QUOTEUP_TEXT_DOMAIN);
$viewCartText = apply_filters('quoteup_mc_view_cart_text', $viewCartText, $cart_count, $cart_total);

// 'Total' text shown on the mini-cart.
$totalText = $quoteup->quoteupEnquiryCart->getMiniCartTotalText($cart_total);

?>
<div class="pep-mc-wrap <?php echo $classes; ?>">
    <div class="pep-mc">
        <div class="pep-mc-title">
            <span class="pep-mc-collapse"><?php echo $icon; ?></span>
            <span><?php echo $heading; ?></span>
        </div>
        <div class="pep-mc-items">
            <?php
                global $quoteup;
                $items = $quoteup->wcCartSession->get('wdm_product_info');
                $args = array(
                    'items' => $items,
                );
                if (!empty($items)) {
                    quoteupGetPublicTemplatePart('mini-cart/mini-cart-items', '', $args);
                }
                ?>
        </div>
    </div>
</div>
<div class="pep-mc-footer <?php echo esc_attr($classes); ?>">
    <div class="pep-mc-price">
        <?php echo wp_kses_post($totalText); ?>
    </div>
    <div class="pep-mc-link">
        <a href="<?php echo esc_url($url); ?>"><?php echo esc_html($viewCartText); ?></a>
    </div>
</div>

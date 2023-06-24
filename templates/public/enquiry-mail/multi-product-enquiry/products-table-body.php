<?php
/**
 * Enquiry mail multi products table body
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/enquiry-mail/multi-product-enquiry/products-table-body.php.
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

if (empty($author_email)) {
    $prod = $quoteup->wcCartSession->get('wdm_product_info');
} else {
    $prod = $author_products;
}

foreach ($prod as $element) {
    $id = $element[ 'id' ];
    $url = get_permalink($id);
    $product = wc_get_product($id);
    $enable_price = quoteupShouldHidePrice($id) ? '' : 'yes';
    $sku = $product->get_sku();
    $variationString = '';
    if ($element['variation_id'] != '') {
        $product = wc_get_product($element['variation_id']);
        $variation_sku = $product->get_sku();
        if (!empty($variation_sku)) {
            $sku = $variation_sku;
        }
        $variationString = printVariations($element);
    }

    $title = get_the_title($id);

    if (!empty($variationString)) {
        $variationString = preg_replace(']<br>]', '<br>&#8627 ', $variationString); // Used to add arrow symbol
        $variationString = preg_replace(']<br>]', '', $variationString, 1); // Used to remove first br tag
    }

    // Add variables to arguments so they can be used in other templates
    $args['url'] = $url;
    $args['title'] = $title;
    $args['sku'] = $sku;
    $args['element'] = $element;
    $args['enable_price'] = $enable_price;
    $args['variationString'] = $variationString;

    //This loads the template for products table body for multi product enquiry
    quoteupGetPublicTemplatePart('enquiry-mail/multi-product-enquiry/products-table-row', '', $args);

    if ($enable_price == ''  && $source == 'admin') {
        $GLOBALS['enable_price_flag'] = true;
    }
}

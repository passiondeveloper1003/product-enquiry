<?php
/**
 * Enquiry mail single product table body
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/enquiry-mail/single-product-enquiry/products-table-body.php.
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

//If single product enquiry is active
$product_id = $data_obtained_from_form[ 'product_id' ];
$enable_price = quoteupShouldHidePrice($product_id) ? '' : 'yes';
$title = get_the_title($product_id);
$prod_permalink = $data_obtained_from_form[ 'product_url' ];
$productQuantity = $data_obtained_from_form[ 'product_quant' ];
$variation_id = filter_var($data_obtained_from_form['variation_id'], FILTER_SANITIZE_NUMBER_INT);
$variation_detail = '';
$variationStringToPrint = array();

//Variable Product
if ($variation_id != '') {
    $product = wc_get_product($variation_id);
    $sku = $product->get_sku();
    $variation_detail = $data_obtained_from_form['variation_detail'];
    $price = quoteupGetPriceToDisplay($product, $productQuantity);
    $img = wp_get_attachment_url(get_post_thumbnail_id($variation_id));
    $img_url = getImgUrl($img, $product_id);

    $variation_detail = getVariationDetails($variation_detail);
    $variationString = Includes\Frontend\SendEnquiryMail::getVatiationString($variation_detail, $variation_id);
    $variationStringToPrint = explode(",", $variationString);
} else {
    $product = wc_get_product($product_id);
    $price = quoteupGetPriceToDisplay($product, $productQuantity);

    $sku = $product->get_sku();
    $img_url = wp_get_attachment_url(get_post_thumbnail_id($product_id));
}
//End of Variable Product

if ($price == '') {
    $price = 0;
}

// Add variables to arguments so they can be used in other templates
$args['title'] = $title;
$args['sku'] = $sku;
$args['productQuantity'] = $productQuantity;
$args['enable_price'] = $enable_price;
$args['price'] = $price;
$args['prod_permalink'] = $prod_permalink;
if (!empty($variationStringToPrint)) {
    $args['variationStringToPrint'] = $variationStringToPrint;
}


//This loads the template for products table body for multi product enquiry
quoteupGetPublicTemplatePart('enquiry-mail/single-product-enquiry/products-table-row', '', $args);

if ($enable_price == ''  && $source == 'admin') {
    $GLOBALS['enable_price_flag'] = true;
}

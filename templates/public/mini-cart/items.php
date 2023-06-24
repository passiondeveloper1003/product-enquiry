<?php
global $quoteup;
foreach($items as $product) {
    $_product =  wc_get_product($product['id']);
    $product_link = get_permalink($product['id']);
    $dataVariation = htmlspecialchars(json_encode($product['variation']), ENT_QUOTES, 'UTF-8');
    $img_content = $quoteup->quoteupEnquiryCart->getImageURL($product);
    $url = get_permalink($product[ 'id' ]);
    ?>

    <div class="quoteup_item_wrap">
        <div class="quoteup_item">
            <div class="quoteup_item_delete product-remove">
            <a href="#" class="quoteup_remove_item remove" data-product_id="<?php echo $product[ 'id' ]; ?>" data-variation_id="<?php echo $product['variation_id']; ?>" data-variation = '<?php echo $dataVariation; ?>'>
                &times;
            </a>
            </div>
            <a href="<?php echo $url; ?>" class="quoteup_item_img">
                <img width="180" height="180" src="<?php echo $img_content; ?>" class="attachment-shop_thumbnail size-shop_thumbnail wp-post-image"  sizes="(max-width: 180px) 100vw, 180px" />
            </a>
            <div class="quoteup_item_content">
                <div class="quoteup_item_title">
                    <a href="<?php echo $product_link; ?>"><?php echo $_product->get_title(); ?></a>
                </div>
                <?php if($product['variation']){ ?>
                    <div class="quoteup_item_dop">
                        <?php echo printVariations($product); ?>
                    </div>
                <?php } ?>
                <div class="quoteup_item_price_wrap">
                    <div class="quoteup_item_price_label">Price:</div>
                    <?php echo $product['price']. ' X ' . $product['quantity']; ?> 
                </div>
            </div>
            <div class="quoteup_clear"></div>
        </div>
    </div>
<?php } ?>

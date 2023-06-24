<?php
global $quoteup;
foreach($items as $product) {
    global $quoteup;
    $_product =  wc_get_product($product['id']);
    $product_link = get_permalink($product['id']);
    $dataVariation = htmlspecialchars(json_encode($product['variation']), ENT_QUOTES, 'UTF-8');
    $img_content = $quoteup->quoteupEnquiryCart->getImageURL($product);
    $url = get_permalink($product[ 'id' ]);
    $Price = $quoteup->quoteupEnquiryCart->getPrice($product);
    ?>
        <div class="pep-mc-item">
            

            <a href="<?php echo $url; ?>" class="pep-mc-item-img">
                <img width="180" height="180" src="<?php echo $img_content; ?>" class="attachment-shop_thumbnail size-shop_thumbnail wp-post-image"  sizes="(max-width: 180px) 100vw, 180px" />
            </a>

            <div class="pep-mc-content">
                <div class="pep-mc-item-title">
                    <a href="<?php echo $product_link; ?>"><?php echo $_product->get_title(); ?></a>
                </div>
                <?php if($product['variation']){ ?>
                    <div class="quoteup_item_dop">
                        <?php echo printVariations($product); ?>
                    </div>
                <?php } ?>
                <div class="pep-mc-price-wrap">
                    <div class="pep-mc-item-price">
                    <?php _e('Price:', QUOTEUP_TEXT_DOMAIN); ?>
                    <?php echo $Price. ' × ' . $product['quantity']; ?>
                    </div> 
                </div>
            </div>

            <div class="pep-mc-remove-item">
                <a href="#" class="quoteup_remove_item remove" data-product_id="<?php echo $product[ 'id' ]; ?>" data-variation_id="<?php echo $product['variation_id']; ?>" data-variation = '<?php echo $dataVariation; ?>'>
                <img src="<?php echo plugin_dir_url( __DIR__ ) ?>images/remove.svg">
                </a>
            </div>
        </div>
<?php } ?>

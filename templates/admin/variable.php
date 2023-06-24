<?php

if (!defined('ABSPATH')) {
    exit;
}
global $product;
?>
<input type="hidden" name="variation_id" class="variation_id" id="variation-id-<?php echo $count ?>" value="<?php echo $variationID; ?>">
            <div class="images">
                <img class="variation_image" src="<?php echo $productImage; ?>" data-o_src="<?php echo has_post_thumbnail(absint($id)) ? wp_get_attachment_image_url(get_post_thumbnail_id($id)) : wc_placeholder_img_src(); ?>"/>
            </div>
            <div class="product_meta">
                <span class="sku_wrapper">SKU: <span class="sku" itemprop="sku"><?php echo ($sku = $product->get_sku()) ? $sku : __('N/A', QUOTEUP_TEXT_DOMAIN); ?></span></span>

            </div>   
            <?php
            $attribute_value_saved_in_db = '';
            foreach ($attributes as $attribute_name => $options) {
                $variations = $variationData;
                if (is_array($variations)) {
                    foreach ($variations as $key => $value) {
                        if (strcasecmp(preg_replace('/[^A-Za-z0-9\-_]/', '', sanitize_title($attribute_name)), trim($key)) == 0) {
                            $attribute_value_saved_in_db = stripcslashes($value);
                            break 1;
                        }
                    }
                }
                
                if (!empty($attribute_value_saved_in_db)) {
                    $_REQUEST[ 'attribute_'.sanitize_title($attribute_name) ] = $attribute_value_saved_in_db;
                } else {
                    /*
                         * Above WC 2.6, we have a method get_variation_default_attribute which directly returns the default attributes
                         */
                    if (method_exists($product, 'get_variation_default_attribute')) {
                        $_REQUEST[ 'attribute_'.sanitize_title($attribute_name) ] = $product->get_variation_default_attribute($attribute_name);
                    } else {
                        $defaults = method_exists($product, 'get_default_attributes') ? $product->get_default_attributes() : $product->get_variation_default_attributes();
                        $attribute_name = sanitize_title($attribute_name);
                        $_REQUEST[ 'attribute_'.sanitize_title($attribute_name) ] = isset($defaults[ $attribute_name ]) ? $defaults[ $attribute_name ] : '';
                    }
                }
                unset($options);
            }


            $attribute_keys = array_keys($attributes);

            do_action('woocommerce_before_add_to_cart_form'); ?>
    <?php do_action('woocommerce_before_variations_form'); ?>

    <?php if (empty($available_variations) && false !== $available_variations) : ?>
        <p class="stock out-of-stock"><?php _e('This product is currently out of stock and unavailable.', 'quoteup'); ?></p>
        <?php
    else : ?>
        <table class="variations" cellspacing="0">
            <tbody>
                <?php foreach ($attributes as $attribute_name => $options) : ?>
                    <tr>
                        <td class="label"><label for="<?php echo sanitize_title($attribute_name); ?>"><?php echo wc_attribute_label($attribute_name); ?></label></td>
                        <td class="value">
                            <?php
                                $selected = isset($_REQUEST[ 'attribute_' . sanitize_title($attribute_name) ]) ? wc_clean(urldecode($_REQUEST[ 'attribute_' . sanitize_title($attribute_name) ])) : $product->get_variation_default_attribute($attribute_name);
                                wc_dropdown_variation_attribute_options(array( 'options' => $options, 'attribute' => $attribute_name, 'product' => $product, 'selected' => $selected ));
                                echo end($attribute_keys) === $attribute_name ? apply_filters('woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . __('Clear', 'quoteup') . '</a>') : '';
                            ?>
                        </td>
                    </tr>
                    <?php
                endforeach;
                ?>
            </tbody>
        </table>

        <?php do_action('woocommerce_before_add_to_cart_button'); ?>

        <div class="single_variation_wrap">
            <?php
                /**
                 * woocommerce_before_single_variation Hook.
                 */
                do_action('woocommerce_before_single_variation');

                /**
                 * woocommerce_single_variation hook. Used to output the cart button and placeholder for variation data.
                 * @since 2.4.0
                 * @hooked woocommerce_single_variation - 10 Empty div for variation data.
                 * @hooked woocommerce_single_variation_add_to_cart_button - 20 Qty and cart button.
                 */
                do_action('woocommerce_single_variation');

                /**
                 * woocommerce_after_single_variation Hook.
                 */
                do_action('woocommerce_after_single_variation');
            ?>
        </div>

        <?php do_action('woocommerce_after_add_to_cart_button'); ?>
        <?php
    endif;
    ?>

    <?php do_action('woocommerce_after_variations_form'); ?>

<?php
do_action('woocommerce_after_add_to_cart_form');


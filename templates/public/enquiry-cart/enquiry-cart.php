<?php
/**
 * Enquiry cart.
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/enquiry-cart/enquiry-cart.php.
 *
 * HOWEVER, on occasion QuoteUp will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author  WisdmLabs
 *
 * @version 6.1.0
 */
global $quoteup;
$prodCount = $quoteup->wcCartSession->get('wdm_product_count');
if ($prodCount > 0) {
//if ($_SESSION[ 'wdm_product_count' ] > 0) {
    ?>
    <div class='quoteup-quote-cart'>
        <div class='woocommerce wdm-quoteup-woo'>
            <div class='error-quote-cart' id='error-quote-cart'>
            </div>
            <?php
            /**
             * Before enquiry cart table.
             *
             * @param  array  $args  {
             *     An array of arguments.
             *
             *     @type  string  $shopPageUrl  Shop page URL.
             *     @type  array  $form_data  Quoteup settings.
             *     @type  string  $url  Admin URL.
             * }
             */
            do_action('quoteup_before_enquiry_cart_table', $args);

            /*
             * Display enquiry cart table.
             *
             * @hooked enquiryCartTable - 10 (Displays enquiry cart table).
             *
             * @param  array  $args   {
             *     An array of arguments.
             *
             *     @type  string  $shopPageUrl  Shop page URL.
             *     @type  array  $form_data  Quoteup settings.
             *     @type  string  $url  Admin URL.
             * }
             */
            do_action('quoteup_enquiry_cart_table', $args);

            /*
             * After enquiry cart table.
             *
             * @param  array  $args   {
             *     An array of arguments.
             *
             *     @type  string  $shopPageUrl  Shop page URL.
             *     @type  array  $form_data  Quoteup settings.
             *     @type  string  $url  Admin URL.
             * }
             */
            do_action('quoteup_after_enquiry_cart_table', $args);
            ?>

        </div>
        <?php
        if (!isset($form_data['enquiry_form']) || $form_data['enquiry_form'] == 'default' || $form_data['enquiry_form'] == '') {
            $args = apply_filters('quoteup_enquiry_cart_default_form_arguments', $args);

            //This loads the template for default form for enquiry cart
            quoteupGetPublicTemplatePart('enquiry-cart/default-enquiry-form', '', $args);
        } else {
            $args = apply_filters('quoteup_enquiry_cart_custom_form_arguments', $args);

            //This loads the template for default form for enquiry cart
            quoteupGetPublicTemplatePart('enquiry-cart/custom-enquiry-form', '', $args);
        }
        ?>
    </div>
    <?php

    /**
     * After enquiry form on enquiry cart page.
     *
     * @hooked pepSuccessMessage - 10 (outputs success message)
     *
     * @param  array  $args  {
     *     An array of arguments.
     *
     *     @type  string  $shopPageUrl  Shop page URL.
     *     @type  array  $form_data  Quoteup settings.
     *     @type  string  $url  Admin URL.
     * }
     */
    do_action('quoteup_after_enquiry_form', $args);
} else {
    $args = apply_filters('quoteup_empty_enquiry_cart_arguments', $args);

    /*
     * Before enquriy cart empty content is displayed.
     *
     * @param  array  $args  {
     *     An array of arguments.
     *
     *     @type  string  $returnToShop  'Return to Shop' button label.
     *     @type  string  $shopPageUrl  Shop page URL.
     *     @type  string  $emptyMsg  Empty cart message.
     * }
     */
    do_action('quoteup_before_enquiry_cart_empty', $args);

    /*
     * Content when enquriy cart is empty.
     *
     * @hooked enquriyCartEmpty - 10 (outputs enquiry cart empty message)
     *
     * @param  array  $args  {
     *     An array of arguments.
     *
     *     @type  string  $returnToShop  'Return to Shop' button label.
     *     @type  string  $shopPageUrl  Shop page URL.
     *     @type  string  $emptyMsg  Empty cart message.
     * }
     */
    do_action('quoteup_enquiry_cart_empty', $args);

    /*
     * After enquriy cart empty content is displayed.
     *
     * @param  array  $args  {
     *     An array of arguments.
     *
     *     @type  string  $returnToShop  'Return to Shop' button label.
     *     @type  string  $shopPageUrl  Shop page URL.
     *     @type  string  $emptyMsg  Empty cart message.
     * }
     */
    do_action('quoteup_after_enquiry_cart_empty', $args);
}
?>

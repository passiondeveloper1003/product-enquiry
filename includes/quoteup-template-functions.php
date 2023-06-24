<?php

/**
 * This function adds extra arguments required for cart table row
 *
 * @param  array $args    previous arguments
 * @param  array $product product information
 * @return array          array with added arguments
 */
function generateCartRowArguments($args, $product)
{
    global $quoteup;
    $current_status = quoteupShouldHidePrice($product['id']) ? '' : 'yes';
    $img_content = $quoteup->quoteupEnquiryCart->getImageURL($product);
    $totalPrice = $quoteup->quoteupEnquiryCart->getTotalPrice($current_status, $product);
    $url = get_permalink($product[ 'id' ]);
    $dataVariation = htmlspecialchars(json_encode($product['variation']), ENT_QUOTES, 'UTF-8');
    $placeholder = $quoteup->quoteupEnquiryCart->getPlaceholder($args['form_data']);

    // Add variables to arguments so they can be used in other templates
    if (empty($product['variation_id'])) {
        $args['productHash'] = quoteupReturnProductHash($product['id']);
    } else {
        $args['productHash'] = quoteupReturnProductHash($product['id'], $product['variation_id'], $product['variation']);
    }
    $args['dataVariation'] = $dataVariation;
    $args['img_content'] = $img_content;
    $args['totalPrice'] = $totalPrice;
    $args['product'] = $product;
    $args['placeholder'] = $placeholder;
    $args['url'] = $url;

    return $args;
}

/**
 * Generate and return the hash for a particular product.
 *
 * The product hash is used to identify the each product in the enquiry cart
 * uniquely.
 * It uses md5 to generate the hash.
 * It concatenates product id, variation id and attributes of the variation
 * product.
 *
 * @since 6.5.0
 *
 * @param  int  $productId  Product Id.
 * @param  int  $variationId  Variation Id. Default 0.
 * @param  array  $variationAttributes  Select attributes for the variable
 *                                      product. Default array().
 *
 * @return  string  Return the hash to uniquely identify the product.
 */
function quoteupReturnProductHash($productId, $variationId = '', $variationAttributes = array())
{
    $productHash = md5($productId.'_'.$variationId.'_'.implode('_', $variationAttributes));

    return $productHash;
}

/**
 * This function adds extra arguments required for default enquiry form
 *
 * @param  array $args previous arguments
 * @return array          array with added arguments
 */
function generateDefaultFormArguments($args)
{
    global $quoteup;
    $ajax_nonce = wp_create_nonce('nonce_for_enquiry');
    $currentLocale = getCurrentLocale();
    $manual_css = $quoteup->quoteupEnquiryCart->getCssSettings($args['form_data']);
    $manualCss = '';
    if ($manual_css == 1) {
        $manualCss = getManualCSS($args['form_data']);
    }
    $shopPageUrl = get_permalink(get_option('woocommerce_shop_page_id'));
    $thankyou = __('Enquiry sent successfully!', QUOTEUP_TEXT_DOMAIN);
    $returnToShop = __('Return To Shop', QUOTEUP_TEXT_DOMAIN);

    $args['ajax_nonce'] = $ajax_nonce;
    $args['currentLocale'] = $currentLocale;
    $args['manualCss'] = $manualCss;
    $args['shopPageUrl'] = $shopPageUrl;
    $args['thankyou'] = $thankyou;
    $args['returnToShop'] = $returnToShop;

    return $args;
}

/**
 * This function adds extra arguments required for default enquiry form
 *
 * @param  array $args previous arguments
 * @return array       array with added arguments
 */
function generateEmptyEnquiryCartArguments($args)
{
    if (!isQuotationModuleEnabled()) {
        $emptyMsg = __('Your enquiry cart is currently empty.', QUOTEUP_TEXT_DOMAIN);
    } else {
        $emptyMsg = __('Your enquiry and quotation cart is currently empty.', QUOTEUP_TEXT_DOMAIN);
    }
    $args['returnToShop'] = quoteupReturnToShopBtnLabel();
    $args['shopPageUrl']  = quoteupReturnToShopBtnURL();
    $args['emptyMsg']     = $emptyMsg;

    return $args;
}

/**
 * This function returns boolean to display send me a copy
 *
 * @param  array $form_data settings
 * @return boolean            true to display send me a copy
 */
function checkEnquiryCartSendCopy($form_data)
{
    $enable_mc = '';
    if (isset($form_data[ 'enable_send_mail_copy' ])) {
        return $form_data[ 'enable_send_mail_copy' ];
    }

    return $enable_mc;
}

/**
 * Return the colspan value for 'Update' button which is shown in the enquiry
 * cart.
 *
 * @param array $quoteupSettings Quoteup settings.
 */
function quoteupReturnColSpanEnqCartUpdateButton($quoteupSettings)
{
    $colSpan = 6;

    if (quoteupIsPriceColumnDisabled($quoteupSettings)) {
        $colSpan--;
    }

    if (quoteupIsRemarksColumnDisabled($quoteupSettings)) {
        $colSpan--;
    }

    return apply_filters('quoteup_return_colspan_update_button_enq_cart', $colSpan, $quoteupSettings);
}

/**
 * This function is used to display title of the form
 *
 * @param array $args arguments for template
 */
function displayTitle($args)
{
    //This loads the template for title before form
    quoteupGetPublicTemplatePart('enquiry-cart/display-title', '', $args);
}

/**
 * This function is used to render hidden fields in form
 *
 * @param array $args arguments for template
 */
function renderHiddenFields($args)
{
    //This loads the template for hidden fields in form
    quoteupGetPublicTemplatePart('enquiry-cart/hidden-fields', '', $args);
}

/**
 * This function is used to render nonce div in form
 *
 * @param array $args arguments for template
 */
function renderNonceErrorDiv($args)
{
    //This loads the template for nonce error div in form
    quoteupGetPublicTemplatePart('enquiry-cart/nonce-error', '', $args);
}

/**
 * This function is used to render send me a copy in form
 *
 * @param array $args arguments for template
 */
function displaySendMeCopy($args)
{
    //This loads the template for send me a copy
    quoteupGetPublicTemplatePart('enquiry-cart/send-me-a-copy', '', $args);
}

/**
 * This function is used to render captcha in form
 *
 * @param array $args arguments for template
 */
function displayCaptcha($args)
{
    //This loads the template for google recaptcha
    quoteupGetPublicTemplatePart('enquiry-cart/captcha', '', $args);
}

/**
 * This function is used to render send button in form
 *
 * @param array $args arguments for template
 */
function displaysendButton($args)
{
    //This loads the template for send button
    quoteupGetPublicTemplatePart('enquiry-cart/send-button', '', $args);
}

/**
 * This function is used to render error div in form
 *
 * @param array $args arguments for template
 */
function displayErrorDiv($args)
{
    //This loads the template for error div
    quoteupGetPublicTemplatePart('enquiry-cart/error-div', '', $args);
}

/**
 * This function adds required arguments for success message
 * It also calls template to display success message
 *
 * @param array $args arguments for template
 */
function pepSuccessMessage($args)
{
    if (!quoteupIsCustomFormEnabled()) {
        $thankyou = __('Enquiry sent successfully!', QUOTEUP_TEXT_DOMAIN);
    } else {
        $formID          = $args['form_data']['custom_formId'];
        $formsetting_raw = get_post_meta($formID, 'form_data', true);
        $thankyou        = ((!isset($formsetting_raw['thankyou']) || $formsetting_raw['thankyou'] == '') ? __('Enquiry sent successfully!', QUOTEUP_TEXT_DOMAIN) : quoteupReturnWPMLVariableStrTranslation($formsetting_raw['thankyou']));
    }

    $args['thankyou']     = $thankyou;
    $args['returnToShop'] = quoteupReturnToShopBtnLabel();
    $args['shopPageUrl']  = quoteupReturnToShopBtnURL();

    // @since 6.4.0
    $args = apply_filters('quoteup_enquiry_cart_success_arguments', $args);

    //This loads the template for success message
    quoteupGetPublicTemplatePart('enquiry-cart/success-message', '', $args);
}

/**
 * This function is used to render enquiry cart table in form
 *
 * @param array $args arguments for template
 */
function enquiryCartTable($args)
{
    //This loads the template for enquiry cart table
    quoteupGetPublicTemplatePart('enquiry-cart/enquiry-cart-table', '', $args);
}

/**
 * This function is used to render enquiry cart empty div in form
 *
 * @param array $args arguments for template
 */
function enquriyCartEmpty($args)
{
    //This loads the template for enquiry cart empty
    quoteupGetPublicTemplatePart('enquiry-cart/enquiry-cart-empty', '', $args);
}

/**
 * Single product enquiry modal functions
 */

/**
 * This function starts modal render
 *
 * @param array $args required arguments(product id, price,
 *                    button class, object)
 */
function displayModal($args)
{
    $url = get_permalink($args['prod_id']);
    $img_url = wp_get_attachment_url(get_post_thumbnail_id($args['prod_id']));
    $form_data = get_option('wdm_form_data');
    $title = get_the_title($args['prod_id']);
    $product_id = $args['prod_id'];

    //Append CSS added in the settings page
    quoteupRenderCustomCSS('modal_css1');

    global $wpdb;
    $query = "select user_email from {$wpdb->posts} as p join {$wpdb->users} as u on p.post_author=u.ID where p.ID=%d";
    $uemail = $wpdb->get_var($wpdb->prepare($query, $product_id));

    $manual_css = 0;
    if (isset($form_data[ 'button_CSS' ])) {
        if ($form_data[ 'button_CSS' ] == 'manual_css') {
            $manual_css = 1;
            $dialogue_product_color = $form_data[ 'dialog_product_color' ];
            $dialogue_text_color = $form_data[ 'dialog_text_color' ];
            $color = $form_data[ 'dialog_color' ];
        } else {
            $color = '#FFFFFF';
            $dialogue_product_color = '#999';
            $dialogue_text_color = '#000000';
        }
    } else {
        $color = '#FFFFFF';
        $dialogue_product_color = '#999';
        $dialogue_text_color = '#000000';
    }

    $color = ' style = "background-color:'.$color.'";';
    $dialogue_text_color = " style=' color: ".$dialogue_text_color.";'";
    $dialogue_product_color = " style='color: ".$dialogue_product_color.";'";

    $args['button_text_color'] = '';
    if (isset($form_data['button_text_color']) && $form_data['button_text_color'] != '') {
        $args['button_text_color'] = 'color: '.$form_data[ 'button_text_color' ].';';
    }

    $args['manual_css'] = $manual_css;
    $args['color'] = $color;
    $args['title'] = $title;
    $args['uemail'] = $uemail;
    $args['img_url'] = $img_url;
    $args['url'] = $url;
    $args['product_id'] = $product_id;
    $args['form_data'] = $form_data;
    $args['dialogue_text_color'] = $dialogue_text_color;
    $args['dialogue_product_color'] = $dialogue_product_color;

    /**
     * Render the enquiry modal dialog HTML.
     *
     * @param  array  $args  Array containing data required to fill in the
     *                       modal.
     */
    do_action('quoteup_display_modal_dialog', $args);
}

/**
 * This function starts rendering modal
 *
 * @param  array $args required arguments
 * @return [type]       [description]
 */
function displayModalDialog($args)
{
    //This loads the template for enquiry cart empty
    quoteupGetPublicTemplatePart('enquiry-modal/modal-dialog', '', $args);
}

function displaySuccessDiv($args)
{
    if (!isset($args['form_data']['enquiry_form']) || $args['form_data']['enquiry_form'] == 'default' || $args['form_data']['enquiry_form'] == '') {
        $thankyou = __('Enquiry email sent successfully!', QUOTEUP_TEXT_DOMAIN);
    } else {
        $formID = $args['form_data']['custom_formId'];
        $formsetting_raw = get_post_meta($formID, 'form_data', true);
        $thankyou = ((!isset($formsetting_raw['thankyou']) || $formsetting_raw['thankyou'] == '') ? __('Enquiry sent successfully!', QUOTEUP_TEXT_DOMAIN) : $formsetting_raw['thankyou']);
    }

    $args['thankyou'] = $thankyou;

    // @since 6.4.0
    $args = apply_filters('quoteup_enquiry_modal_success_arguments', $args);

    //This loads the template for success message on single product
    quoteupGetPublicTemplatePart('enquiry-modal/success-message', '', $args);
}

/**
 * This function renders enquiry form header in modal
 *
 * @param array $args required arguments
 */
function enquiryModalFormHeader($args)
{
    quoteupGetPublicTemplatePart('enquiry-modal/enquiry-form-header', '', $args);
}

/**
 * This function renders enquiry form body in modal
 *
 * @param array $args required arguments
 */
function enquiryModalFormBody($args)
{
    quoteupGetPublicTemplatePart('enquiry-modal/enquiry-form-body', '', $args);
}

/**
 * This function renders enquiry form footer in modal
 *
 * @param array $args required arguments
 */
function enquiryModalFormFooter($args)
{
    quoteupGetPublicTemplatePart('enquiry-modal/enquiry-form-footer', '', $args);
}

function enquiryButton($args)
{
    
    quoteupGetPublicTemplatePart('enquiry-modal/enquiry-button', '', $args);
}


function approvalRejectionView($args)
{
    quoteupGetPublicTemplatePart('approval-rejection/approval-rejection-view', '', $args);
}

function approvalRejectionValidation($args)
{
    quoteupGetPublicTemplatePart('approval-rejection/approval-rejection-validation', '', $args);
}

function quoteNotEnoughStock()
{
    global $quoteup;
    $quoteup->wcCartSession->unsetSession();
    if (WC()->cart->get_cart_contents_count() !== 0) {
        $replace_order = new \WC_Cart();
        $replace_order->empty_cart(true);
    }
}

function quoteRejected($args)
{
    global $quoteup;
    $enquiry_id = explode('_', $_GET[ 'quoteupHash' ]);
    $enquiry_id = $enquiry_id[0];
    $order = 0;
    $form_data = quoteupSettings();

    \Includes\QuoteupOrderQuoteMapping::updateOrderIDOfQuote($enquiry_id, $order);

    $rejectionMessage = empty($form_data[ 'reject_message' ]) ? _e('Quote Rejected', QUOTEUP_TEXT_DOMAIN) : $form_data[ 'reject_message' ];

    $customCSS = $quoteup->quoteupViewQuotationApprovalRejection->getCustomCSS($form_data);

    $args['rejectionMessage'] = $rejectionMessage;
    $args['customCSS'] = $customCSS;

    quoteupGetPublicTemplatePart('approval-rejection/quote-rejected', '', $args);
}

/**
 * Displays either a form asking for email id associated with enquiry or  buttons to approve/reject quote. Approve/Reject actions are handled using POST. Email id form's handling is done using GET.
 */
function displayApprovalRejectionForm($args)
{
    global $quoteup;
    $emailAddressFailed = false;
    if (isset($_GET[ 'quoteupHash' ])) {
        if (isset($_GET[ 'enquiryEmail' ])) {
            $email = $_GET[ 'enquiryEmail' ];
            //validate email address entered by user
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
                $emailAddressFailed = $email;
            } else {
                //If email address entered by user is correct, show him/her buttons to accept or reject a quote
                if ($quoteup->quoteApprovalRejection->checkIfEmailIsValid($_GET[ 'enquiryEmail' ], $_GET[ 'quoteupHash' ]) !== 0) {
                    $enquiry_id = getEnquiryIdFromHash($_GET[ 'quoteupHash' ]);
                    //Check if Quote is Expired
                    if ($quoteup->manageExpiration->isQuoteExpired($enquiry_id)) {
                        quoteupGetPublicTemplatePart('approval-rejection/quotation-expired', '', $args);

                        return;
                    }

                    global $wpdb, $quoteup;
                    $enquiry_tbl = getEnquiryDetailsTable();
                    $quotation_tbl = getQuotationProductsTable();
                    $enquiry_id = explode('_', $_GET[ 'quoteupHash' ]);
                    $enquiry_id = $enquiry_id[ 0 ];
                    $enquiry_details = $wpdb->get_row($wpdb->prepare("SELECT product_details, name, email FROM $enquiry_tbl WHERE enquiry_id = %d", $enquiry_id));
                    $quotation = $wpdb->get_results($wpdb->prepare("SELECT * FROM $quotation_tbl WHERE enquiry_id = %d", $enquiry_id), ARRAY_A);
                    $quoteupSettings = quoteupSettings();
                    $company_name = $quoteupSettings['company_name'];
                    $company_address = empty($quoteupSettings['company_address']) ? '' : $quoteupSettings['company_address'];
                    $admin_mail = get_option('admin_email');

                    $name = $enquiry_details->name;

                    $mail = $enquiry_details->email;
                    $show_price = $quotation[ 0 ]['show_price'];
                    $expiration_date = $quoteup->manageExpiration->getExpirationDate($enquiry_id);

                    $args['name'] = $name;
                    $args['company_name'] = $company_name;
                    $args['company_address'] = $company_address;
                    $args['admin_mail'] = $admin_mail;
                    $args['mail'] = $mail;
                    $args['enquiry_id'] = $enquiry_id;
                    $args['show_price'] = $show_price;
                    $args['enquiry_details'] = $enquiry_details;
                    $args['quotation'] = $quotation;
                    $args['expiration_date'] = $expiration_date;

                    ob_start();
                    //Show Quotation sent to user
                    quoteupGetPublicTemplatePart('approval-rejection/quotation/quotation-view', '', $args);
                    $html = ob_get_clean();
                    if (isset($GLOBALS['isProductDeleted']) && $GLOBALS['isProductDeleted']) {
                        quoteupGetPublicTemplatePart('approval-rejection/product-deleted-notice', '', $args);
                        return;
                    } else {
                        echo $html;
                    }

                    $form_data = quoteupSettings();
                    //Show Approve button Label
                    $approveButtonLabel = __('Approve Quote', QUOTEUP_TEXT_DOMAIN);
                    if (isset($form_data['approve_custom_label']) && !empty($form_data['approve_custom_label'])) {
                        $approveButtonLabel = $form_data['approve_custom_label'];
                    }
                    //Show Reject button Label
                    $rejectButtonLabel = __('Reject Quote', QUOTEUP_TEXT_DOMAIN);
                    if (isset($form_data['reject_custom_label']) && !empty($form_data['reject_custom_label'])) {
                        $rejectButtonLabel = $form_data['reject_custom_label'];
                    }
                    $customCSS = $quoteup->quoteupViewQuotationApprovalRejection->getCustomCSS($form_data);

                    $args['customCSS'] = $customCSS;
                    $args['approveButtonLabel'] = $approveButtonLabel;
                    $args['rejectButtonLabel'] = $rejectButtonLabel;

                    quoteupGetPublicTemplatePart('approval-rejection/approve-reject-button', '', $args);
                } else {
                    //email id entered by user does not match with the email id associated with enquiry
                    $emailAddressFailed = $_GET[ 'enquiryEmail' ];
                }
            }
        } else {
            $emailAddressFailed = true;
        }

        //Either email address is blank, invalid or does not match with the one associated with enquiry. Hence show email address form again.
        if ($emailAddressFailed !== false) {
            if ($emailAddressFailed !== true) {
                $args['emailAddressFailed'] = $emailAddressFailed;
                quoteupGetPublicTemplatePart('approval-rejection/email-address-failed', '', $args);
            }
            quoteupGetPublicTemplatePart('approval-rejection/email-address-field', '', $args);
        }
    }
}

function displayQuoteTableRow($args)
{
    $GLOBALS['total_price'] = 0;
    global $quoteup;
    foreach ($args['quotation'] as $quoteProduct) {
        $productAvailable = $quoteup->quoteupViewQuotationApprovalRejection->getProductStatus($quoteProduct);
        if ($productAvailable) {
            $_product = wc_get_product($quoteProduct[ 'product_id' ]);
            $sku = $quoteup->quoteupViewQuotationApprovalRejection->getSku($_product, $quoteProduct['variation_id']);
        } else {
            $GLOBALS['isProductDeleted'] = true;
            break;
        }
        $price = $quoteProduct['oldprice'];

        $args['quoteProduct'] = $quoteProduct;
        $args['_product'] = $_product;
        $args['sku'] = $sku;
        $args['price'] = $price;
        $args['total_price'] = $GLOBALS['total_price'];


        quoteupGetPublicTemplatePart('approval-rejection/quotation/quote-table-row', '', $args);
    }
}

/**
 * This function renders quote content.
 */
function quoteupDisplayQuoteEndPointContent($args)
{
    quoteupGetPublicTemplatePart('quote-frontend/quote', '', $args);
}

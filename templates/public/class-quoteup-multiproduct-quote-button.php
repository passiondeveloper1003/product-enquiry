<?php
namespace Templates\Frontend;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Displays the multi product enquiry button.
 *
 * @static $instance object of class
 */
class QuoteupMultiproductQuoteButton
{
    /**
     * @var Singleton The reference to *Singleton* instance of this class
     */
    private static $instance;

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Action to add a hidden field before variation form
     */
    protected function __construct()
    {
        add_action('woocommerce_before_variations_form', array($this, 'quoteupAddVariationID'));
    }

    /**
     * This function is used to create a hidden field for variation ID on front-end
     */
    public function quoteupAddVariationID()
    {
        if (!is_admin()) : ?>
            <input type="hidden" name="variation_id" class="variation_id" value="0" />
            <?php
        endif;
    }

    /**
     * This function is used to decide to display button or message
     *
     * @param string $calledFrom Page type from which it is called.
     */
    public function decideDisplayButtonOrMessage($calledFrom)
    {
        // @session_start();
        global $decideDisplayQuoteButton,$sitepress,$quoteup;
        $decideDisplayQuoteButton = true;
        $cartLanguage = $quoteup->wcCartSession->get('wdm_cart_language');
        $cartLanguage = isset($cartLanguage) ? $cartLanguage : '';
        //$cartLanguage = isset($_SESSION[ 'wdm_cart_language' ]) ? $_SESSION[ 'wdm_cart_language' ] : '';
        $currentLanguageCode = $sitepress->get_current_language();
        if ($cartLanguage != '' && $cartLanguage != $currentLanguageCode) {
            $currentLanguageName = icl_get_languages('skipmissing=0');
            if (!isset($currentLanguageName[$cartLanguage])) {
                $decideDisplayQuoteButton = true;
                return;
            }
            $currentLanguageName = isset($currentLanguageName[$cartLanguage]['translated_name']) ? $currentLanguageName[$cartLanguage]['translated_name'] : $currentLanguageName[$cartLanguage]['native_name'];
            if ($calledFrom != 'Archive') {
                echo "<div class = 'quote-button-error'>".sprintf(__('Please change language to %s to make enquiry of this product', QUOTEUP_TEXT_DOMAIN), $currentLanguageName).'</div>';
            } else {
                $decideDisplayQuoteButton = false;

                return $currentLanguageName;
            }
            $decideDisplayQuoteButton = false;
        }
    }

    /**
     * This function is used to display Quote button
     * Checks the settings .
     * Gets the Manual Css settings if checked by user.
     * Gets the Custom css if any specified by the user.
     *
     * @param INT    $prod_id                      Product ID
     * @param string $btn_class                    single_add_to_cart_button button alt wdm_enquiry
     * @param object $QuoteUpDisplayQuoteButtonObj QuoteUp button object
     */
    public function displayQuoteButton($prod_id, $btn_class, $QuoteUpDisplayQuoteButtonObj)
    {
        // @session_start();
        if (quoteupIsWpmlActive()) {
            global $decideDisplayQuoteButton;
            $this->decideDisplayButtonOrMessage('Product');
            if (!$decideDisplayQuoteButton) {
                return;
            }
        }

        $default_vals = array('after_add_cart' => 1,
        'button_CSS' => 0,
        'pos_radio' => 0,
        'show_powered_by_link' => 0,
        'enable_send_mail_copy' => 0,
        'enable_telephone_no_txtbox' => 0,
        'only_if_out_of_stock' => 0,
        'dialog_product_color' => '#999',
        'dialog_text_color' => '#333',
        'dialog_color' => '#fff',
        );
        $form_data = get_option('wdm_form_data', $default_vals);
        $pcolor = $QuoteUpDisplayQuoteButtonObj->getDialogTitleColor($form_data);
        $manual_css = 0;
        if ($form_data[ 'button_CSS' ] == 'manual_css') {
            $manual_css = 1;
        }

        quoteupRenderCustomCSS('modal_css1');

        $this->cssHTML($btn_class, $manual_css, $form_data, $prod_id, $QuoteUpDisplayQuoteButtonObj);
        ?>
        <?php
        unset($pcolor);
    }

    /**
     * This function is used to display quote button
     *
     * @param  [type] $btn_class                    [description]
     * @param  [type] $manual_css                   [description]
     * @param  [type] $form_data                    [description]
     * @param  [type] $prod_id                      [description]
     * @param  [type] $QuoteUpDisplayQuoteButtonObj [description]
     * @return [type]                               [description]
     */
    private function cssHTML($btn_class, $manual_css, $form_data, $prod_id, $QuoteUpDisplayQuoteButtonObj)
    {
        ?>
        <div class="quote-form">         <!-- Button trigger modal -->
        <?php
        $this->showAddToQuoteButton($form_data, $manual_css, $prod_id, $btn_class, $QuoteUpDisplayQuoteButtonObj);
        ?>
        </div><!--/contact form or btn-->
        <?php
    }


    /**
     * This function is used to display quote button according to condition.
     * Check if show button as link is checked in Settings or not.
     * Gets manual css and apply inline styling likewise.
     * If yes display corresponding HTML if not.
     * Check variable product or not and display corresponding HTML.
     *
     * @param  Array  $form_data                    Settings stored in database
     * @param  String $manual_css                   Used as flag
     * @param  int    $prod_id                      Product ID
     * @param  string $btn_class                    quoteup button class
     * @param  object $QuoteUpDisplayQuoteButtonObj object of class displayquotebutton
     * @return [type]                               [description]
     */
    private function showAddToQuoteButton($form_data, $manual_css, $prod_id, $btn_class, $QuoteUpDisplayQuoteButtonObj)
    {
        global $product, $wpdb;

        $btn_class = apply_filters('qouteup_enquiry_btn_class', $btn_class, $product, $QuoteUpDisplayQuoteButtonObj);

        if ($product->get_type() == 'variable') {
            if (isset($form_data[ 'show_button_as_link' ]) && $form_data[ 'show_button_as_link' ] == 1) {
                ?>
                <a id="wdm-quoteup-trigger-<?php echo $prod_id ?>" class="quoteup-enq-btn-variable-product" data-toggle="wdm-quoteup-modal" data-target="#wdm-quoteup-modal" href='#' style='font-weight: bold;
                <?php
                if (!empty($form_data[ 'button_text_color' ])) {
                    echo 'color: '.$form_data[ 'button_text_color' ].';';
                }
                ?>'>
                <?php echo $QuoteUpDisplayQuoteButtonObj->returnButtonText($form_data);
                ?>
                </a>
                <?php
            } else {
                if (!is_singular('product')) {
                    ?>
                    <a href="<?php echo get_permalink($prod_id) ?>"><button type="button" class="<?php echo $btn_class ?>"
                    <?php
                    if ($manual_css == 1) {
                        echo getManualCSS($form_data);
                    }
                    ?>>
                        <?php echo $QuoteUpDisplayQuoteButtonObj->returnButtonText($form_data); ?>
                    </button></a>
                    <?php
                } else {
                    ?>
                    <button type="button" class="<?php echo esc_attr($btn_class); ?> quoteup-enq-btn-variable-product" id="wdm-quoteup-trigger-<?php echo esc_attr($prod_id); ?>"  data-toggle="wdm-quoteup-modal" data-target="#wdm-quoteup-modal" 
                    <?php
                    if ($manual_css == 1) {
                        echo getManualCSS($form_data);
                    }
                    ?>>
                    <?php echo $QuoteUpDisplayQuoteButtonObj->returnButtonText($form_data); ?>
                    </button>
                    <?php
                }
            }
        } else {
            if (isset($form_data[ 'show_button_as_link' ]) && $form_data[ 'show_button_as_link' ] == 1) { ?>
                <a id="wdm-quoteup-trigger-<?php echo $prod_id ?>" data-toggle="wdm-quoteup-modal" data-target="#wdm-quoteup-modal" href='#' style='font-weight: bold;
                <?php
                if (!empty($form_data[ 'button_text_color' ])) {
                    echo 'color: '.$form_data[ 'button_text_color' ].';';
                }
                ?>'>
                    <?php echo $QuoteUpDisplayQuoteButtonObj->returnButtonText($form_data); ?>
                </a>
                <?php
            } else {
                ?>
                <button type="button" class="<?php echo $btn_class ?>" id="wdm-quoteup-trigger-<?php echo $prod_id ?>"  data-toggle="wdm-quoteup-modal" data-target="#wdm-quoteup-modal"
                <?php
                if ($manual_css == 1) {
                    echo getManualCSS($form_data);
                }
                ?>>
                <?php echo $QuoteUpDisplayQuoteButtonObj->returnButtonText($form_data); ?>
                </button>
                <?php
            }
        }

        $query = "select user_email from {$wpdb->posts} as p join {$wpdb->users} as u on p.post_author=u.ID where p.ID=%d";
        $uemail = $wpdb->get_var($wpdb->prepare($query, $prod_id));
        $wdmLocale = getCurrentLocale();
        ?>
        <input type='hidden' name='author_email' id='author_email' value='<?php echo $uemail; ?>'>
        <input type='hidden' name='wdmLocale' id='wdmLocale' value='<?php echo $wdmLocale; ?>'>
        <?php
    }
}

            $quoteupMultiproductQuoteButton = QuoteupMultiproductQuoteButton::getInstance();

<?php

namespace Includes\Frontend;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
* This class is to display the quote cart bubble.
*
* @static instance Object of class
*/
class QuoteupDisplayEnquiryCartBubble
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
    * Action to display cart bubble
    */
    protected function __construct()
    {
        add_action('wp_head', array($this, 'displayBubble'), 11);
        add_action('quoteup_after_enquiry_cart_bubble_displayed', array($this, 'enqueueManualStylingForEnquiryCartBubble'));
    }

    /**
     * This function is used to get and return cart url.
     *
     * @param [array] $form_data [settings stored in database]
     *
     * @return [string] $url [Quote Cart URL]
     */
    public function getCartUrl($form_data)
    {
        if (isset($form_data[ 'mpe_cart_page' ])) {
            $url = get_permalink($form_data[ 'mpe_cart_page' ]);
        } else {
            $url = '';
        }

        return $url;
    }

    /**
     * This function is used to enqueue manual styling for cart bubble.
     *
     */
    public function enqueueManualStylingForEnquiryCartBubble()
    {
        $quoteupSettings = quoteupSettings();
        $customStyle     = '';
    
        if (quoteupIsMPEEnabled($quoteupSettings)) {
            // Enquiry Cart Icon Position CSS.
            $customStyle = $this->getEcIconPositionCSS($quoteupSettings);

            // Check 'Specify Icon Color' setting enabled.
            if (quoteupIsManualEcIconColorEnabled($quoteupSettings)) {
                // Add CSS for Enquiry Cart Icon.
                $ecIconColors = quoteupGetEcIconColors($quoteupSettings);
                $customStyle .= "
                    div#wdm-cart-count {
                        background-color: {$ecIconColors['ec_icon_bg_color']};
                        border-color: {$ecIconColors['ec_icon_border_color']};
                    }
                    
                    div#wdm-cart-count > a.wdm-cart-count-link {
                        color: {$ecIconColors['ec_icon_color']};
                    }
                    
                    span.wdm-quoteupicon-count {
                        background-color: {$ecIconColors['ec_icon_number_bg_color']};
                        border-color: {$ecIconColors['ec_icon_number_border_color']};
                        color: {$ecIconColors['ec_icon_number_color']};
                    }";
            }
        }

        if (!empty($customStyle)) {
            wp_add_inline_style('wdm-quoteup-icon', $customStyle);
        }
    }

    /**
     * Return Enquiry Cart Icon position CSS.
     */
    public function getEcIconPositionCSS($quoteupSettings)
    {
        $ecIconPos = quoteupGetEcIconPosition($quoteupSettings);
        $ecIconPosCSS = '';

        switch ($ecIconPos) {
            case 'icon_top_left':
                $ecIconPosCSS = 'left:0;
                                 right:unset;';
                break;
            case 'icon_bottom_left':
                $ecIconPosCSS = 'bottom:0;
                                 left:0;
                                 right:unset;';
                break;
            case 'icon_bottom_right':
                $ecIconPosCSS = 'bottom:0;';
                break;
            default:
                $ecIconPosCSS = '';
                break;
        }

        if (!empty($ecIconPosCSS)) {
            $ecIconPosCSS = "
                div#wdm-cart-count {
                    {$ecIconPosCSS}
                }";
        }

        return $ecIconPosCSS;
    }

    /**
     * This function returns the 'data' attribute which is used by JS to decide whether enquiry bubble
     * should be displayed based on the 'quoteup_wp_session'.
     *
     * @return string 'data' attribute.
     */
    public function getDataAttribute()
    {
        global $quoteup;
        $attr = 'data-display-bubble="1"';
        $prodCount = $quoteup->wcCartSession->get('wdm_product_count');
        
        if (!isset($prodCount) || $prodCount <= 0) {
            $attr = 'data-display-bubble="0"';
        }

        return $attr;
    }

    /*
     * This function is used to display Cart bubble which shows when Multi product enquiry enabled.
     * It has the no. of Products present in Quote Cart.
     * Enqueues the scripts for the bubble i.e, the draggable jquery ui
     */
    public function displayBubble()
    {
        global $quoteup;
        $prodCount = $quoteup->wcCartSession->get('wdm_product_count');
        // @session_start();
        $displayBubble = true;
        $displayBubble = apply_filters('quoteup_display_bubble', $displayBubble);

        if (!$displayBubble) {
            return;
        }
        $form_data = get_option('wdm_form_data');
        if (isset($form_data[ 'enable_disable_mpe' ]) && $form_data[ 'enable_disable_mpe' ] != 1) {
            return;
        }

        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_style('wdm-quoteup-icon', QUOTEUP_PLUGIN_URL.'/css/public/wdm-quoteup-icon.css');
        wp_enqueue_style('quoteup-common-css', QUOTEUP_PLUGIN_URL.'/css/common.css');
        $url = $this->getCartUrl($form_data);
        $dataAttr = $this->getDataAttribute();
        if (isset($form_data[ 'enable_disable_mpe' ]) && $form_data[ 'enable_disable_mpe' ] == 1) {
            wp_enqueue_script('wdm-draggable', QUOTEUP_PLUGIN_URL.'/js/public/enquiry-cart-bubble.js', array('jquery-ui-draggable'));
        }

        quoteupRenderCustomCSS('quoteup-common-css');
        ?>

        <div id="wdm-cart-count" style="display:none" <?php echo $dataAttr; ?>>
            <a href='<?php echo $url ?>' class='wdm-cart-count-link' title="<?php
            $prodCount        = (int) $prodCount;
            $productCountText = $prodCount > 1 ? sprintf(__('%d products added in Enquiry Cart', QUOTEUP_TEXT_DOMAIN), $prodCount) : sprintf(__('%d product added in Enquiry Cart', QUOTEUP_TEXT_DOMAIN), $prodCount);

            echo esc_html($productCountText);
            ?>">
                <span class='wdm-quoteupicon wdm-quoteupicon-list'></span><span class='wdm-quoteupicon-count'><?php echo esc_attr($prodCount); ?></span>
            </a>
        </div>
        <?php
        /**
         * After enquiry cart icon is displayed in case of MPE mode.
         *
         * @param  int  $prodCount  Number of products in the enquiry cart.
         */
        do_action('quoteup_after_enquiry_cart_bubble_displayed', $prodCount);
    }
}
QuoteupDisplayEnquiryCartBubble::getInstance();

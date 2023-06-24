<?php

namespace Includes\Admin;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('Abstracts\QuoteupEdit')) {
    //This file handles the edit quote page functions.
    require_once QUOTEUP_PLUGIN_DIR.'/includes/admin/abstracts/class-abstract-quoteup-edit.php';
}

/**
* Display enquiry details on enquiry edit page.
  * @static $instance Object of class
  * $enquiry_details array enquiry details
*/
class QuoteupEnquiriesEdit extends Abstracts\QuoteupEdit
{

    protected static $instance = null;
    public $enquiry_details = null;

    /**
     * Function to create a singleton instance of class and return the same.
     *
     * @return object -Object of the class
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
    * Get the settings 
    * If Quotation system is enabled then Create Enquiry System.
    * @param array $displayNames 
    */
    public function __construct($displayNames = array())
    {
        $settings = quoteupSettings();
        if (isset($settings['enable_disable_quote']) && $settings['enable_disable_quote'] == 1) {
            parent::__construct();
            add_action('quoteup-edit-heading', array($this, 'displayEnquiryHeading'));
            add_action('quoteup_after_customer', array($this, 'displayEnquiryProductDetails'));
        }
    }

    /**
    * Display Enquiry Heading with its Id
    * @param array $enquiry_details Enquiry Details
    */
    public function displayEnquiryHeading($enquiry_details)
    {
        $enquiryId = $enquiry_details['enquiry_id'];
        esc_html_e('Enquiry Details', QUOTEUP_TEXT_DOMAIN).' #'.$enquiryId;
    }

    
    /**
     * Create Meta box with heading "Product Details".
     *
       * @param array $enquiry_details Enquiry Details
     */
    public function displayEnquiryProductDetails($enquiry_details)
    {
        global $quoteup_admin_menu;
        add_meta_box('editProductDetailsData', __('Product Details', QUOTEUP_TEXT_DOMAIN), array($this, 'productDetailsSection'), $quoteup_admin_menu, 'normal');
    }

    /**
    * Display product details
    */
    public function productDetailsSection()
    {
        ?>
        <div class="wdmpe-detailtbl-wrap">
        <?php echo $this->enquiryTable();
        ?>
        </div>
        <?php        
    }

    /**
    * Display the Product details.
    * Fetches the enquiry details , take the products
    * See if the products are available incurrent database for products or not.
    * Display accordingly the price and other details of the available products.
    * Get the attachments for enquiry.
    */
    public function enquiryTable()
    {
        $enquiryId = filter_var($_GET[ 'id' ], FILTER_SANITIZE_NUMBER_INT);
        $deletedProducts = array();
        $img = QUOTEUP_PLUGIN_URL.'/images/table_header.png';
        ?>
            <!-- <div class="wdmpe-detailtbl-wrap"> -->
            <table class='wdm-tbl-prod wdmpe-detailtbl wdmpe-enquiry-table'>
                <thead class="wdmpe-detailtbl-head">
                    <tr class="wdmpe-detailtbl-head-row">
                        <!-- <th class="wdmpe-detailtbl-head-item item-head-count">#</th> -->
                        <th class="wdmpe-detailtbl-head-item item-head-img">
                            <img src= '<?php echo $img; ?>' class='wdm-prod-img wdm-prod-head-img'/>
                        </th>
                        <th class="wdmpe-detailtbl-head-item item-head-detail">
                            <?php echo __('Item', QUOTEUP_TEXT_DOMAIN); ?>
                        </th>
                        <th class="wdmpe-detailtbl-head-item item-head-sku">
                            <?php echo __('SKU', QUOTEUP_TEXT_DOMAIN); ?>
                        </th>
                        <th class="wdmpe-detailtbl-head-item item-head-cost">
                            <?php echo __('Price', QUOTEUP_TEXT_DOMAIN); ?>
                        </th>
                        <th class="wdmpe-detailtbl-head-item item-head-qty">
                            <?php echo __('Quantity', QUOTEUP_TEXT_DOMAIN); ?>
                        </th>
                        <th class="wdmpe-detailtbl-head-item item-head-cost">
                            <?php echo __('Total price', QUOTEUP_TEXT_DOMAIN); ?>
                        </th>
                    </tr>
                </thead>
                <tbody class="wdmpe-detailtbl-content">
            <?php
            $enquiryProducts = getEnquiryProducts($enquiryId);
            $count = 0;
            $total_price = 0;
            foreach ($enquiryProducts as $prod) {
                $productId = $prod[ 'product_id' ];
                $img_url = $this->getImageURL($prod);
                $url = admin_url("/post.php?post={$productId}&action=edit");
                $deletedClass = '';

                $isVariableProduct = isset($prod[ 'variation_id' ]) && $prod[ 'variation_id' ] != '' && $prod[ 'variation_id' ] != 0;

                    // Check avaiblity of variable product
                    if ($isVariableProduct) {
                        $productAvailable = isProductAvailable($prod[ 'variation_id' ]);
                        $productData = wc_get_product($prod[ 'variation_id' ]);
                    } else {
                        //Avaiblity of simple product
                        $productAvailable = isProductAvailable($productId);
                        $productData = wc_get_product($productId);
                    }
                        // Get latest data from database for available product
                    if ($productAvailable) {
                        $sku = $productData->get_sku();
                        $ProductTitle = '<a href='.$url." target='_blank'>".get_the_title($productId).'</a>';
                    } else {
                        // display old data for product not available
                        $sku = "";
                        $ProductTitle = $prod[ 'product_title' ];
                        $deletedClass = 'deleted-product';
                        ob_start();
                    }

                $sku = $this->getSkuValue($sku);

                $price = $prod[ 'price' ];
                ?>
                                <tr class="wdmpe-detailtbl-content-row <?php echo $deletedClass;
                ?>">
                        <?php
                        ++$count;
                ?>

                                <td class="wdmpe-detailtbl-content-item item-content-img">
                                    <img src= '<?php echo $img_url;
                ?>' class='wdm-prod-img'/>
                                </td>
                                <td class="wdmpe-detailtbl-content-item item-content-link">
                                    <?php
                                    echo $ProductTitle;
                if ($isVariableProduct) {
                    echo '<br>';
                    echo printVariations($prod);
                }
                ?>
                                </td>
                                <td class="wdmpe-detailtbl-content-item item-content-sku">
                            <?php echo $sku;
                ?>
                                </td>
                                <td class="wdmpe-detailtbl-content-item item-content-cost">
                            <?php echo wc_price($price);
                ?>
                                </td>
                                <td class="wdmpe-detailtbl-content-item item-content-qty">
                            <?php echo $prod['quantity'];
                ?>
                                </td>
                                <td class="wdmpe-detailtbl-content-item item-content-cost">
                            <?php
                            echo wc_price($price * $prod[ 'quantity' ]);
                if ($productAvailable) {
                    $total_price = $total_price + ($price * $prod[ 'quantity' ]);
                }
                ?>
                                </td>

                            </tr>
                            <?php
                            if (!$productAvailable) {
                                $deletedRow = ob_get_contents();
                                ob_end_clean();
                                array_push($deletedProducts, $deletedRow);
                            }
            }
            $deletedProducts = implode("\n", $deletedProducts);
            echo $deletedProducts;
                    ?>
                    <tr class="total_amount_row">
                        <?php
                        $this->totalSpan();
            ?>
                        <td class='wdmpe-detailtbl-head-item amount-total-label' id="amount_total_label"><?php _e('Total', QUOTEUP_TEXT_DOMAIN);
        ?> </td>
                        <td class="wdmpe-detailtbl-content-item item-content-cost" id="amount_total"> 
                        <?php echo wc_price($total_price);
        ?></td>
                    </tr>
                </tbody>
            </table>

            <input type="hidden" id="enquiry_id" value="<?php echo $_GET['id'];
        ?>">
            <input type="hidden" id="quoteNonce" value="<?php echo wp_create_nonce('quoteup');
        ?>">
        <!-- </div> -->
        <?php 
        $this->displayAttachments();
    }

    public function totalSpan()
    {
        if (version_compare(WC_VERSION, '2.6', '>')) {
            ?>
                <td class="total_span" colspan="3"></td>
            <?php
        } else {
            ?>
                    <td class="total_span" colspan="4"></td>
            <?php
        }
    }
}
<?php

namespace Includes\Admin;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * This class is used for handleing hover feature on enquiry page.
 * On hover over the enquiry it shows the Product details in tooltip.
 * @static instance Object of class
 */
class QuoteupTooltipOnHover
{
    protected static $instance = null;

    /**
     * Function to create a singleton instance of class and return the same.
     *
     * @return instance of the class.
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * constructor is used to add actions and filter.
     * Filter for creating tooltip for enquiry.
     * Action for enqueue scripts for tooltip.
     */
    private function __construct()
    {
        add_filter('enquiry_list_table_data', array($this, 'enquiryTooltipData'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'addScript'));
    }

    /**
     * This Function is used to add scripts in file for the hook
     * @param string $hook specific hook(admin side)
     */
    public function addScript($hook)
    {
        if ('toplevel_page_quoteup-details-new' == $hook) {
            //This is css for Tooltip
            wp_register_style('tooltipCSS', QUOTEUP_PLUGIN_URL.'/css/admin/tooltipster.css');
            wp_enqueue_style('tooltipCSS');

            //This is js for Tooltip
            wp_register_script('tooltip2', QUOTEUP_PLUGIN_URL.'/js/admin/jquery.tooltipster.min.js');
            wp_enqueue_script('tooltip2');

            //This is custom js file
            wp_register_script('addonJS', QUOTEUP_PLUGIN_URL.'/js/admin/trigger-tooltipster.js', array('jquery'));
            wp_enqueue_script('addonJS');
        }
    }

    /**
     * This function is used to return title stored in database.
     *
     * @param [array] $details         [array of enquiry products]
     * @param [array] $singleQuoteData [Quote Products]
     *
     * @return string $productName Title of Product
     */
    public function getProductEnquiryName($details, $singleQuoteData)
    {
        $productName = 'deleted product';
        foreach ($details as $singleProductEnquiryDetails) {
            if ($singleQuoteData['product_id'] == $singleProductEnquiryDetails['product_id']) {
                $productName = $singleProductEnquiryDetails['product_title'];
            }
        }

        return $productName;
    }

    /**
     * This function is used to get variation details.
     * @param [array] $variation [variation details maybe first empty]
     * @param [int] $variation_id [id for the variable product]
     * @return [array] $allVariationDetails [Variation details of variable product]
     */
    public function getVariationDetails($variation, $variation_id)
    {
        $allVariationDetails = array();
        if (!empty($variation)) {
            $variationDetails = maybe_unserialize($variation);
            if (!isProductAvailable($variation_id)) {
                foreach ($variationDetails as $singleVariationAttribute => $singleVariationValue) {
                    $allVariationDetails[] = wc_attribute_label($singleVariationAttribute).': '.$singleVariationValue;
                }

                return $allVariationDetails;
            }
            $variableProduct = wc_get_product($variation_id);
            if ($variableProduct) {
                $product_attributes = $variableProduct->get_attributes();
            }
            foreach ($variationDetails as $singleVariationAttribute => $singleVariationValue) {
                // If this is a term slug, get the term's nice name
                $label = $this->getLabel($variableProduct, $product_attributes, $singleVariationAttribute, $singleVariationValue);

                $allVariationDetails[] = $label.': '.$singleVariationValue;
            }
        }

        return $allVariationDetails;
    }

    public function getLabel($variableProduct, $product_attributes, $singleVariationAttribute, &$singleVariationValue)
    {
        $taxonomy = wc_attribute_taxonomy_name(str_replace('pa_', '', urldecode($singleVariationAttribute)));
        if (taxonomy_exists($taxonomy)) {
            $term = get_term_by('slug', $singleVariationValue, $taxonomy);
            if (!is_wp_error($term) && $term && $term->name) {
                $singleVariationValue = $term->name;
            }
            $label = wc_attribute_label($taxonomy);
            // If this is a custom option slug, get the options name
        } else {
            $label = quoteupVariationAttributeLabel($variableProduct, $singleVariationAttribute, $product_attributes);
        }

        return $label;
    }

    /**
    * Creates tooltip on hover for the enquiry .
    * For every product in array checks if product is available or not .
    * Creates tooltip likewise.
    * @param array $tooltipProducts array of products in enquiry.
    * @param string html &$tooltip HTML for tooltip.
    * @param string &$deletedProductsTooltip empty at first.
    */
    public function createTooltip($tooltipProducts, &$tooltip, &$deletedProductsTooltip)
    {
        if (!empty($tooltipProducts)) {
            foreach ($tooltipProducts as $singleProduct) {
                if (isset($singleProduct['variation_id']) && $singleProduct['variation_id'] != 0) {
                    $productAvailable = isProductAvailable($singleProduct['variation_id']);
                } else {
                    $productAvailable = isProductAvailable($singleProduct['product_id']);
                }

                if ($productAvailable) {
                    $tooltip .= '<tr>';
                    $tooltip .= '<td>'.$singleProduct[ 'product_name' ].$singleProduct[ 'variation_details' ].'</td>';
                    $tooltip .= '<td>'.$singleProduct[ 'quantity' ].'</td>';
                    $tooltip .= '</tr>';
                } else {
                    $deletedProductsTooltip .= '<tr>';
                    $deletedProductsTooltip .= '<td><del>'.$singleProduct[ 'product_name' ].$singleProduct[ 'variation_details' ].'</del></td>';
                    $deletedProductsTooltip .= '<td><del>'.$singleProduct[ 'quantity' ].'</del></td>';
                    $deletedProductsTooltip .= '</tr>';
                }
            }
        }
    }

    /**
     * This function is used to get the product name.
     * If product is added before than it will take name from array or it will
     * get name from database
     * @param [array] &$productNames [array of the quote data with some product id]
     * @param [array] $singleQuoteData [data for the quote]
     * @param [array] $details [Enquiry details]
     * @return [string] $productName [Name of the product]
     */
    public function getProductName(&$productNames, $singleQuoteData, $details)
    {
        // Check if we have already figured out the product name
        if (isset($productNames[$singleQuoteData['product_id']])) {
            $productName = $productNames[$singleQuoteData['product_id']];
        } else {
            $productName = get_the_title($singleQuoteData['product_id']);

            //If product does not exist, we will get blank title. In that case, lets find out title from Enquiry
            if (empty($productName)) {
                $productName = $this->getProductEnquiryName($details, $singleQuoteData);
            }

            $productNames[$singleQuoteData['product_id']] = $productName;
        }

        return $productName;
    }

    /**
     * This function is used to send the edited data to parent plugin using filter.
     * Gets the Product details in the enquiry and creates tooltip for them on hover.
     * @param [object] $currentdata [old data of table]
     * @param [object] $res         [values fetched from database]
     *
     * @return [object] new data for table with hover functionality
     */
    public function enquiryTooltipData($currentdata, $res)
    {
        $enquiry = $res[ 'enquiry_id' ];
        $admin_path = get_admin_url();
        $tooltipProducts = array();
        $itemText = __('Item', QUOTEUP_TEXT_DOMAIN);
        $quantityText = __('Quantity', QUOTEUP_TEXT_DOMAIN);
        static $productNames = array();

        $deletedProductsTooltip = '';
        $tooltip = '<table>';
        $tooltip .= '<thead>';
        $tooltip .= '<th> ' . $itemText . ' </th>';
        $tooltip .= '<th> ' . $quantityText . ' </th>';
        $tooltip .= '</thead>';

        $form_Data = quoteupSettings();

        $databaseProducts = getQuoteProducts($enquiry);

        // If quotation module is activated
        if (1 == $form_Data['enable_disable_quote'] || empty($databaseProducts)) {
            $databaseProducts   = getEnquiryProducts($enquiry);
        }

        $databaseProducts   = apply_filters('quoteup_enq_quote_products_on_hover', $databaseProducts, $enquiry);
        $totalNumberOfItems = count($databaseProducts);
        $result             = $databaseProducts;

        foreach ($result as $singleQuoteData) {
            //Get Product Name
            $productName = $this->getProductName($productNames, $singleQuoteData, $result);

            // This is variable product for which quote is created
            if ($singleQuoteData['variation_id'] != 0 &&  $singleQuoteData['variation_id'] != null) {
                //Create array of variation details
                $allVariationDetails = $this->getVariationDetails($singleQuoteData['variation'], $singleQuoteData['variation_id']);
                $tooltipProducts[] = array(
                'product_id' => $singleQuoteData['product_id'],
                'product_name' => $productName,
                'variation_details' => ' ( '.implode(', ', $allVariationDetails).' ) ',
                'quantity' => $singleQuoteData['quantity'],
                'variation_id' => $singleQuoteData['variation_id'],
                );
            } else {
                // This is simple product

                $tooltipProducts[] = array(
                'product_id' => $singleQuoteData['product_id'],
                'product_name' => $productName,
                'variation_details' => '',
                'quantity' => $singleQuoteData['quantity'],
                'variation_id' => 0,

                );
            }
        }

        //Create strings for tooltip
        $this->createTooltip($tooltipProducts, $tooltip, $deletedProductsTooltip);

        $tooltip .= $deletedProductsTooltip.'</table>';
        $tooltip = esc_html($tooltip);
        $tooltip = stripcslashes($tooltip);

        $currentdata[ 'product_details' ] = "<a class = 'Items-hover' title='$tooltip'  href='{$admin_path}admin.php?page=quoteup-details-edit&id=$enquiry'> {$totalNumberOfItems}</a>";

        return apply_filters('quoteup_enquiry_tooltip_data', $currentdata, $res, $tooltip);
    }
}

QuoteupTooltipOnHover::getInstance();

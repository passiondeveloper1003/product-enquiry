<?php

namespace Admin\Includes;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * This class is used to create meta box for versions on enquiry details page and display versions in that box.
 * @static $instance Object of class
 * $enquiry_details array enquiry details
 */
class QuoteupDisplayVersions
{
    protected static $instance = null;
    public $enquiry_details = null;

    /**
     * Function to create a singleton instance of class and return the same.
     *
     * @return object instance of the class
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor is used to add action for meta box for versions.
     */
    public function __construct()
    {
        // Display the admin notification
        add_action('quoteup_edit_details', array($this, 'versionsMeta'));
    }

    /**
     * Create Meta box with heading "Version".
     * @param array $enquiry_details Enquiry Details
     */
    public function versionsMeta($enquiry_details)
    {
        $this->enquiry_details = $enquiry_details;
        global $quoteup_admin_menu;
        $form_data      = get_option('wdm_form_data');
        $showVersions   = 1;
        $hasCapability  = quoteupIsCurrentUserHavingManageOptionsCap();
        $hasCapability  = apply_filters('quoteup_has_cap_for_quote_version', $hasCapability, $enquiry_details);

        if (!$hasCapability || (isset($form_data[ 'enable_disable_quote' ]) && $form_data[ 'enable_disable_quote' ] == 1)) {
            $showVersions = 0;
        }

        if ($showVersions == 1) {
            add_meta_box('editVersions', __('Quote Version', QUOTEUP_TEXT_DOMAIN), array(
            $this, 'editVersionsFn', ), $quoteup_admin_menu, 'normal');
        }
    }

     /**
    * Gets the username from userId
    * @param [int] $userId  user id
    * @return string [username]
    */
    private function getUserName($userId)
    {
        if (is_numeric($userId)) {
            $user = get_userdata($userId);
            if ($user === false) {
                return '';
            } else {
                return $user->display_name;
            }
        }
    }

    /**
     * This function displays all the versions available in enquiry versions table for that particular enquiry.
     */
    public function editVersionsFn()
    {
        global $wpdb;
        $versionTbl = getVersionTable();
        $enquiryID = filter_var($_GET[ 'id' ], FILTER_SANITIZE_NUMBER_INT);
        $sql = $wpdb->prepare("SELECT MAX(version) FROM $versionTbl WHERE enquiry_id=%d", $enquiryID);
        $maxVersion = $wpdb->get_var($sql);

        ?>
        <table class="enquiry-versions">
            <thead>
                <tr>
                    <th class="version-number-head"><?php _e('Version No.', QUOTEUP_TEXT_DOMAIN); ?></th>
                    <th class="version-date-head"><?php _e('Date and Time', QUOTEUP_TEXT_DOMAIN); ?></th>
                    <th class="version-product-details-head"><?php _e('Product Details', QUOTEUP_TEXT_DOMAIN); ?></th>
                    <th class="version-total-head"><?php _e('Total', QUOTEUP_TEXT_DOMAIN); ?></th>
                    <th class="performed-by-head"><?php _e('Performed by', QUOTEUP_TEXT_DOMAIN); ?></th>
                </tr>
            </thead>
                <tr class="version-row wdm-version-heading">
                    <th class="version-number-head"></th>
                    <th class="version-date-head"></th>
                    <th class="version-product-details-head">
                        <table style="width: 90%;">
                            <thead>
                                <td class=""><?php _e('Product Name', QUOTEUP_TEXT_DOMAIN); ?></th>
                                <td class=""><?php _e('Quantity', QUOTEUP_TEXT_DOMAIN); ?></th>
                                <td class="" style="text-align: right;"><?php _e('Price', QUOTEUP_TEXT_DOMAIN); ?></th>                                
                            </thead>
                        </table>
                    </th>
                    <th class="version-total-head"></th>
                    <th class="performed-by-head"></th>
                </tr>
        <?php
        if (empty($maxVersion) || $maxVersion == 0) {
            ?>
            <tr>
                <td class='no-found' colspan="5"><?php _e('No Versions Found', 'quoteup'); ?></td>
            </tr>
            <input type="hidden" name="last-version" class="last-version" value= "0">
            <?php
        } else {
            for ($version = $maxVersion; $version > 0; --$version) {
                $versionDetails = $wpdb->get_results($wpdb->prepare("SELECT * FROM $versionTbl WHERE enquiry_id = %d AND version = %d ORDER BY version", $enquiryID, $version), ARRAY_A);
                $this->printVersionRow($version, $versionDetails);
            }
        }
        ?>
        </table>
        <input type="hidden" name="last-version" class="last-version" value=<?php echo $maxVersion;
        ?>>
        <?php
    }

    /**
    * This function is for performing different actions on basis of History status
    * @param array $version version for enquiries
    * @param $versionDetails Version details
    */
    public function printVersionRow($version, $versionDetails)
    {
        ?>
        <tr class="version-row wdm-versionnum-row">
            <td class="version-number" data-title="Version no."><p> V<?php echo $version ?></p></td>
            <td class="version-date" data-title="Date and Time"><p><?php echo $versionDetails[0][ 'version_date' ] ?></p></td>
            <td class="version-product-details">
                <table class="version-details" align="center">
                    <?php
                    $quoteTotal = 0;
                    foreach ($versionDetails as $versionDetail) {
                        $url = admin_url("/post.php?post={$versionDetail['product_id']}&action=edit");
                        $ProductTitle = '<a href='.$url." target='_blank'>".get_the_title($versionDetail['product_id']).'</a>';
                        $price = $versionDetail['quantity'] * $versionDetail['newprice'];
                        ?>
                        <tr>
                            <td class="version-product-name" data-title="Product Name">
                              <p>
                                <?php
                                echo $ProductTitle;
                                if (isset($versionDetail[ 'variation_id' ]) && $versionDetail[ 'variation_id' ] != '' && $versionDetail[ 'variation_id' ] != 0) {
                                    $variationString = printVariations($versionDetail);
                                    $variationString = preg_replace(']<br>]', '<br>&#8627 ', $variationString); // Used to add arrow symbol
                                    $variationString = preg_replace(']<br>]', '', $variationString, 1); // Used to remove first br tag
                                    echo "<div style='margin-left:10px'>";
                                    echo $variationString;
                                    echo '</div>';
                                }
                                ?>
                              </p>
                            </td>
                            <td class="version-product-qty" data-title="Quantity"><p><?php echo $versionDetail['quantity']; ?></p></td>
                            <td class="version-product-price" data-title="Price"><p><?php echo wc_price($price); ?></p></td>
                        </tr>
                        <?php
                        $quoteTotal = $quoteTotal + $price;
                    }
                    ?>
                </table>
            </td>
            <td class="version-total" data-title="Total"><p> <?php echo wc_price($quoteTotal) ?></p></td>
            <td class="performed-by" data-title="Performed By"> <p><?php echo $this->getUserName($versionDetail[ 'performed_by' ]) ?></p></td>
        </tr>
        <?php
    }

    /**
    * Add the enquiry details in the version details table.
    * @param int $enquiry_id Enquiry Id
    * @param int $product_d Product Id
    * @param int $new_price New Price from input box
    * @param int $quantity Quantity of Product
    * @param int $old_price Old price of Product.
    * @param int $variation_id Variation Id
    * @param array $variation_detials Variation details
    * @param bool $show_price Show Price in quotation.
    * @param int $variation_index_in_enquiry Variation index in enquiry
    * @param int $currentVersion current version of enquiry.
    */
    public function addVersion($enquiry_id, $product_id, $newprice, $quantity, $oldprice, $variation_id, $variation_details, $show_price, $show_price_diff, $variation_index_in_enquiry, $currentVersion)
    {
        global $wpdb;
        $versionTableName = getVersionTable();

        $date = current_time('mysql');
        $performedBy = null;
        if (is_user_logged_in()) {
            $performedBy = get_current_user_id();
        }

        $wpdb->insert(
            $versionTableName,
            array(
                'enquiry_id' => $enquiry_id,
                'version' => $currentVersion + 1,
                'product_id' => $product_id,
                'newprice' => $newprice,
                'quantity' => $quantity,
                'oldprice' => $oldprice,
                'variation_id' => $variation_id,
                'variation' => serialize($variation_details),
                'show_price' => $show_price,
                'show_price_diff' => $show_price_diff,
                'variation_index_in_enquiry' => $variation_index_in_enquiry,
                'version_date' => $date,
                'performed_by' => $performedBy,
            )
        );
    }
}

$this->displayVersions = QuoteupDisplayVersions::getInstance();

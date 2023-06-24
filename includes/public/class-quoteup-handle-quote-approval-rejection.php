<?php

namespace Includes\Frontend;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/**
 * Handles Approval and Rejection on the Frontend Side.
 * Handles the response on approval and rejection
* @static instance Object of class
* $isQuoteRejected bool
 */
class QuoteupHandleQuoteApprovalRejection
{
    /**
     * @var Singleton The reference to *Singleton* instance of this class
     */
    private static $instance;

    public $isQuoteRejected = false;

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
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     * Action to handle approval rejection response from user.
     */
    protected function __construct()
    {
        add_action('wp_loaded', array($this, 'handleApprovalRejectionResponse'));
    }

    /**
     * Checks if email id passed to function is associated with the enquiry.
     *
     * @global object $wpdb Database object
     *
     * @param string $emailAddress Email Address of the user
     * @param string $enquiryHash  Hash of the enquiry
     *
     * @return int If enquiry is found, returns enquiry id, else returns 0
     */
    public function checkIfEmailIsValid($emailAddress, $enquiryHash)
    {
        global $wpdb;
        $quotationTable = getEnquiryDetailsTable();
        $checkEnquiryExists = $wpdb->get_var($wpdb->prepare("SELECT enquiry_id FROM $quotationTable WHERE email = %s AND enquiry_hash = %s", $emailAddress, $enquiryHash));

        if ($checkEnquiryExists === null) {
            return 0;
        }

        return $checkEnquiryExists;
    }

    /**
     * Triggers the display for Shortcode.
     * Action for adding Approve Reject Button in the mail
     * @return string $getContent ob contentS
     */
    public static function approvalRejectionShortcodeCallback()
    {
        ob_start();
        /**
         * Display the content on the Approval/ Rejection page.
         */
        do_action('quoteup_approval_rejection_content');
        $getContent = ob_get_contents();
        ob_end_clean();

        return $getContent;
    }
    /**
    * Gets if approve quote is checked or not.
    * @param string $hash quoteup hash
    * @param string $enquiryEmail customers' email
    * @return bool true if yes
    */
    public function getFlag($hash, $enquiryEmail)
    {
        if ((isset($_POST['approvalQuote']) &&
        isset($_POST['quoteupHash']) &&
        isset($_POST['enquiryEmail']) &&
        !empty($hash) &&
        !empty($enquiryEmail)) || (isset($_GET['quoteupHash']) && isset($_GET['enquiryEmail']) && isset($_GET['source']) && $_GET['source'] == 'emailApprove')) {
            return true;
        }
        return false;
    }

    /**
     * Handles the action taken by user on the frontend. It triggers all the actions
     * need to be taken on clicking 'Approve' or 'Reject' button on the frontend.
     * When approve button is clicked the history status changes.
     * It checks whether the product is in stock or not .
     * If yes put that product in cart and redirect user to checkout page.
     * Otherwise if product is out of stock make history as approved quote but order not placed.
     * @global object $quoteupManageHistory Object of the class which manages history
     */
    public function handleApprovalRejectionResponse()
    {
        $data = $this->getData();

        if (!isset($data['_quoteupApprovalRejectionNonce']) ||
            empty($data['_quoteupApprovalRejectionNonce'])) {
            return;
        }

        global $quoteup, $quoteupManageHistory, $quoteup_enough_stock;
        $hash = trim($data['quoteupHash']);
        $enquiryEmail = trim($data['enquiryEmail']);
        //User has approved the quote. Handles that action in below if.
        $checkFlag = $this->getFlag($hash, $enquiryEmail);
        if ($checkFlag) {
            $result = \Includes\QuoteupOrderQuoteMapping::getOrderIdOfQuote(0);
            $expiredText = apply_filters('quoteup_quote_expired_text', __('The quotation has expired.', QUOTEUP_TEXT_DOMAIN));

            $args = array(
                'result' => $result,
                'expiredText' => $expiredText,
                );
            quoteupGetPublicTemplatePart('approval-rejection/approval-rejection-validation', '', $args);
            if (!$GLOBALS['approvalRejectionValid']) {
                return;
            }
            //Check if hash is correct
            if (($enquiryId = $this->checkIfEmailIsValid($data['enquiryEmail'], $data['quoteupHash'])) !== 0) {
                $enquiry_id = explode('_', $_GET['quoteupHash']);
                $enquiry_id = $enquiry_id[0];
                $quoteupManageHistory->addQuoteHistory($enquiry_id, __('Approved but order not yet placed'), 'Approved');

                //Add Product in cart and redirect to cart page
                $quoteup->wcCart->addProductsToCart($enquiryId);

                if ($quoteup_enough_stock) {
                    $quoteup->wcCart->redirectToCheckoutPage('ManualRedirect');
                }
            }
        } elseif (isset($data['quoteupHash']) &&
            isset($data['enquiryEmail']) &&
            !empty($data['quoteupHash']) &&
            !empty($data['enquiryEmail']) &&
            $enquiryId = $this->checkIfEmailIsValid($data['enquiryEmail'], $data['quoteupHash'])) {
            if (!$enquiryId) {
                return;
            }

            //User has rejected the quote. Handles that action in this else.
            //Check if hash is correct
            $enquiry_id = explode('_', $_GET['quoteupHash']);

            $reason = trim($data['quoteRejectionReason']);
            $message = sanitize_textarea_field($data['quoteRejectionReason']);
            if (empty($reason)) {
                $message = __('No message from customer', QUOTEUP_TEXT_DOMAIN);
            }

            $message = stripcslashes($message);

            $quoteupManageHistory->addQuoteHistory($enquiryId, $message, 'Rejected');
            $this->isQuoteRejected = true;

            // Send email to admin
            $emailObject = QuoteupCustomerRejectedEmail::getInstance($enquiryId, $message);

            /**
             * Send email to admin, recipients when quotation is rejected by
             * the customer.
             *
             * @since 6.5.0
             *
             * @param   Object  Instance of QuoteupCustomerRejectedEmail class.
             */
            do_action('send_quote_rejected_email', $emailObject);
        }
    }

    public function isApprovalRejectionParamSet()
    {
        if ((isset($_POST['quoteupHash']) && isset($_POST['enquiryEmail']) && !empty($_POST['quoteupHash']) && !empty($_POST['enquiryEmail'])) || (isset($_GET['quoteupHash']) && isset($_GET['enquiryEmail']) && !empty($_GET['quoteupHash']) && !empty($_GET['enquiryEmail']))) {
            return true;
        } else {
            return false;
        }
    }

    private function getData()
    {
        $data = $_POST;
        if (isset($_GET['source']) && $_GET['source'] == 'emailApprove') {
            $data = $_GET;
        }

        return $data;
    }
}

$this->quoteApprovalRejection = QuoteupHandleQuoteApprovalRejection::getInstance();

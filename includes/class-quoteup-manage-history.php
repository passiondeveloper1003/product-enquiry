<?php

namespace Includes;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Manage Quote history.
 * Updates in database the status of the Enquiries.
 * 'Requested' if it is still in process.
 * 'Quote Created' if it is admin created quote.
 * Adds the message to the Enquiries.
 * Gets the Details of the Last Added History.
  * @static instance Object of class
 */
class QuoteupManageHistory
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
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     * Action to update status of enquiry requested
     * Action to update status of Quote created
     */
    protected function __construct()
    {
        add_action('quoteup_add_custom_field_in_db', array($this, 'updateRequest'), 15, 2);
        add_action('create_quote_form_entry_added_in_db', array($this, 'updateQuoteCreated'), 15, 2);
    }
    /**
    * This function is update the status of the Enquiry Request to Requested.
    * @param [int] $insert [Enquiry Id]
    */
    public function updateRequest($insert, $data)
    {
        $this->addQuoteHistory($insert, '-', 'Requested', $data);
    }
    /**
    * This function is responsible for updating the status of Enquiry to "Quote Created" when
    * it is a admin created Quote or the Quote created from backend.
    * @param [int] $insert [Enquiry Id]
    */
    public function updateQuoteCreated($insert, $data)
    {
        if (!isset($_POST['globalEnquiryID']) || $_POST['globalEnquiryID'] == 0) {
            $this->addQuoteHistory($insert, '-', 'Quote Created', $data);
        }
    }

    /**
     * Add message to Enquiry in the Manage Enquiry history table.
     *
     * @param int    $enquiryId Enquiry id associated with enquiry
     * @param string $message   message to be added in the enquiry
     * @param string $status    action performed in the enquiry
     * @param array  $data      $_POST data containing the useremail. Default null.
     */
    public function addQuoteHistory($enquiryId, $message, $status, $data = null)
    {
        global $wpdb;
        $date = current_time('mysql');
        $table_name = getEnquiryHistoryTable();
        $performedBy = null;
        if (is_user_logged_in()) {
            $performedBy = get_current_user_id();
        }
        
        if ((!is_user_logged_in() && 'Requested' == $status) || 'Quote Created' == $status) {
            $email = isset($data[ 'txtemail' ]) ? filter_var($data[ 'txtemail' ], FILTER_SANITIZE_EMAIL) : '';
            $userData = empty($email) ? '' : get_user_by('email', $email);
            
            if (!empty($userData)) {
                $performedBy = $userData->ID;
            }
        }

        /**
         * Before adding data in 'enquiry_history' table.
         *
         * @param  int  $enquiryId  Enquiry Id.
         * @param  string  $message  Message.
         * @param  string  $status  Quote status.
         */
        do_action('quoteup_before_adding_history', $enquiryId, $message, $status);
        $insert_id = $wpdb->insert(
            $table_name,
            array(
            'enquiry_id' => $enquiryId,
            'date' => $date,
            'message' => $message,
            'status' => $status,
            'performed_by' => $performedBy,
            )
        );

        /**
         * After adding data in 'enquiry_history' table.
         *
         * @param  int  $insert_id  Id for the inserted row in 'enquiry_history' table.
         * @param  int  $enquiryId  Enquiry Id.
         * @param  string  $message  Message.
         * @param  string  $status  Quote status.
         */
        do_action('quoteup_after_adding_history', $insert_id, $enquiryId, $message, $status);
    }

    /**
     * Returns the last entry added in the history table.
     * @param int    $enquiry_id Enquiry id associated with enquiry
     * @return Returns NULL if record is not found in the database. Otherwise returns the data
     */
    public function getLastAddedHistory($enquiry_id)
    {
        global $wpdb;
        $table_name = getEnquiryHistoryTable();

        return $wpdb->get_row($wpdb->prepare("SELECT ID, date, message, status, performed_by FROM $table_name WHERE enquiry_id = %d ORDER BY ID DESC LIMIT 1", $enquiry_id), ARRAY_A);
    }

    /**
     * Returns the last status in the history table.
     *
     * @param int    $enquiry_id Enquiry id associated with enquiry
     * @return Returns NULL if record is not found in the database. Otherwise returns the data
     */
    public function getLastAddedStatus($enquiry_id)
    {
        global $wpdb;
        $table_name = getEnquiryHistoryTable();

        return $wpdb->get_var($wpdb->prepare("SELECT status FROM $table_name WHERE enquiry_id = %d ORDER BY ID DESC LIMIT 1", $enquiry_id));
    }

    /**
     * Returns the history data of a particular quote.
     *
     * @param  int      $enquiry_id         Enquiry ID
     * @param  bool     $assoctivateArray   True if need associative array. Default false.
     * @return array    Returns indexed array of row objects or indexed array
     *                  of associative array containing the quote history data or
     *                  empty array if no data is found.
     */
    public static function getQuoteHistoryData($enquiry_id, $allQuoteData = false, $assoctivateArray = false)
    {
        global $wpdb;
        $historytable = getEnquiryHistoryTable();

        if (true == $allQuoteData) {
            $quotationtable = getQuotationProductsTable();
            $enquiry_id = $enquiry_id;
            $sql = "SELECT $historytable.enquiry_id,product_title,show_price,oldprice, newprice, variation, variation_id,quantity FROM $historytable join $quotationtable on $quotationtable.enquiry_id = $historytable.enquiry_id and $historytable.enquiry_id=$enquiry_id and $historytable.ID = (select max(ID) from $historytable where $historytable.enquiry_id = $quotationtable.enquiry_id)";
        } else {
            $sql = $wpdb->prepare("SELECT enquiry_id, date, message, status, performed_by FROM $historytable WHERE enquiry_id=%s ORDER BY date DESC", $enquiry_id);
        }

        if (true == $assoctivateArray) {
            $result = $wpdb->get_results($sql, ARRAY_A);
        } else {
            $result = $wpdb->get_results($sql);
        }

        return $result;
    }
}

/*
 * Creating $quoteupManageHistory as a Global variable because it is going to be needed in Cron Job too.
 * Creating it as a separate variable eliminates bootstraping of $quoteup variable and hence can be used
 * directly whenever needed 
 */
$GLOBALS['quoteupManageHistory'] = QuoteupManageHistory::getInstance();

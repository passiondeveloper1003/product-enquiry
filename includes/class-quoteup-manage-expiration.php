<?php

namespace Includes;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/**
 * Handles all the tasks related to expiration of quotes
 * Sets the expiration date and checks if Quote Expired.
 * @static instance Object of class
 */

class QuoteupManageExpiration
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
     */
    protected function __construct()
    {
    }

    /**
     * This function is used to set and update expiration date in database
     * @param [date] $expiration_date [Expiry Date]
     * @param [int] $enquiry_id      [enquiry ID]
     */
    public function setExpirationDate($expiration_date, $enquiry_id)
    {
        global $wpdb;
        $date = date_create_from_format('Y-m-d H:i:s', $expiration_date);
        $mysql_datetime = date_format($date, 'Y-m-d H:i:s');
        $wpdb->update(
            getEnquiryDetailsTable(),
            array(
                    'expiration_date' => $mysql_datetime,
                    ),
            array(
                    'enquiry_id' => $enquiry_id,
                    )
        );

        /**
         * Use hook after expiration date for the quote is updated in
         * the databases.
         *
         * @param  int  $enquiry_id  Enquiry Id.
         * @param  string  $expiration_date  Expiration date set for the quote.
         */
        do_action('quoteup_set_expiration_date', $enquiry_id, $expiration_date);
    }

    /**
     * This function is used to get expiry date from database
     * @param  [int] $enquiry_id [enquiry ID]
     * @return [date]             [Date stored in database]
     */
    public function getExpirationDate($enquiry_id)
    {
        global $wpdb;
        $table = getEnquiryDetailsTable();
        $expiration_date = $wpdb->get_var($wpdb->prepare("SELECT expiration_date FROM $table WHERE enquiry_id = %d", $enquiry_id));
        if ($expiration_date) {
            return $this->getHumanReadableDate($expiration_date);
        }

        return '';
    }

    /**
     * This function is used to convert date in human readable format
     * @param  [date] $expiration_date_time [date to be converted]
     * @return [date]                       [Human readable date]
     */
    public function getHumanReadableDate($expiration_date_time)
    {
        if (empty($expiration_date_time) || $expiration_date_time == '0000-00-00 00:00:00') {
            return '';
        }

        $dateTime = date_create_from_format('Y-m-d H:i:s', $expiration_date_time);

        return apply_filters('quoteup_get_human_readable_expiration_date', date_format($dateTime, 'M d, Y'), $dateTime);
    }

    /**
     * This function is used to check if quote is expired
     * @param  [INT]  $enquiry_id [enquiry ID]
     * @return boolean             [true if quote is expired]
     */
    public function isQuoteExpired($enquiry_id)
    {
        global $wpdb;
        $table = getEnquiryDetailsTable();
        $expirationDate = $wpdb->get_var($wpdb->prepare("SELECT expiration_date FROM $table WHERE enquiry_id = %d", $enquiry_id));
        if ($expirationDate == null || empty($expirationDate) || $expirationDate == '0000-00-00 00:00:00') {
            return false;
        } else {
            $currentTime = strtotime(current_time('Y-m-d').' 00:00:00');
            $expirationTime = strtotime($expirationDate);
            if ($currentTime > $expirationTime) {
                return true;
            }
        }

        return false;
    }
}
$this->manageExpiration = QuoteupManageExpiration::getInstance();

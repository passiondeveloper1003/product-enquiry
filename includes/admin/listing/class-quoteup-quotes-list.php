<?php

namespace Includes\Admin;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('Abstracts\QuoteupList')) {
        // This file Handles the Bulk actions for the enquiry list table.
    require_once QUOTEUP_PLUGIN_DIR.'/includes/admin/abstracts/class-abstract-quoteup-list.php';
}

/**
* This class is to separate the enquiries on basis of their history status.
* It craetes the status links and add the enquiries of corresponding status to them.
*/
class QuoteupQuotesList extends Abstracts\QuoteupList
{
    public $countFilter;

    /**
    * Get the enquiries as per status .
    * @return array Enquiries with specific status
    */
    protected function statusSpecificEnquiries()
    {
        $enquiries = array();

        if (!isset($_GET['status'])) {
            return $enquiries;
        }

        $filter = filter_var($_GET['status'], FILTER_SANITIZE_STRING);

        global $wpdb;
        $tableName  = getEnquiryHistoryTable();
        $columnName = 's1.enquiry_id';
 
        if (isset($filter) && 'admin-created' == $filter) {
            $metaTableName = getEnquiryMetaTable();
            $sql = "SELECT enquiry_id FROM $metaTableName WHERE meta_key = '_admin_quote_created'";
            $columnName = 'enquiry_id';
        } elseif ($filter == "saved") {
            $sql = "SELECT s1.enquiry_id
                FROM $tableName s1
                LEFT JOIN $tableName s2 ON s1.enquiry_id = s2.enquiry_id
                AND s1.id < s2.id
                WHERE s2.enquiry_id IS NULL AND (s1.status ='".$filter."' OR s1.status ='Quote Created') AND s1.enquiry_id > 0 AND s1.ID > 0";
        } else {
            $sql = "SELECT s1.enquiry_id
                FROM $tableName s1
                LEFT JOIN $tableName s2 ON s1.enquiry_id = s2.enquiry_id
                AND s1.id < s2.id
                WHERE s2.enquiry_id IS NULL AND s1.status ='".$filter."'AND s1.enquiry_id > 0 AND s1.ID > 0";
        }

        $sql = apply_filters('quoteup_status_specific_enq_query', $sql, $columnName, $filter);
        return $wpdb->get_col($sql);
    }




    /**
    * Gets the status icon image for each status.
    * @param string $status enquiry status
    * @return string html HTML with image url of status icon
    */
    public function getStatusImage($status)
    {
        $spanTag = "<span class = 'status-span'>";
        $closeSpanImageTag = "</span><img class='status-image' title = '";
        $title = '';
        $imgSrc2 = '';
        $imgSrc = "' src = ".QUOTEUP_PLUGIN_URL.'/images/';
        //Create image name for status image
        $imgSrc2 = strtolower($status).'.png>'; //convert name to lower
        $imgSrc2 = str_replace(" ", "-", $imgSrc2); // replace space with -
        //End of create image name for status image
        
        // Using array to make title translation ready
        $statusArray = array(
            'Quote Created' => __('Quote Created', QUOTEUP_TEXT_DOMAIN),
            'Requested' => __('Requested', QUOTEUP_TEXT_DOMAIN),
            'Saved' => __('Saved', QUOTEUP_TEXT_DOMAIN),
            'Sent' => __('Sent', QUOTEUP_TEXT_DOMAIN),
            'Approved' => __('Approved', QUOTEUP_TEXT_DOMAIN),
            'Rejected' => __('Rejected', QUOTEUP_TEXT_DOMAIN),
            'Order Placed' => __('Order Placed', QUOTEUP_TEXT_DOMAIN),
            'Expired' => __('Expired', QUOTEUP_TEXT_DOMAIN),
        );
        $title= $statusArray[$status];
        
        return $spanTag.$title.$closeSpanImageTag.$title.$imgSrc.$imgSrc2;
    }

    /**
    * Divide the Enquiries as per their status.
    * Get the enquiry status from history table.
    * Display the links for the status to the particular which shows enquiries of that status.
    * Count the enquiries of every status
    * @return string array $statusLinks status Links for enquiries.
    */
    public function get_views()
    {
        $countAll = $countRequested = $countSaved = $countSent = $countApproved = $countRejected = $countPlaced = $countExpired = $countAdminQuote = 0;

        global $wpdb;
        $tableName = getEnquiryHistoryTable();
        $metaTableName = getEnquiryMetaTable();

        $sql = "SELECT s1.status,COUNT(s1.enquiry_id) AS EnquiryCount FROM $tableName s1 LEFT JOIN $tableName s2 ON s1.enquiry_id = s2.enquiry_id AND s1.id < s2.id WHERE s2.enquiry_id IS NULL AND s1.status IN ('requested','saved','sent','approved','rejected','Order Placed','expired','Quote Created') AND s1.enquiry_id > 0 AND s1.ID > 0 ";
        $sql = apply_filters('quoteup_enquiry_status_add_where_clause', $sql, 's1.enquiry_id');
        $sql .= ' GROUP BY s1.status';

        $res = $wpdb->get_results($sql, ARRAY_A);
        $sql1 = "SELECT COUNT(enquiry_id) AS EnquiryCount FROM $metaTableName WHERE meta_key = '_admin_quote_created' ";
        $sql1 = apply_filters('quoteup_admin_created_quote_where_clause', $sql1, 'enquiry_id');

        $res1 = $wpdb->get_var($sql1);
        $adminCreatedQuoteArray = array(
            'status' => 'admin-created',
            'EnquiryCount' => $res1,
            );
        array_push($res, $adminCreatedQuoteArray);
        $this->countFilter = $res;

        self::getCount($res, $countAll, $countRequested, $countSaved, $countSent, $countApproved, $countRejected, $countPlaced, $countExpired, $countAdminQuote);

        $requestedURL = get_admin_url('', 'admin.php?page=quoteup-details-new');
        $currentAll = $currentRequested = $currentSaved = $currentSent = $currentApproved = $currentRejected = $currentPlaced = $currentExpired = $currentAdminCreated = '';

        $statusDisplayName = array(
            'all' => __('All', QUOTEUP_TEXT_DOMAIN),
            'requested' => __('Requested', QUOTEUP_TEXT_DOMAIN),
            'saved' => __('Saved', QUOTEUP_TEXT_DOMAIN),
            'sent' => __('Sent', QUOTEUP_TEXT_DOMAIN),
            'approved' => __('Approved', QUOTEUP_TEXT_DOMAIN),
            'rejected' => __('Rejected', QUOTEUP_TEXT_DOMAIN),
            'Order Placed' => __('Order Placed', QUOTEUP_TEXT_DOMAIN),
            'expired' => __('Expired', QUOTEUP_TEXT_DOMAIN),
            'admin-created' => __('Admin Created Quotes', QUOTEUP_TEXT_DOMAIN),
        );
        $statusDisplayName = apply_filters('quoteup_enq_list_status_display_name', $statusDisplayName);

        if (isset($_GET['status'])) {
            switch ($_GET['status']) {
                case 'requested':
                    $currentRequested = 'current';
                    break;

                case 'saved':
                    $currentSaved = 'current';
                    break;

                case 'sent':
                    $currentSent = 'current';
                    break;

                case 'approved':
                    $currentApproved = 'current';
                    break;

                case 'rejected':
                    $currentRejected = 'current';
                    break;

                case 'Order Placed':
                    $currentPlaced = 'current';
                    break;

                case 'expired':
                    $currentExpired = 'current';
                    break;
                case 'admin-created':
                    $currentAdminCreated = 'current';
                    break;
                default:
                    break;
            }
        } else {
            $currentAll = 'current';
        }
        $status_links = array(
            'all' => "<a class=$currentAll id='all' href='".$requestedURL."'>".$statusDisplayName['all']." <span class='count'>(".$countAll.')</span></a>',
        );
        if ($countRequested>0) {
            $status_links['requested'] = "<a class='".$currentRequested."'  id='requested' href='".$requestedURL."&status=requested'>".$statusDisplayName['requested']."<span class='count'>(".$countRequested.')</span></a>';
        }

        if ($countSaved>0) {
            $status_links['saved'] = "<a class='".$currentSaved."'  id='saved' href='".$requestedURL."&status=saved'>".$statusDisplayName['saved']." <span class='count'>(".$countSaved.')</span></a>';
        }

        if ($countSent>0) {
            $status_links['sent'] = "<a class='".$currentSent."'  id='sent' href='".$requestedURL."&status=sent'>".$statusDisplayName['sent']." <span class='count'>(".$countSent.')</span></a>';
        }

        if ($countApproved>0) {
            $status_links['approved'] = "<a class='".$currentApproved."'  id='approved' href='".$requestedURL."&status=approved'>".$statusDisplayName['approved']." <span class='count'>(".$countApproved.')</span></a>';
        }

        if ($countRejected>0) {
            $status_links['rejected'] = "<a class='".$currentRejected."'  id='rejected' href='".$requestedURL."&status=rejected'>".$statusDisplayName['rejected']." <span class='count'>(".$countRejected.')</span></a>';
        }

        if ($countPlaced>0) {
            $status_links['Order Placed'] = "<a class='".$currentPlaced."'  id='completed' href='".$requestedURL."&status=Order Placed'>".$statusDisplayName['Order Placed']." <span class='count'>(".$countPlaced.')</span></a>';
        }

        if ($countExpired>0) {
            $status_links['expired'] = "<a class='".$currentExpired."' id='expired' href='".$requestedURL."&status=expired'>".$statusDisplayName['expired']." <span class='count'>(".$countExpired.')</span></a>';
        }

        if ($countAdminQuote>0) {
            $status_links['admin'] = "<a class='".$currentAdminCreated."' id='adminCreated' href='".$requestedURL."&status=admin-created'>".$statusDisplayName['admin-created']." <span class='count'>(".$countAdminQuote.')</span></a>';
        }

        return $status_links;
    }

    /**
    * Get the count for every enquiry status.
    * @param array $res All enquiries result
    * @param int &$countAll Count of all enquiries.
    * @param int &$countRequested Count of requested enquiries.
    * @param int &$countSaved Count of saved enquiries.
    * @param int &$countSent Count of sent enquiries.
    * @param int &$countApproved Count of approved enquiries.
    * @param int &$countRejected Count of rejected enquiries.
    * @param int &$countPlaced Count of placed enquiries.
    * @param int &$countExpired Count of expired enquiries.
    * @param int &$countAdminQuote Count of admin created quotes.
    */
    public static function getCount($res, &$countAll, &$countRequested, &$countSaved, &$countSent, &$countApproved, &$countRejected, &$countPlaced, &$countExpired, &$countAdminQuote)
    {
        foreach ($res as $key) {
            switch ($key['status']) {
                case 'Requested':
                    $countRequested = $key['EnquiryCount'];
                    $countAll = $countAll + $countRequested;
                    break;

                case 'Saved':
                    $countSaved = $countSaved + $key['EnquiryCount'];
                    $countAll = $countAll + $key['EnquiryCount'];
                    break;

                case 'Sent':
                    $countSent = $key['EnquiryCount'];
                    $countAll = $countAll + $countSent;
                    break;

                case 'Approved':
                    $countApproved = $key['EnquiryCount'];
                    $countAll = $countAll + $countApproved;
                    break;

                case 'Rejected':
                    $countRejected = $key['EnquiryCount'];
                    $countAll = $countAll + $countRejected;
                    break;

                case 'Order Placed':
                    $countPlaced = $key['EnquiryCount'];
                    $countAll = $countAll + $countPlaced;
                    break;

                case 'Expired':
                    $countExpired = $key['EnquiryCount'];
                    $countAll = $countAll + $countExpired;
                    break;

                case 'Quote Created':
                    $countSaved = $countSaved + $key['EnquiryCount'];
                    $countAll = $countAll + $key['EnquiryCount'];
                    break;

                case 'admin-created':
                    $countAdminQuote = $key['EnquiryCount'];
                    break;
                default:
                    break;
            }
        }
    }

    /*
     * text to be displayed when there are no records
     */
    public function noItems()
    {
        _e('No enquiry & quote details available.', QUOTEUP_TEXT_DOMAIN);
    }

    /**
    * displays row actions for each row
    * @param int $enquiryId Enquiry Id.
    * @param string $adminPath admin url
    * @param int $currentPage current page no.
    * @return array $actions actions to be performed for rows
    */
    public function rowActions($enquiryId, $adminPath, $currentPage)
    {
        return array(
            'edit' => sprintf('<a href="%sadmin.php?page=%s&id=%s">%s</a>', $adminPath, 'quoteup-details-edit', $enquiryId, __('Edit', QUOTEUP_TEXT_DOMAIN))
        );
    }


    /**
    * Returns column titles for the page
    * @return array $columns column titles
    */
    public function columnsTitles()
    {
        $columns = array(
            'status' => __('Status', QUOTEUP_TEXT_DOMAIN),
            'product_details' => __('Item', QUOTEUP_TEXT_DOMAIN),
            'name' => __('Customer Name', QUOTEUP_TEXT_DOMAIN),
            'email' => __('Customer Email', QUOTEUP_TEXT_DOMAIN),
            'enquiry_date' => __('Enquiry Date', QUOTEUP_TEXT_DOMAIN),
            'total' => __('Total', QUOTEUP_TEXT_DOMAIN),
            'order_number' => __('Order #', QUOTEUP_TEXT_DOMAIN),
        );

        return apply_filters('wdm_quote_list_columns', $columns);
    }


    /**
    * Get the columns which are sortable.
    * @return array $sortableColumns sortable column list
    */
    public function sortableColumns()
    {
        $sortableColumns = array(
            'enquiry_id' => array('enquiry_id', false),
            'name' => array('name', true),
            'email' => array('email', true),
            'enquiry_date' => array('enquiry_date', true),
        );

        return apply_filters('quoteup_quotes_get_sortable_columns', $sortableColumns);
    }

     /**
    * Returns the status of enquiry
    * @param array $item Enquiry details
    * @return string html Status icon url image tag
    */
    public function displayStatus($item)
    {
        global $quoteupManageHistory;
        $status = $quoteupManageHistory->getLastAddedHistory($item['enquiry_id']);
        return $this->getStatusImage($status['status']);
    }

    /**
     * Returns the total price of products in enquiry
     * @param array $item Enquiry details
     * @return int total of Product price
     */
    public function displayTotal($item)
    {
        if (empty($item['total'])) {
            global $wpdb;
            $databaseProducts = array();
            $enquiryId        = $item['enquiry_id'];
            $enquiryTableName = getEnquiryDetailsTable();

            if (isQuotationModuleEnabled()) {
                $databaseProducts = getQuoteProducts($enquiryId);
            }

            if (empty($databaseProducts)) {
                $databaseProducts = getEnquiryProducts($enquiryId);
            }

            $total = quoteupReturnSumOfProductPrices($databaseProducts);

            //Save total price enquiry_detail_new table
            $wpdb->update(
                $enquiryTableName,
                array(
                    'total' => $total,
                ),
                array(
                    'enquiry_id' => $enquiryId,
                )
            );
        } else {
            $total = wc_price($item['total']);
        }
        
        /**
         * Filter to modify the total price of products in enquiry.
         *
         * @param  float  $total  The total price of products.
         * @param  array  $item  Enquiry details
         */
        $total = apply_filters('quote_total_on_enquiry_list', $total, $item);
        return $total;
    }

    /**
    * Returns the url to the order id of enquiry.
    * @param array $item Enquiry details
    * @return string $orderid Return url to the order id.
    */
    public function displayOrderNumber($item)
    {
        $enquiryid = $item['enquiry_id'];
        $orderid   = \Includes\QuoteupOrderQuoteMapping::getOrderIdOfQuote($enquiryid);
        if ('0' == $orderid ||  null == $orderid) {
            $orderid = '-';
        }
 
        /**
         * Use this filter to modify the order Id associate with quote.
         *
         * @param int $orderid      Order Id.
         * @param int $enquiryid    Enquiry Id.
         */
        $orderid = apply_filters('order_id_ass_with_quote_on_enq_list', $orderid, $enquiryid);

        if (empty($orderid) || '-' == $orderid) {
            return $orderid;
        }

        $orderid = '<a href="'.admin_url('post.php?post='.absint($orderid).'&action=edit').'" >'.$orderid.'</a>';
        return $orderid;
    }
}

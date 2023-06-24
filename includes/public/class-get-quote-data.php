<?php
namespace Includes\Frontend;

/**
 * This class is used to fetch quotation details to be displayed at front end.
 *
 * This class is used paginate and search through quotation detail.
 *
 * Get total price and quantity.
 */
class QuoteUpGetQuoteData
{
    public $quoteid;
    public $pagelink;
   
    /**
     * This function is used to get Quotation details.
     *
     * @param  [int] $limit     [number of quotation to be displayed]
     *
     * @param  [int] $offset    [offset for quotation]
     *
     * @param  [String] $search [search throughout a row]
     *
     * @param  [String] $order  [sort quotation data by]
     *
     * @return [Array]          [Quotation details]
     */
    public function getQuote($limit, $offset, $search, $order = '')
    {
        global $wpdb;
        $userid = get_current_user_id();
        $search_query = '';
        $historytable = getEnquiryHistoryTable();

        if ('' != $search) {
            $formData = get_option('wdm_form_data'); // PEP settings

            // if quotation module is active
            if (!isset($formData['enable_disable_quote']) || empty($formData['enable_disable_quote'])) {
                $search_query="and CONCAT_ws('-',status, DATE_FORMAT(date,'%M %d,%Y'), enquiry_id) like '%".$search."%'";
            } else {
                $search_query="and CONCAT_ws('-', DATE_FORMAT(date,'%M %d,%Y'), enquiry_id) like '%".$search."%'";
            }
        }
        
        if (! empty($order) && is_numeric($order[0]['column'])) {
            $order_query = (int) $order[0]['column'] + 1 . ' ' . $order[0]['dir'];
        } else {
            $order_query = $historytable . '.enquiry_id DESC ';
        }

        $qry = "SELECT enquiry_id, date, status FROM $historytable WHERE id IN (SELECT max(id) from $historytable WHERE enquiry_id IN (SELECT DISTINCT enquiry_id FROM $historytable WHERE performed_by = $userid) GROUP BY enquiry_id) ".$search_query." ORDER BY $order_query LIMIT $offset, $limit";

        $results=$wpdb->get_results($qry);
        return $results;
    }

    /**
     * This function returns number of found quotation rows.
     *
     * @param  [String] $search [search for particular quotation]
     *
     * @return [int]            [number of quotation found]
     */
    public function countQuote($search)
    {
        global $wpdb;
        $userid=get_current_user_id();
        $historytable=getEnquiryHistoryTable();
        $search_query='';

        if ('' != $search) {
            $formData = get_option('wdm_form_data'); // PEP settings

            // if quotation module is active
            if (!isset($formData['enable_disable_quote']) || empty($formData['enable_disable_quote'])) {
                $search_query="and CONCAT_ws('-',status, DATE_FORMAT(date,'%M %d,%Y'), enquiry_id) like '%".$search."%'";
            } else {
                $search_query="and CONCAT_ws('-', DATE_FORMAT(date,'%M %d,%Y'), enquiry_id) like '%".$search."%'";
            }
        }

        $qry = "SELECT count(*) FROM $historytable WHERE id IN (SELECT max(id) from $historytable WHERE enquiry_id IN (SELECT DISTINCT enquiry_id FROM $historytable WHERE performed_by = $userid) GROUP BY enquiry_id) ".$search_query;

        $count = $wpdb->get_var($qry);
        return $count;
    }

    /**
     * Returns the total price and quantity for a particular enquiry.
     *
     * @param  int      $enquiryId              Enquiry ID
     * @param  bool     $isQuotationModActive   Is quotation module active
     *
     * @return array    Returns an array containing the total price, quantity and
     *                  disable price (indicated whether enquiry contains any Price
     *                  Hidden Product) for a particular enquiry.
     */
    public static function getQuoteProdPriceQty($enquiryId, $isQuotationModActive)
    {
        $quoteTotPriceQty = array(
            'disable-price' => false,
        );
        // If quotation module is activated
        if ($isQuotationModActive) {
            $databaseProducts = getQuoteProducts($enquiryId);
            if (empty($databaseProducts)) {
                $databaseProducts = getEnquiryProducts($enquiryId);
            }
        } else {
            $databaseProducts = getEnquiryProducts($enquiryId);
        }

        $totalPrice = 0;
        $totalQuantity = 0;

        foreach ($databaseProducts as $product) {
            $enable_price = quoteupShouldHidePrice($product['product_id']) ? '' : 'yes';

            // If any product has price hidden, then don't show the quote/ enquiry price.
            if (empty($enable_price)) {
                $quoteTotPriceQty = array(
                    'disable-price' => true,
                );
            }
            $price = isset($product['newprice']) ? $product['newprice'] : $product['price'];
            $totalPrice = $totalPrice + ($price * $product['quantity']);
            $totalQuantity += $product['quantity'];
        }
        
        $quoteTotPriceQty['total-price'] = $totalPrice;
        $quoteTotPriceQty['total-quantity'] = $totalQuantity;
        
        return $quoteTotPriceQty;
    }
}

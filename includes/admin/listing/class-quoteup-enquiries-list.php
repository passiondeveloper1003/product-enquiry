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
* This class gets the column titles for enquiry list page.
* Gives which are sortable columns are not.
* Display the row actions for each row.
*/
class QuoteupEnquiriesList extends Abstracts\QuoteupList
{
    public $countFilter;

    /**
    * Function for applying filter for the column titles in enquiries list.
    */
    public function columnsTitles(){

        $columns = array(
                'product_details' => __('Item', QUOTEUP_TEXT_DOMAIN),
                'name' => __('Customer Name', QUOTEUP_TEXT_DOMAIN),
                'email' => __('Customer Email', QUOTEUP_TEXT_DOMAIN),
                'enquiry_date' => __('Enquiry Date', QUOTEUP_TEXT_DOMAIN),
                'message' => __('Message', QUOTEUP_TEXT_DOMAIN),
            );

            return apply_filters('wdm_enquiry_list_columns', $columns);
    }

    /**
    * displays row actions for each row
    * @param int $enquiryId Enquiry Id.
    * @param string $adminPath admin url
    * @param int $currentPage current page no.
    * @return array $actions actions to be performed for rows
    */
    public function rowActions($enquiryId, $adminPath, $currentPage){

            return array(
                'edit' => sprintf('<a href="%sadmin.php?page=%s&id=%s">%s</a>', $adminPath, 'quoteup-details-edit', $enquiryId, __('Edit', QUOTEUP_TEXT_DOMAIN))
            );
    }

    /*
     * text to be displayed when there are no records
     */
    public function noItems()
    {
        _e('No enquiries avaliable.', QUOTEUP_TEXT_DOMAIN);
    }

    /**
    * Get the columns which are sortable.
    * @return array $sortable_columns sortable column list
    */
    public function sortableColumns()
    {
        $sortable_columns = array(
            'enquiry_id' => array('enquiry_id', false),
            'name' => array('name', true),
            'email' => array('email', true),
            'enquiry_date' => array('enquiry_date', true),
        );

        return apply_filters('quoteup_enquiries_get_sortable_columns', $sortable_columns);
    }

    /**
    * Returns the message in enquiry
    * @param array $item Enquiry details
    * @return string message in enquiry
    */
    public function displayMessage($item){
        return $item['message'];
    }
}

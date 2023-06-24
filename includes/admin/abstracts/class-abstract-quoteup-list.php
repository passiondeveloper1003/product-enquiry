<?php

namespace Includes\Admin\Abstracts;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH.'wp-admin/includes/class-wp-list-table.php';
}

/**
* Handles the Bulk actions for the enquiry list table.
* $perPageEnquiries Per page enquiries.
* $pageNumber page number (in case of pagination.)
* $totalResults total results for enquiries.
* $sendback url for sendback
*/
abstract class QuoteupList extends \WP_List_Table
{
    public $perPageEnquiries = 10;

    public $pageNumber = 1;

    public $totalResults = 0;

    public $sendback = '';

    /*
     * text to be displayed when there are no records
     */
    abstract public function noItems();

    abstract public function sortableColumns();

    abstract protected function columnsTitles();

    /**
    * For the quotes list gets the displaynames in headers.
    * @param array $displayNames display names
    */
    public function __construct($displayNames = array())
    {
        if (empty($displayNames)) {
            // $displayNames = array(
            //         'singular' => __('Enquiry', QUOTEUP_TEXT_DOMAIN)/* singular name of the listed records */,
            //         'plural' => __('Enquiries', QUOTEUP_TEXT_DOMAIN), /* plural name of the listed records */
            // );
            $displayNames = array(
                'singular' => 'Enquiry',
                'plural' => 'Enquiries',
            );
        }

        parent::__construct($displayNames);

        add_filter('removable_query_args', array($this, 'customRemovableArgs'), 10, 1);
        add_filter('quoteup_enquiry_list_base_query', array($this, 'modifyBaseQueryForEnquirySearch'));
    }
    /**

     * To delete a single row of enquiry.
     * Checks nonce and then deletes
    */
    protected function _processRowAction()
    {
        //Detect when a single delete is being triggered...
        if ('delete' === $this->current_action()) {
            $nonce = esc_attr($_REQUEST[ '_wpnonce' ]);

            if (!wp_verify_nonce($nonce, 'wdm_enquiry_actions')) {
                die('Go get a life script kiddies');
            } else {
                /**
                 * Use the hook to Validate enquiry on delete action.
                 *
                 * @param  int  $id  Enquiry Id.
                 */
                do_action('quoteup_validate_enquiry_delete_action', $_REQUEST['id']);

                $this->_deleteEnquiry(absint($_GET[ 'id' ]));
                echo "<div class='updated'><p>Enquiry is deleted successfully</p></div>";
            }
        }

        if (method_exists($this, 'processRowAction')) {
            $this->processRowAction();
        }
    }
    /**
    * Process bulk action for current page.
    * If delete delete enquiries else do something else.
    * @param int $currentPage Page id
    * @param string $sendback url of page
    */
    public function _processBulkAction($currentPage, $sendback)
    {
        $this->sendback = add_query_arg('paged', $currentPage, $sendback);
        $this->_delteEnquiriesInBulk();
        /**
         * Processing bulk action.
         *
         * @param  $currentPage  Current Page Id.
         * @param  $sendback  url of page
         */
        do_action('quoteup_process_bulk_action', $currentPage, $sendback);
        if (method_exists($this, 'processBulkAction')) {
            $this->processBulkAction($currentPage, $sendback);
        }
    }
/**
* If it is a bulk delete of enquiries.
* take the selected enquiries and delete them.
*/
    public function _delteEnquiriesInBulk()
    {
        // If the delete bulk action is triggered
        if ((isset($_POST[ 'action' ]) && $_POST[ 'action' ] == 'bulk-delete') || (isset($_POST[ 'action2' ]) && $_POST[ 'action2' ] == 'bulk-delete')
            ) {
            check_admin_referer('bulk-enquiries');

            if (isset($_POST[ 'bulk-select' ])) {
                $delete_ids = esc_sql($_POST[ 'bulk-select' ]);

                // loop over the array of record IDs and delete them
                foreach ($delete_ids as $id) {
                    $id = strip_tags($id);
                    $this->_deleteEnquiry($id);
                }
                $count = count($delete_ids);
                $this->sendback = add_query_arg('bulk-delete', $count, $this->sendback);
            } else {
                $this->sendback = add_query_arg('selectnone', 'yes', $this->sendback);
            }

            if ($this->current_action()) {
                wp_redirect($this->sendback);
                exit;
            }
        }
    }
    /**
    * When bulk enquiries deletes add message of bulk enquiries deleted, else error.
    */
    public function _bulkActionNotices()
    {
        if (!$this->current_action()) {
            $args = $_GET;
            foreach ($args as $key => $value) {
                switch ($key) {
                    case 'bulk-delete':
                        echo "<div class='updated'><p> $value ".__('enquiries are deleted', QUOTEUP_TEXT_DOMAIN).'</p></div>';
                        break;

                    case 'selectnone':
                        echo "<div class='error'><p>".__('Select Enquiries to delete', QUOTEUP_TEXT_DOMAIN).'</p></div>';
                        break;
                    default:
                        /**
                         * Use the hook to add notice for bulk action.
                         *
                         * @param  string  $key  $_GET variable key.
                         * @param  string  $value  $_GET variable value.
                         */
                        do_action('quoteup_bulk_action_notices', $key, $value);
                        break;
                }
            }
        }

        if (method_exists($this, 'bulkActionNotices')) {
            $this->bulkActionNotices();
        }
    }
    /**
    * Gets the table name.
    * @return Enquiry table name
    */
    protected function table()
    {
        global $wpdb;

        return "{$wpdb->prefix}enquiry_detail_new";
    }

    /**
     * This function add arguments which should be removed from URL on load.
     *
     * @param [array] $removableQueryArgs [remove args]
     * @return apply filters on removable query arguments
     */
    public function customRemovableArgs($removableQueryArgs)
    {
        $customArgs = array('action', 'id', '_wpnonce', 'bulk-delete');

        $removableQueryArgs = array_merge($removableQueryArgs, $customArgs);

        return apply_filters('quoteup_add_custom_removable_args', $removableQueryArgs);
    }

    /**
    * Get the enquiries which are searched for.
    * @param int $perPageEnquiries Enquiries on per page.
    * @param int $pageNumber page number.
    * @return array $finalResults results which are searched
    */
    public function getEnquiries($perPageEnquiries = 10, $pageNumber = 1)
    {
        global $wpdb;

        $this->convertPostSearchToGet();

        $this->perPageEnquiries = $perPageEnquiries;
        $this->pageNumber = $pageNumber;

        $finalResults = array();

        $sql = $this->createDbSelectQuery();

        if (empty($sql)) {
            return;
        }

        $results = $wpdb->get_results($sql, 'ARRAY_A');

        if ($results) {
            $this->setTotalResultsFound($results);

            foreach ($results as $result) {
                $currentData = array();
                $currentData['enquiry_id'] = isset($result['enquiry_id']) ? $result['enquiry_id'] : '';

                foreach ($this->columnsTitles() as $columnSlug => $columnTitle) {
                    $currentData[$columnSlug] = call_user_func_array(array($this, 'display'.$this->toCamelCase($columnSlug)), array($result));
                    unset($columnTitle);
                }

                $finalResults[] = apply_filters('enquiry_list_table_data', $currentData, $result);
            }
        }

        return $finalResults;
    }

    /**
    * Converts string to Camel Case.
    * @param string $string string to be converted.
    * @return string converted string
    */
    protected function toCamelCase($string)
    {
        $string = str_replace('-', ' ', $string);
        $string = str_replace('_', ' ', $string);
        $string = ucwords(strtolower($string));
        return str_replace(' ', '', $string);
    }

    /**
    * Set the count of total results found
    */
    protected function setTotalResultsFound($results)
    {
        global $wpdb;
            //We will avoid firing another query to calculate total number of results
        if ($this->perPageEnquiries >= 0) {
            $this->totalResults = $wpdb->get_var('SELECT FOUND_ROWS()');
        } else {
            $this->totalResults = count($results);
        }
    }
    /**
    * Gets the url of the Post searched http or https.
    */
    protected function convertPostSearchToGet()
    {
        if (isset($_POST['s']) && $_POST['s'] != '') {
            $searchByParam = isset($_POST['quoteup_enq_search_param']) ? $_POST['quoteup_enq_search_param'] : 'search-by-all';
            $searchTerm    = trim($_POST['s']);
            $actual_link   = (isset($_SERVER['HTTPS']) ? 'https' : 'http')."://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            header("Location: $actual_link&s=$searchTerm&quoteup_enq_search_param=$searchByParam");
        } elseif (isset($_POST['s']) && $_POST['s'] == '') {
            $requestedURL = get_admin_url('', 'admin.php?page=quoteup-details-new');
            header("Location: $requestedURL");
        }
    }
    /**
    * Return the rows or columns based on the action done.
    * Sorts the entries if such action performed.
    */
    protected function createDbSelectQuery()
    {
        global $wpdb;

        $tableName = $this->table();
        $query = array();
        $columns = $this->get_columns();

        if (empty($columns)) {
            return;
        }

        $filteredColumns = array();

        //Check if columns exist in table
        foreach ($columns as $columnSlug => $columnTitle) {
            if ($columnSlug == 'cb' || $columnSlug == 'product_details') {
                continue;
            }

            $columnExists = $wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM {$tableName} LIKE %s", $columnSlug));

            if ($columnExists != null) {
                $filteredColumns[] = $tableName . '.' . $columnSlug;
            }
        }

        $columns = implode(', ', $filteredColumns);

        $foundRows = '';

        //We will avoid firing another query to calculate total number of results
        if ($this->perPageEnquiries >= 0) {
            $foundRows = 'SQL_CALC_FOUND_ROWS';
        }

        $baseQuery = "SELECT {$foundRows} DISTINCT {$columns} FROM {$tableName} ";
        /**
         * Filter to change the enquiry list search query.
         *
         * @since 6.4.4
         *
         * @param   bool    SQL Query to search enquiry list.
         *
         */
        $baseQuery        = apply_filters('quoteup_enquiry_list_base_query', $baseQuery);
        $query[]          = $baseQuery;
        $query['where'][] = $this->appendSearchQuery();
        $enquiryIds       = $this->statusSpecificEnquiries();
        $enquiryIds       = apply_filters('quoteup_enq_quote_ids', $enquiryIds);

        if ($enquiryIds === -1) {
            $query['where'][] = "enquiry_id = 0";
        } elseif (!empty($enquiryIds)) {
            $enquiryIds = implode(', ', $enquiryIds);
            $query['where'][] = "enquiry_id IN ({$enquiryIds})";
        }

        $query['where'] = 'WHERE ' . implode(' AND ', array_filter($query['where']));

        if ($query['where'] == 'WHERE ') {
            unset($query['where']);
        }

        $query[] = $this->orderByQuery();
        $query[] = $this->limitQuery();
        $query[] = $this->offsetQuery();

        //Remove blank entries from array
        $query = array_filter($query);

        return implode(' ', $query);
    }

    /**
    * This arranges the enquiries in ascending or descending order whatever required by user.
    * @return string $sql sql for order by query
    */
    protected function orderByQuery()
    {
        $sql       = '';
        $tableName = $this->table();
        if (!empty($_REQUEST['orderby'])) {
            $sql = "ORDER BY $tableName.".esc_sql($_REQUEST['orderby']);
            $sql .= !empty($_REQUEST['order']) ? ' '.esc_sql($_REQUEST['order']) : ' ASC';
        } else {
            $sql = 'ORDER BY ' . $tableName .'.enquiry_id DESC';
        }

        return $sql;
    }

    /**
    * Limits the per page enquiries, returns the query part
    * @return string Limit query
    */
    protected function limitQuery()
    {
        return 'LIMIT '.$this->perPageEnquiries;
    }

    /**
    * Enquiries divided in offset depending on per page enquiries.
    * @return string offset query
    */
    protected function offsetQuery()
    {
        return 'OFFSET '.($this->pageNumber - 1) * $this->perPageEnquiries;
    }

    /**
    * If it is search request return the searched entries.
    * @return string search query
    */
    protected function appendSearchQuery()
    {
        $searchQuery = $searchTerm = $searchByParam = '';

        if ($this->isSearchRequest()) {
            $searchTerm = $this->getSearchTerm();
            if (!empty($searchTerm)) {
                $searchByParam = $this->getSelectedSearchByParam();

                switch ($searchByParam) {
                    case 'enq-id':
                        if (is_numeric($searchTerm)) {
                            $searchQuery = " enquiry_id = {$searchTerm} ";
                        } else {
                            $searchQuery = " enquiry_id = -1 ";
                        }
                        break;
                    
                    case 'cust-name':
                        $searchQuery = " name LIKE '%{$searchTerm}%' ";
                        break;
                    
                    case 'cust-email':
                        $searchQuery = " email LIKE '%{$searchTerm}%' ";
                        break;

                    case 'product-name':
                        $enquiryProductsTable = getEnquiryProductsTable();
                        $searchQuery          = " $enquiryProductsTable.product_title LIKE '%{$searchTerm}%' ";

                        if (isQuotationModuleEnabled()) {
                            $enquiryQuotationTable = getQuotationProductsTable();
                            $searchQuery          .= " OR $enquiryQuotationTable.product_title LIKE '%{$searchTerm}%' ";
                        }
                        break;
                    
                    default: // enquivalent to 'search-by-all' parameter
                        $enquiryProductsTable   = getEnquiryProductsTable();
                        
                        if (is_numeric($searchTerm)) {
                            $enquiryDetailsNewTable = getEnquiryDetailsTable();
                            $searchQuery            = " $enquiryDetailsNewTable.enquiry_id = {$searchTerm} OR ";
                        }
                    
                        $searchQuery .= " name LIKE '%{$searchTerm}%' OR email LIKE '%{$searchTerm}%' ";
                        $searchQuery .= " OR $enquiryProductsTable.product_title LIKE '%{$searchTerm}%' ";

                        if (isQuotationModuleEnabled()) {
                            $enquiryQuotationTable = getQuotationProductsTable();
                            $searchQuery          .= " OR $enquiryQuotationTable.product_title LIKE '%{$searchTerm}%' ";
                        }
                        break;
                }
            }
        }

        /**
         * Filter to change the where clause query.
         *
         * @since 6.4.4
         *
         * @param string $searchQuery String containing where clause query.
         * @param string $searchTerm  String containing search term/ keyword.
         * @param string $searchByParam String containing search by Parameter.
         *
         */
        $searchQuery = apply_filters('quoteup_enquiry_list_where_clause', $searchQuery, $searchTerm, $searchByParam);
        return $searchQuery;
    }
    /**
    * Search is selected.
    * @return serach selected or not
    */
    protected function isSearchRequest()
    {
        return isset($_GET['s']) && (trim($_GET['s']));
    }

    protected function statusSpecificEnquiries()
    {
        return array();
    }

    /**
     * Returns the Search term/ keyword.
     *
     * @return string Return the searched term/ keyword.
     */
    protected function getSearchTerm()
    {
        if (isset($_GET['s']) &&  '' !== $_GET['s']) {
            return filter_var(trim($_GET['s']), FILTER_SANITIZE_STRING);
        } else {
            return '';
        }
    }

    /**
     * This function is used to delete enquiry from database.
     *
     * @param [int] $enquiry_id [enquiry id to be deleted]
     *
     * @return [type] [description]
     */
    public function _deleteEnquiry($enquiryId)
    {
        global $wpdb;

        /**
         * Before deleting the enquiry.
         *
         * @param  int  $enquiryId  Enquiry Id.
         */
        do_action('quoteup_before_enquiry_delete_enquiry', $enquiryId);

        $wpdb->delete(getEnquiryDetailsTable(), array('enquiry_id' => $enquiryId), array('%d'));
        $wpdb->delete(getEnquiryHistoryTable(), array('enquiry_id' => $enquiryId), array('%d'));
        $wpdb->delete(getEnquiryMetaTable(), array('enquiry_id' => $enquiryId), array('%d'));
        $wpdb->delete(getEnquiryProductsTable(), array('enquiry_id' => $enquiryId), array('%d'));
        $wpdb->delete(getQuotationProductsTable(), array('enquiry_id' => $enquiryId), array('%d'));
        $wpdb->delete(getEnquiryThreadTable(), array('enquiry_id' => $enquiryId), array('%d'));
        $wpdb->delete(getVersionTable(), array('enquiry_id' => $enquiryId), array('%d'));

        /**
         * After deleting the enquiry.
         *
         * @param  int  $enquiryId  Enquiry Id.
         */
        do_action('quoteup_after_enquiry_delete_enquiry', $enquiryId);
    }

    /**
    * Specify to actions that may be required for the rows of enquiries.
    * @param int $enquiryId Enquiry Id.
    * @param string $adminPath admin url
    * @param int $currentPage current page no.
    * @return array $actions actions to be performed for rows
    */
    public function _rowActions($enquiryId, $adminPath, $currentPage)
    {
        $nonce = wp_create_nonce('wdm_enquiry_actions');

        $actions = array(
                'delete' => sprintf('<a href="?page=%s&paged=%s&action=%s&id=%s&_wpnonce=%s">%s</a>', esc_attr($_REQUEST[ 'page' ]), $currentPage, 'delete', $enquiryId, $nonce, __('Delete', QUOTEUP_TEXT_DOMAIN)),
            );

        if (method_exists($this, 'rowActions')) {
            $actions = array_merge($this->rowActions($enquiryId, $adminPath, $currentPage), $actions);
        }

        $actions = apply_filters('quoteup_enquiry_actions_cap', $actions, $enquiryId, $adminPath, $currentPage);
        return $actions;
    }

    /**
    * Return the customer name.
    * @param array $item Enquiry details.
    * @return string customer name
    */
    public function displayName($item)
    {
        return $item['name'];
    }


    /**
    * Return the customer email.
    * @param array $item Enquiry details.
    * @return string customer email
    */
    public function displayEmail($item)
    {
        return $item['email'];
    }


    /**
    * Return the enquiry date.
    * @param array $item Enquiry details.
    * @return date Enquiry date
    */
    public function displayEnquiryDate($item)
    {
        return $item['enquiry_date'];
    }


    /**
    * Return the checkbox for bulk selection.
    * @param array $item Enquiry details.
    * @return string HTML checkbox for bulk selection
    */
    public function column_cb($item)
    {
        $enquiry_id = strip_tags($item[ 'enquiry_id' ]);

        return sprintf('<input type="checkbox" name="bulk-select[]" pr-id="%d" value="%s" />', $enquiry_id, $item[ 'enquiry_id' ]);
    }


    /**
    * Return the data for the column
    * @param array $item Enquiry details.
    * @param string $coulmn_name Column name
    * @return string
    */
    public function column_default($item, $column_name)
    {
        return $item[ $column_name ];
    }


    /**
    * Returns the column for enquiry with Enquiry Id and the row actions
    * @param array $item Enquiry details.
    * @return string column data
    */
    public function column_enquiry_id($item)
    {
        $enquiryId = strip_tags($item[ 'enquiry_id' ]);
        $adminPath = get_admin_url();
        $currentPage = $this->get_pagenum();
        $actions = $this->_rowActions($enquiryId, $adminPath, $currentPage);

        return sprintf('%s %s', $item[ 'enquiry_id' ], $this->row_actions($actions));
    }

    /**
    * Return message if no enquiries available.
    * @return string message for no enquiries.
    */
    public function no_items()
    {
        return $this->noItems();
    }
    /**
    * Gets the hidden columns for the list page.
    * @return array $hidden_columns hidden columns on page.
    */
    public function get_hidden_columns()
    {
        $hidden_columns = get_user_option('managetoplevel_page_quoteup-details-newcolumnshidden');
        if (!$hidden_columns) {
            $hidden_columns = array();
        }

        return $hidden_columns;
    }
    /**
    * Gets the columns for the list page.
    */
    public function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'enquiry_id' => __('ID', QUOTEUP_TEXT_DOMAIN),
        );

        $columns = array_merge($columns, $this->columnsTitles());

        return apply_filters('wdm_enquiry_quote_list_columns', $columns);
    }

    /**
    * Prepare all items on the page.
    */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        /* Process bulk action */
        $this->process_bulk_action();
        $this->views();

        $perPage = $this->get_items_per_page('request_per_page', 10);
        $currentPage = $this->get_pagenum();
        $this->items = $this->getEnquiries($perPage, $currentPage);

        $this->set_pagination_args(array(
                'total_items' => $this->totalResults, //WE have to calculate the total number of items
                'per_page' => $perPage, //WE have to determine how many items to show on a page
            ));
    }
    /**
    * Gets the bulk action which is selected.
    */
    public function get_bulk_actions()
    {
        $actions = array(
                'bulk-export' => __('Export', QUOTEUP_TEXT_DOMAIN),
                'bulk-export-all' => __('Export all enquiries', QUOTEUP_TEXT_DOMAIN),
                'bulk-delete' => __('Delete', QUOTEUP_TEXT_DOMAIN),
            );

        if (method_exists($this, 'bulkActions')) {
            $actions = array_merge($actions, $this->bulkActions());
        }

        return apply_filters('quoteup_get_bulk_actions', $actions);
    }

    /**
    * Get the columns which are sortable.
    * @return array  sortable column list
    */
    public function get_sortable_columns()
    {
        return $this->sortableColumns();
    }
    /**
    * Gets Page id , page url
    * Process bulk action generated bulk notices
    */
    public function process_bulk_action()
    {
        $currentPage = $this->get_pagenum();
        $url = admin_url('/admin.php?page=quoteup-details-new');
        $this->_processRowAction();
        $this->_processBulkAction($currentPage, $url);
        $this->_bulkActionNotices();
    }

    /**
    * Gets details for single enquiry
    * @param array $item single enquiry detail
    */
    public function single_row($item)
    {
        global $wpdb;
        $metaTbl = getEnquiryMetaTable();
        $class = '';
        $enquiryId = $item['enquiry_id'];
        $sql = "SELECT meta_value FROM $metaTbl WHERE meta_key = '_unread_enquiry' AND enquiry_id= $enquiryId";
        $metaValue = $wpdb->get_var($sql);
        if ($metaValue == 'yes') {
            $class = 'unread-enquiry';
        }
        echo '<tr class = "'.$class.'">';
        $this->single_row_columns($item);
        echo '</tr>';
    }

    /**
     * Render the dropdown containing the enquiry search by parameters.
     *
     * @since 6.4.4
     *
     * @return void
     */
    public function renderEnqSearchByParamDropdown()
    {
        $selectedSearchByParam = $this->getSelectedSearchByParam();
        $searchByParameters    = array(
            'search-by-all' => __('Search by (All)', QUOTEUP_TEXT_DOMAIN),
            'enq-id'        => __('Enquiry Id', QUOTEUP_TEXT_DOMAIN),
            'cust-name'     => __('Customer Name', QUOTEUP_TEXT_DOMAIN),
            'cust-email'    => __('Customer Email', QUOTEUP_TEXT_DOMAIN),
            'product-name'  => __('Product Name', QUOTEUP_TEXT_DOMAIN),
        );
        ?>
        <select name="quoteup_enq_search_param" id="quoteup_dropdown_enq_search_param" class="quoteup_enq_search_param">
            <?php
            foreach ($searchByParameters as $key => $search_param) {
                ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php selected($selectedSearchByParam, $key); ?>><?php echo esc_html($search_param); ?></option>
                <?php
            }
            ?>
        </select>
        <?php
    }

    /**
     * Return the Search by Parameter selected by admin.
     *
     * @since 6.4.4
     *
     * @return string   Return the selected search by parameter.
     */
    public function getSelectedSearchByParam()
    {
        $searchByParam = '';
        if (!empty($_GET['quoteup_enq_search_param'])) {
            $searchByParam = filter_var($_GET['quoteup_enq_search_param'], FILTER_SANITIZE_STRING);
        }

        return $searchByParam;
    }

    /**
     * Return the modified query to search for the products.
     * Callback function for 'quoteup_enquiry_list_base_query' filter.
     *
     * @since 6.4.4
     *
     * @param   string   $searchQuery    The SQL Query for enquiry list search.
     *
     * @return  string   Return the modified query to search for the products.
     */
    public function modifyBaseQueryForEnquirySearch($baseQuery)
    {
        if ($this->isSearchRequest()) {
            $searchByParam = $this->getSelectedSearchByParam();
            if (!empty($searchByParam) && ('product-name' === $searchByParam  || 'search-by-all' === $searchByParam)) {
                $enquiryDetailsNewTable = getEnquiryDetailsTable();
                $enquiryProductsTable   = getEnquiryProductsTable();
                $baseQuery             .= " INNER JOIN $enquiryProductsTable ON $enquiryDetailsNewTable.enquiry_id = $enquiryProductsTable.enquiry_id ";

                if (isQuotationModuleEnabled()) {
                    $enquiryQuotationTable = getQuotationProductsTable();
                    $baseQuery            .= " LEFT JOIN $enquiryQuotationTable ON $enquiryDetailsNewTable.enquiry_id = $enquiryQuotationTable.enquiry_id ";
                }
            }
        }

        return $baseQuery;
    }
}

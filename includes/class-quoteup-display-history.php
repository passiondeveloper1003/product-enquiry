<?php

namespace Includes;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * This class is used to create meta box for history on enquiry details page and display history in that box.
 *
 * @static $instance Object of class
 * $enquiry_details array enquiry details
 */
class QuoteupDisplayHistory
{
    protected static $instance = null;
    public $enquiry_details = null;

    /**
     * Function to create a singleton instance of class and return the same.
     *
     * @return [Object] [
     *                  description]
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor is used to add action for history meta box.
     */
    public function __construct()
    {
        // Display the admin notification
        add_action('quoteup_edit_details', array($this, 'historyMeta'));
    }

    /**
     * Create Meta box with heading "Quote Status History".
     *
     * @param array $enquiry_details Details of enquiry
     */
    public function historyMeta($enquiry_details)
    {
        $this->enquiry_details = $enquiry_details;
        global $quoteup_admin_menu;
        $form_data = get_option('wdm_form_data');
        $showQuoteStatusHistory = 1;
        $hasCapability = quoteupIsCurrentUserHavingManageOptionsCap();
        $hasCapability = apply_filters('quoteup_has_cap_for_quote_status_history', $hasCapability, $enquiry_details);

        if (!$hasCapability || (isset($form_data[ 'enable_disable_quote' ]) && $form_data[ 'enable_disable_quote' ] == 1)) {
            $showQuoteStatusHistory = 0;
        }

        if ($showQuoteStatusHistory == 1) {
            add_meta_box(
                'editPEDetailHistory',
                __('Quote Status History', QUOTEUP_TEXT_DOMAIN),
                array($this, 'editHistoryFn'),
                $quoteup_admin_menu,
                'normal'
            );
        }
    }

    /**
     * This function displays all the history available in enquiry history table for that particular enquiry.
     */
    public function editHistoryFn()
    {
        $enquiryID = filter_var($_GET[ 'id' ], FILTER_SANITIZE_NUMBER_INT);
        $result = \Includes\QuoteupManageHistory::getQuoteHistoryData($enquiryID, false, true);
        ?>
        <div class="enquiry-history-table-parent">
            <table class="enquiry-history-table">
                <thead class="enquiry-history-table-thead">
                    <tr>
                        <th><?php _e('Date and Time', QUOTEUP_TEXT_DOMAIN); ?></th>
                        <th><?php _e('Action', QUOTEUP_TEXT_DOMAIN); ?></th>
                        <th><?php _e('Performed by', QUOTEUP_TEXT_DOMAIN); ?></th>
                        <th><?php _e('Message', QUOTEUP_TEXT_DOMAIN); ?></th>
                    </tr>
                </thead>
                <tbody>
        <?php
        foreach ($result as $History) {
            $this->printSingleRow($History, $this->enquiry_details);
        }
        ?>
                </tbody>
            </table>
        </div>
                <?php
    }

    /**
     * This function is for performing different actions on basis of History status
     *
     * @param [array] $History [Enquiry history]
     * @param $enquiry_details Enquiry details
     */
    public function printSingleRow($History, $enquiry_details)
    {
        switch ($History[ 'status' ]) {
            case 'Approved':
                $this->historyAccept($History, $enquiry_details);
                break;

            case 'Rejected':
                $this->historyReject($History, $enquiry_details);
                break;

            case 'Sent':
                $this->historySent($History, $enquiry_details);
                break;

            case 'Requested':
                $this->historyRequested($History, $enquiry_details);
                break;

            case 'Saved':
                $this->historyQuoteSaved($History, $enquiry_details);
                break;

            case 'Expired':
                $this->historyQuoteExpired($History, $enquiry_details);
                break;

            case 'Order Placed':
                $this->historyPlaced($History, $enquiry_details);
                break;

            case 'Quote Created':
                $this->historyQuoteCreated($History, $enquiry_details);
                break;
            default:
                break;
        }
    }
    /**
    * This function returns the user-id if the  quote created by user(admin)
    * Otherwise returns visitors name from enquiry details.
    * @param [array] $History         [history data]
    * @param [array] $enquiry_details [enquiry details of customer]
    * @param string $default (visitor or user)
    */
    private function performedByUser($History, $enquiry_details, $default = 'visitor')
    {
        if ($History[ 'performed_by' ] == null || $History[ 'performed_by' ] == '' || $History[ 'performed_by' ] == 0) {
            switch ($default) {
                case 'visitor':
                    return $enquiry_details['name'];
                case 'user':
                    return $this->getUserName(get_current_user_id());
                default:
                    break;
            }
        }

        return $this->getUserName($History[ 'performed_by' ]);
    }

    /**
     * Gets the username from userId
     *
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
     * Display current element if history is for acception.
     *
     * @param [array] $History         [history data]
     * @param [array] $enquiry_details [enquiry details of customer]
     */
    private function historyAccept($History, $enquiry_details)
    {
        ?>
        <tr>
            <td><?php echo $History[ 'date' ]; ?></td>
            <td><?php _e('Quote Approved', QUOTEUP_TEXT_DOMAIN); ?></td>
            <td><?php echo $this->performedByUser($History, $enquiry_details, 'visitor'); ?></td>
            <td class="history-message"><?php echo $History[ 'message' ] == 'Approved' ? __('Approved', QUOTEUP_TEXT_DOMAIN) : $History[ 'message' ]; ?></td>
        </tr>
        <?php
    }

    /**
     * Display current element if history is for rejection.
     *
     * @param [array] $History         [history data]
     * @param [array] $enquiry_details [enquiry details of customer]
     */
    private function historyReject($History, $enquiry_details)
    {
        ?>
            <tr>
            <td><?php echo $History[ 'date' ]; ?></td>
            <td><?php _e('Quote Rejected', QUOTEUP_TEXT_DOMAIN); ?></td>
            <td><?php echo $this->performedByUser($History, $enquiry_details, 'visitor'); ?></td>
            <td class="history-message"><?php echo $History[ 'message' ] == 'Reject' ? __('Reject', QUOTEUP_TEXT_DOMAIN) : $History[ 'message' ]; ?></td>
        </tr>
        <?php
    }

    /**
     * Display current element if history is for Quotation Sent.
     *
     * @param [array] $History         [history data]
     * @param [array] $enquiry_details [enquiry details of customer]
     */
    private function historySent($History, $enquiry_details)
    {
        ?>
            <tr>
            <td><?php echo $History[ 'date' ]; ?></td>
            <td><?php _e('Quote Sent', QUOTEUP_TEXT_DOMAIN); ?></td>
            <td><?php echo $this->performedByUser($History, $enquiry_details, 'user'); ?></td>
            <td class="history-message"><?php echo $History[ 'message' ] == 'Sent' ? __('Sent', QUOTEUP_TEXT_DOMAIN) : $History[ 'message' ]; ?></td>
        </tr>
        <?php
    }

    /**
     * Display current element if history is for Quotation is Quote Created.
     *
     * @param [array] $History         [history data]
     * @param [array] $enquiry_details [enquiry details of customer]
     */
    private function historyQuoteCreated($History, $enquiry_details)
    {
        ?>
            <tr>
            <td><?php echo $History[ 'date' ]; ?></td>
            <td><?php _e('Quote Created', QUOTEUP_TEXT_DOMAIN); ?></td>
            <td><?php echo $enquiry_details['name']; ?></td>
            <td class="history-message"><?php echo $enquiry_details['message'] ?></td>
        </tr>
        <?php
    }

    /**
     * Display current element if history is for Quotation is requested.
     *
     * @param [array] $History         [history data]
     * @param [array] $enquiry_details [enquiry details of customer]
     */
    private function historyRequested($History, $enquiry_details)
    {
        ?>
            <tr>
            <td><?php echo $History[ 'date' ]; ?></td>
            <td><?php _e('Quote Requested', QUOTEUP_TEXT_DOMAIN); ?></td>
            <td><?php echo $enquiry_details['name']; ?></td>
            <td class="history-message"><?php echo $enquiry_details['message'] ?></td>
        </tr>
        <?php
    }

    /**
     * Display current element if history is Saving Quote.
     *
     * @param [array] $History         [history data]
     * @param [array] $enquiry_details [enquiry details of customer]
     */
    private function historyQuoteSaved($History, $enquiry_details)
    {
        ?>
            <tr>
            <td><?php echo $History[ 'date' ]; ?></td>
            <td><?php _e('Quote Saved', QUOTEUP_TEXT_DOMAIN); ?></td>
            <td><?php echo $this->performedByUser($History, $enquiry_details, 'user'); ?></td>
            <td class="history-message"><?php echo $History[ 'message' ] == 'Saved' ? __('Saved', QUOTEUP_TEXT_DOMAIN) : $History[ 'message' ]; ?></td>
        </tr>
        <?php
    }

    /**
     * Display current element if history Quote is expired.
     *
     * @param [array] $History         [history data]
     * @param [array] $enquiry_details [enquiry details of customer]
     */
    private function historyQuoteExpired($History, $enquiry_details)
    {
        ?>
            <tr>
            <td><?php echo $History[ 'date' ]; ?></td>
            <td><?php _e('Quote Expired', QUOTEUP_TEXT_DOMAIN); ?></td>
            <td><?php _e('System'); ?></td>
            <td class="history-message"><?php echo $History[ 'message' ] == 'Expired' ? __('Expired', QUOTEUP_TEXT_DOMAIN) : $History[ 'message' ]; ?></td>
        </tr>
        <?php
        unset($enquiry_details);
    }

     /**
      * Display current element if order is  placed.
      *
      * @param [array] $History         [history data]
      * @param [array] $enquiry_details [enquiry details of customer]
      */
    private function historyPlaced($History, $enquiry_details)
    {
        ?>
            <tr>
            <td><?php echo $History[ 'date' ]; ?></td>
            <td><?php _e('Order Placed', QUOTEUP_TEXT_DOMAIN); ?></td>
            <td><?php echo $this->performedByUser($History, $enquiry_details, 'visitor'); ?></td>
            <td class="history-message"><?php echo '-'; ?></td>
        </tr>
        <?php
    }
}

$this->displayHistory = QuoteupDisplayHistory::getInstance();

<?php
namespace Includes\Frontend;

/**
 * This class is used to check whether price needs to be shown or not.
 *
 * This class checks for quote history status and generates approve and reject link.
 */
class QuoteUpAddPopupModal
{

    /**
     * This function is used to check whether show price is enabled at backend for that quotation.
     *
     * @param  [int] $enquiry_id    [enquiry id for that quotation]
     *
     * @return [boolean]            [whether to show price]
     */
    public function shouldShowOldPriceForEnquiry($enquiry_id)
    {
        global $wpdb;
        $quotationTbl = getQuotationProductsTable();
        $flag = false;
        $prodsShowPriceData = $wpdb->get_results($wpdb->prepare("SELECT enquiry_id, show_price FROM $quotationTbl WHERE enquiry_id = %d", $enquiry_id), ARRAY_A);

        foreach ($prodsShowPriceData as $prodShowPriceData) {
            if ('yes' == $prodShowPriceData['show_price']) {
                $flag=true;
                break;
            }
        }
        return $flag;
    }
    
    /**
     * This function is used to check whether quote history status is approved or sent.
     *
     * @param  [int] $enquiry_id [enquiry id for quotation]
     */
    public function checkApproved($enquiry_id)
    {
        global $quoteupManageHistory;
        $lastAddedHistory = $quoteupManageHistory->getLastAddedHistory($enquiry_id);

        if (empty($lastAddedHistory) || !isset($lastAddedHistory['status']) || !('Sent' == $lastAddedHistory['status'] || 'Approved' == $lastAddedHistory['status'])) {
            return;
        }

        $appRejLink = getApproveRejectLink($enquiry_id);
        // if (('Saved' == $status) || ('Sent' == $status)) {
        if (!empty($appRejLink)) {
            ?>
            <div>
                <a id="quoteup-approve-link-popup" href="<?php echo $appRejLink['approve']; ?>" class="reject-quote-button" target="_blank"> <?php _e('Approve', QUOTEUP_TEXT_DOMAIN) ?></a> |
                <a id="quoteup-reject-link-popup" href="<?php echo $appRejLink['reject']; ?>" class="reject-quote-button" target="_blank">  <?php _e('Reject', QUOTEUP_TEXT_DOMAIN) ?></a>
            </div>
            <?php
        }
    }
}

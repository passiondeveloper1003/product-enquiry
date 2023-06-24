<?php
use \Includes\Frontend\QuoteUpAddPopupModal;

$approve = new QuoteUpAddPopupModal();
$formData=quoteupSettings();
$color = $formData['dialog_color'];
$dialogue_text_color = $formData['dialog_text_color'];
$quoteid = $args['enquiryid'];


$title = __('Quotation Details #', QUOTEUP_TEXT_DOMAIN).$quoteid;
?>
<div class="modal fade qmodal " id="wdm-quotes-modal-<?php echo $quoteid ?>" tabindex="-1" role="dialog" aria-hidden="true"  style="display:none">
    <div class="wdm-modal-dialog">
        <div class="wdm-modal-content qmodal-content"
        <?php
        echo ' style= "background-color:'.$color.'";';
        ?>>
            <div class="wdm-modal-header">
                <h4 class="wdm-modal-title modal-heading" id="QModalLabel"
                <?php
                    echo " style='color: ".$dialogue_text_color."; text-align:center'";
                ?>>
                    <span class='pr_name'><?php echo $title ?></span>
                </h4>
            </div>
            <div class="wdm-modal-body">
                <div class="Quotesinfo wdm-quoteup-form-inner">
                    <div class="qdetail">
                        <?php
                        $args = array(
                            'enquiryid' => $quoteid
                        );
                        quoteupGetPublicTemplatePart('quote-frontend/popup-product-details', '', $args);
                        ?>
                    </div>
                    <div class="qdetail linkdiv">
                        <?php
                        // This function will generate approve and reject link.
                        $approve->checkApproved($quoteid);
                        ?>
                    </div>
                    <div class="qdetail">
                        <h4 class="wdm-modal-title modal-heading"
                            <?php
                            $title = __("Quote Status History", QUOTEUP_TEXT_DOMAIN);
                            echo " style='color: ".$dialogue_text_color."; text-align:center'";?>>
                            <span class='pr_name'><?php echo $title; ?></span>
                        </h4>
                        <?php
                        global $quoteup;
                        $quote_history = $quoteup->displayHistory;
                        //to get enquiry details for that quote id.
                        $enquiry_detail = getEnquiryData($quoteid);
                        $quote_history->enquiry_details = $enquiry_detail;
                        // load template for quote status history.
                        $quote_history->editHistoryFn();
                        ?>
                    </div>
                </div>
                        
            </div>
                        
        </div>
    </div>
</div>

<?php

namespace Includes\Settings;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * This function is used to show approval rejection page settings.
 *
 * @param [array] $form_data [Settings stored previously in database]
 *
 * @return [type] [description]
 */
function quoteupApprovalRejectionPageSection($form_data)
{
    ?>
    <fieldset>
        <?php echo '<legend>'.__('Quotation Approval/Rejection', QUOTEUP_TEXT_DOMAIN).'</legend>';
        quotationApprovalRejectionPage($form_data);
        approveButtonLabel($form_data);
        rejectButtonLabel($form_data);
        rejectMessage($form_data);
        replaceQuote($form_data);
    ?>
    </fieldset>
    <?php
}

/**
 * This is used to show Approval Rejection Page on settings page.
 * Adds the dropdown for the available options for the pages.
 *
 *
 * @param [array] $form_data [Settings stored previously in database]
 *
 * @return [type] [description]
 */
function quotationApprovalRejectionPage($form_data)
{
    ?>
    <div class="fd">
            <div class='left_div'>
                <label for="wdm-approval-rejection-page"> <?php _e('Approval/Rejection Page', QUOTEUP_TEXT_DOMAIN) ?> </label>
            </div>
            <div class='right_div'>
            <?php
            $helptip = __('Plugin will automatically add shortcode [APPROVAL_REJECTION_CHOICE] on the selected page, if it is not already present in the page\'s content', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true);
    ?>
                <?php
                wp_dropdown_pages(
                    array(
                        'class'            => 'wdm_quoteup_pages_list',
                        'name'             => 'wdm_form_data[approval_rejection_page]',
                        'id'               => 'wdm-approval-rejection-page',
                        'selected'         => isset($form_data[ 'approval_rejection_page' ]) && !empty($form_data[ 'approval_rejection_page' ]) ? $form_data[ 'approval_rejection_page' ] : '',
                        'show_option_none' => __('Select Page', QUOTEUP_TEXT_DOMAIN),
                    )
                );
                $admin_path = get_admin_url();
    ?>
                <a class="new_page_link" href="<?php echo $admin_path.'post-new.php?post_type=page';
    ?>"> <?php _e('Add New Page', QUOTEUP_TEXT_DOMAIN); ?> </a>
            </div>
    <?php $quotationSettingsNonce = wp_create_nonce('fromQuotatioSettings');
    ?>
            <input type="hidden" name="quotationSettingsNonce" value="<?php echo $quotationSettingsNonce ?>" />
            <div class='clear'></div>
        </div >
    <?php
}

/**
 * This is used to show Approve button label on settings page.
 *
 * @param [array] $form_data [Settings stored previously in database]
 *
 * @return [type] [description]
 */
function approveButtonLabel($form_data)
{
    ?>
    <div class="fd">
        <div class='left_div'>
            <label for="approve_custom_label">
                <?php _e(' Approve Button Label ', QUOTEUP_TEXT_DOMAIN) ?>
            </label>
        </div>
        <div class='right_div'>
            <?php
            $helptip = __('Add custom label for approve quotation button.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true);
    ?>
            <input type="text" class="wdm_wpi_input wdm_wpi_text" name="wdm_form_data[approve_custom_label]"
                   value="<?php echo empty($form_data[ 'approve_custom_label' ]) ? _e('Approve Quote', QUOTEUP_TEXT_DOMAIN) : $form_data[ 'approve_custom_label' ];
    ?>" id="approve_custom_label"  />
        </div>
        <div class="clear"></div>
    </div>
    <?php
}

/**
 * This is used to show Reject button label on settings page.
 *
 * @param [array] $form_data [Settings stored previously in database]
 *
 * @return [type] [description]
 */
function rejectButtonLabel($form_data)
{
    ?>

    <div class="fd">
        <div class='left_div'>
            <label for="reject_custom_label">
                <?php _e(' Reject Button Label ', QUOTEUP_TEXT_DOMAIN) ?>
            </label>
        </div>
        <div class='right_div'>
            <?php
            $helptip = __('Add custom label for reject quotation button.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true);
    ?>
            <input type="text" class="wdm_wpi_input wdm_wpi_text" name="wdm_form_data[reject_custom_label]"
                   value="<?php echo empty($form_data[ 'reject_custom_label' ]) ? _e('Reject Quote', QUOTEUP_TEXT_DOMAIN) : $form_data[ 'reject_custom_label' ];
    ?>" id="reject_custom_label"  />
        </div>
        <div class="clear"></div>
    </div>


    <?php
}

/**
 * This is used to show Reject message on settings page.
 *
 * @param [array] $form_data [Settings stored previously in database]
 *
 * @return [type] [description]
 */
function rejectMessage($form_data)
{
    ?>

    <div class="fd">
        <div class='left_div'>
            <label for="reject_message">
                <?php _e(' Reject Message ', QUOTEUP_TEXT_DOMAIN) ?>
            </label>
        </div>
        <div class='right_div'>
            <?php
            $helptip = __('Add custom message to display when customer rejects quote.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true);
    ?>
            <input type="text" class="wdm_wpi_input wdm_wpi_text" name="wdm_form_data[reject_message]"
                   value="<?php echo empty($form_data[ 'reject_message' ]) ? _e('Quote Rejected', QUOTEUP_TEXT_DOMAIN) : $form_data[ 'reject_message' ];
    ?>" id="reject_message"  />
        </div>
        <div class="clear"></div>
    </div>


    <?php
}

/**
 * This is used to replace 'quote' words on settings page.
 *
 * @param [array] $form_data [Settings stored previously in database]
 *
 * @return [type] [description]
 */
function replaceQuote($form_data)
{
    ?>
    <div class="fd">
        <div class='left_div'>
            <label for="replace_quote">
                <?php _e(' Alternate word for Quote ', QUOTEUP_TEXT_DOMAIN) ?>
            </label>
        </div>
        <div class='right_div'>
            <?php
            $helptip = __('Alternate word for Quote and Quotation.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true);
    ?>
            <input type="text" class="wdm_wpi_input wdm_wpi_text" name="wdm_form_data[replace_quote]"
                   value="<?php echo empty($form_data[ 'replace_quote' ]) ? '' : $form_data[ 'replace_quote' ];
    ?>" id="replace_quote"  />
        </div>
        <!-- <div class="clear"></div> -->
    </div>
    <?php
}

quoteupApprovalRejectionPageSection($form_data);

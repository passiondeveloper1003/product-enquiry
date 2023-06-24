<?php
// @since 6.3.4

namespace Includes\Settings;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * This function is used to display Enquiry mail and Enquiry cart customization
 * Section.
 * @param array $form_data Quoteup settings
 */
function displayEnquiryMailAndCartCustomizationOptions($form_data)
{
    ?>
    <fieldset>
        <?php
        if (quoteupIsMPEEnabled($form_data)) {
            echo '<legend class="enquiry-mail-cart-cust-opt">'.__('Enquiry Mail and Enquiry Cart Customization Options', QUOTEUP_TEXT_DOMAIN).'</legend>';
            echo '<legend class="enquiry-mail-opt" style="display:none;">'.__('Enquiry Mail Customization Option', QUOTEUP_TEXT_DOMAIN).'</legend>';
        } else {
            echo '<legend class="enquiry-mail-cart-cust-opt" style="display:none;">'.__('Enquiry Mail and Enquiry Cart Customization Options', QUOTEUP_TEXT_DOMAIN).'</legend>';
            echo '<legend class="enquiry-mail-opt">'.__('Enquiry Mail Customization Option', QUOTEUP_TEXT_DOMAIN).'</legend>';
        }

        quoteupRenderColumnSettings($form_data);
        quoteupShowRemarksLabelSetting($form_data);
        quoteupShowRemarksColumnPlaceholderSetting($form_data);
        ?>
    </fieldset>
    <?php
}

/**
 * Render setting to enable price and remarks columns in enquiry cart and in enquiry mail table.
 *
 * @since 6.5.0
 *
 * @return void
 */
function quoteupRenderColumnSettings($form_data)
{
    ?>
    <div class="fd">
        <div class='left_div'>
        <?php
        if (quoteupIsMPEEnabled($form_data)) {
            ?>
                <label class="checkbox-coll-header enq-mail-table" style="display:none" for="enable_price_col"><?php _e('In Enquiry Mail table show:', QUOTEUP_TEXT_DOMAIN); ?></label>
                <label class="checkbox-coll-header enq-cart-mail-table" for="enable_price_col"><?php _e('In Enquiry Cart and Enquiry Mail table show:', QUOTEUP_TEXT_DOMAIN); ?></label>
                <?php
        } else {
            ?>
                <label class="checkbox-coll-header enq-mail-table" for="enable_price_col"><?php _e('In Enquiry Mail table show:', QUOTEUP_TEXT_DOMAIN); ?></label>
                <label class="checkbox-coll-header enq-cart-mail-table" style="display:none" for="enable_price_col"><?php _e('In Enquiry Cart and Enquiry Mail table show:', QUOTEUP_TEXT_DOMAIN); ?></label>
                <?php
        }
        ?>
            
        </div>
        <div class='right_div'>
            <?php
                quoteupRenderPriceColSetting($form_data);
                quoteupRenderRemarksColSetting($form_data);
            ?>
        </div>
        <div class='clear'></div>
    </div>
    <?php
}

/**
 * Render setting to enable/ disable price column in enquiry cart and in enquiry
 * mail.
 *
 * @since 6.5.0
 *
 * @return void
 */
function quoteupRenderPriceColSetting($form_data)
{
    ?>
    <div class='checkbox-coll price-col-setting'>
        <?php
            $helptip = __('This setting let\'s you enable/ disable the \'Price\' column in the enquiry mail and enquiry cart.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true);
        ?>       
        <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, isset($form_data[ 'enable_price_col' ]) ? $form_data[ 'enable_price_col' ] : 1); ?> id="enable_price_col" /> 
        <input type="hidden" name="wdm_form_data[enable_price_col]" value="<?php echo isset($form_data[ 'enable_price_col' ]) ? $form_data[ 'enable_price_col' ] : 1 ?>" />
        <label for="enable_price_col"><?php _e('Price Column', QUOTEUP_TEXT_DOMAIN); ?></label>
    </div>
    <?php
}

/**
 * Render setting to enable/ disable remarks column in enquiry cart and in
 * enquiry mail.
 *
 * @since 6.5.0
 *
 * @return void
 */
function quoteupRenderRemarksColSetting($form_data)
{
    ?>
    <div class='checkbox-coll remarks-col-setting'>
        <?php
            $helptip = __('This setting let\'s you enable/ disable the \'Expected Price\' or \'Remarks\' column in the enquiry mail and enquiry cart. If \'Quotation System\' is disabled, then it shows \'Remarks\' as column name and if \'Quotation System\' is enabled, it shows \'Expected Price\' as column name.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true);
        ?>
        <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, isset($form_data[ 'enable_remarks_col' ]) ? $form_data[ 'enable_remarks_col' ] : 0); ?> id="enable_remarks_col" /> 
        <input type="hidden" name="wdm_form_data[enable_remarks_col]" value="<?php echo isset($form_data[ 'enable_remarks_col' ]) && $form_data[ 'enable_remarks_col' ] == 1 ? $form_data[ 'enable_remarks_col' ] : 0 ?>" />
        <label for="enable_remarks_col"><?php _e('Expected Price or Remarks Column', QUOTEUP_TEXT_DOMAIN); ?></label>
    </div>
    <?php
}

/**
 * This function is used to show 'Expected Price or Remarks Label' setting.
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function quoteupShowRemarksLabelSetting($form_data)
{
    $placeholder = '';

    if (!isset($form_data[ 'expected_price_remarks_label' ]) || empty($form_data[ 'expected_price_remarks_label' ])) {
        $form_data[ 'expected_price_remarks_label' ] = '';
    }

    // If 'Quotation System' is enabled.
    if (isset($form_data[ 'enable_disable_quote' ]) && $form_data[ 'enable_disable_quote' ] == 0) {
        $placeholder = __('Expected Price', QUOTEUP_TEXT_DOMAIN);
    } else {
        $placeholder = __('Remarks', QUOTEUP_TEXT_DOMAIN);
    }
    ?>

    <div class="fd expected_price_remarks_label_wrapper">
        <div class='left_div'>
            <label for="expected_price_remarks_label">
                <?php _e('Expected Price or Remarks column head Label', QUOTEUP_TEXT_DOMAIN); ?>
            </label>
        </div>
        <div class='right_div'>
            <?php
            $helptip = __('Add custom label for Expected Price or Remarks column head in Enquiry mail and Enquiry cart. By deafult, if \'Quotation System\' is disabled, then it shows \'Remarks\' as column name and if \'Quotation System\' is enabled, it shows \'Expected Price\' as column name.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true); ?>
            <input type="text" class="wdm_wpi_input wdm_wpi_text" name="wdm_form_data[expected_price_remarks_label]"
                   value="<?php echo $form_data[ 'expected_price_remarks_label' ]; ?>" placeholder="<?php echo $placeholder; ?>" id="expected_price_remarks_label"  />
        </div>
        <div class='clear'></div>
    </div>
    <?php
}

/**
 * This function is used to show 'Expected Price or Remarks Column Placeholder' setting.
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function quoteupShowRemarksColumnPlaceholderSetting($form_data)
{
    $placeholder = '';

    if (!isset($form_data[ 'expected_price_remarks_col_phdr' ]) || empty($form_data[ 'expected_price_remarks_col_phdr' ])) {
        $form_data[ 'expected_price_remarks_col_phdr' ] = '';
    }

    // If 'Quotation System' is enabled.
    if (isset($form_data[ 'enable_disable_quote' ]) && $form_data[ 'enable_disable_quote' ] == 0) {
        $placeholder = __('Expected price and remarks', QUOTEUP_TEXT_DOMAIN);
    } else {
        $placeholder = __('Remarks', QUOTEUP_TEXT_DOMAIN);
    }
    ?>

    <div class="fd expected_price_remarks_col_phdr_wrapper">
        <div class='left_div'>
            <label for="expected_price_remarks_col_phdr">
                <?php _e('Expected Price or Remarks column field placeholder', QUOTEUP_TEXT_DOMAIN); ?>
            </label>
        </div>
        <div class='right_div'>
            <?php
            $helptip = __('Add custom placeholder for Expected Price or Remarks column field in Enquiry mail and Enquiry cart. By deafult, if \'Quotation System\' is disabled, then it shows \'Remarks\' as field placeholder and if \'Quotation System\' is enabled, it shows \'Expected price and remarks\' as field placeholder.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true); ?>
            <input type="text" class="wdm_wpi_input wdm_wpi_text" name="wdm_form_data[expected_price_remarks_col_phdr]"
                   value="<?php echo $form_data[ 'expected_price_remarks_col_phdr' ]; ?>" placeholder="<?php echo $placeholder; ?>" id="expected_price_remarks_col_phdr"  />
        </div>
        <div class='clear'></div>
    </div>
    <?php
}


displayEnquiryMailAndCartCustomizationOptions($form_data);

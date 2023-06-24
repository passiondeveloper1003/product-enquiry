<?php
/**
 * MailChimp Helper Functions
 *
 * @since 6.5.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Check if MailChimp subscription is enabled in Quoteup settings.
 *
 * @since 6.5.0
 *
 * @return  bool  Reurn true if subscription is enabled, false otherwise.
 */
function quoteupIsMailChimpSubscriptionEnabled($quoteupSettings = array())
{
    if (empty($quoteupSettings)) {
        $quoteupSettings = quoteupSettings();
    }

    return isset($quoteupSettings['enable_mc_subs_cb']) && 1 == $quoteupSettings['enable_mc_subs_cb'] ? true : false;
}

/**
 * Return label/ text for MailChimp subscription checkbox on the enquiry form.
 *
 * @since 6.5.0
 *
 * @return  string  Return label/ text for subscription checkbox.
 */
function quoteupGetMailChimpCBLabel($quoteupSettings = array())
{
    if (empty($quoteupSettings)) {
        $quoteupSettings = quoteupSettings();
    }

    $label = isset($quoteupSettings['mc_subs_text']) ? $quoteupSettings['mc_subs_text'] : quoteupGetDefaultMailChimpCBLabel();

    return trim($label);
}

/**
 * Return default label/ text for MailChimp subscription checkbox on the enquiry form.
 *
 * @since 6.5.0
 *
 * @return  string  Return default label/ text for subscription checkbox.
 */
function quoteupGetDefaultMailChimpCBLabel()
{
    return __('Subscribe to our newsletter', QUOTEUP_TEXT_DOMAIN);
}

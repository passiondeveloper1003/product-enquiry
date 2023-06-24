<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * This function is used to show General settings on settings page.
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function otherExtensionsSettings($form_data)
{
    ?>
    <div id="wdm_other_extensions">
        <?php
        require_once 'other-extensions/quoteup-other-extensions-options.php';
        do_action('quoteup_other_extensions_settings', $form_data);
        ?>
    </div>
    <?php
}

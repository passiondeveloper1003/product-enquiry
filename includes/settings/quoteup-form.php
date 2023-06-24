<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * This function is used to show General settings on settings page.
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function formSettings($form_data)
{
    ?>
    <div id="wdm_form">
        <?php
        require_once 'form/quoteup-form-options.php';
        do_action('quoteup_form_settings', $form_data);
        ?>
    </div>
    <?php
}

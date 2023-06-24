<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function quoteupWhatsNewTab($form_data)
{
    ?>
    <div id='wdm_whats_new'>
    <?php
        require_once 'whats-new/whats-new-tab.php';
        do_action('quoteup_whats_new_tab', $form_data);
    ?>
    </div>
<?php
}

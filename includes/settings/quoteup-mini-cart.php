<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function miniCartSettings($form_data)
{
    ?>
<div id='wdm_mini_cart'>
<?php
    require_once 'mini-cart/quoteup-mini-cart-settings.php';
    do_action('quoteup_mini_cart_settings', $form_data);
    ?>
        
</div>
<?php
}

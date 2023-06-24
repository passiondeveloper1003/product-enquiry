<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

add_action('send_headers', 'quoteupStartQuoteCartSession', 10);

/**
* This function is used to start session for Quote cart.
* Sets the count of product in session variable.
*/
function quoteupStartQuoteCartSession()
{
    global $quoteup;
    $prodCount = $quoteup->wcCartSession->get('wdm_product_count');

    // if (empty(session_id())) {
    //     ini_set('session.cookie_lifetime', 60 * 60 * 24 * 7);
    //     ini_set('session.gc_maxlifetime’', 60 * 60 * 24 * 7);
    //     session_start();
    // }
    // $_SESSION[ 'count_product' ] = (!isset($_SESSION[ 'count_product' ]) || !$_SESSION[ 'count_product' ]) ? 0 : $_SESSION[ 'count_product' ];
    
    $prodCount = (!isset($prodCount) || !$prodCount) ? 0 : $prodCount;
    $quoteup->wcCartSession->set('wdm_product_count', $prodCount);
    //$_SESSION[ 'wdm_product_count' ] = (!isset($_SESSION[ 'wdm_product_count' ]) || !$_SESSION[ 'wdm_product_count' ]) ? 0 : $_SESSION[ 'wdm_product_count' ];
}

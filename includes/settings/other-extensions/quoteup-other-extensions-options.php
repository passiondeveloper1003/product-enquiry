<?php

namespace Includes\Settings;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * This function is used to display the form options section in settings
 * @param array $form_data Settings of QuoteUp
 */
function otherExtensions()
{
    require_once 'init.php';
    $promotionObject = Promotion::getInstance();

    $promotionObject->promotionPage();
}

otherExtensions();

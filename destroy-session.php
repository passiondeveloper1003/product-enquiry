<?php

add_action('wp_logout', 'destroySession');
add_action('wp_login', 'destroySession');

// This function destroys session on login and logout
function destroySession()
{
    if (!isset($_SESSION)) {
        @session_start();
    }
    @session_destroy();
}

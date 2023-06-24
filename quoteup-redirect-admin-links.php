<?php

/**
* This function returns the full address of the site admin.
* @return string full address of the site admin.
*/
function quoteupGetAddress()
{
	if (isset($_SERVER[ 'HTTP_HOST' ]) && isset($_SERVER[ 'REQUEST_URI' ])) {
    // return the full address
    return quoteupGetProtocol().'://'.$_SERVER[ 'HTTP_HOST' ].$_SERVER[ 'REQUEST_URI' ];
    }
    return '';
}

/**
* This function sets the base protocol for the site admin .
* @return string $protocol http or https
*/
function quoteupGetProtocol()
{
    // Set the base protocol to http
    $protocol = 'http';
    // check for https
    if (isset($_SERVER[ 'HTTPS' ]) && strtolower($_SERVER[ 'HTTPS' ]) == 'on') {
        $protocol .= 's';
    }

    return $protocol;
}

/**
* This function changes the old admin links which were product-enquiry-for-woocommerce to quoteup
*/
function quoteupRedirectOldAdminLinks()
{
    $userrequest = str_ireplace(admin_url(), '', quoteupGetAddress());
    $userrequest = rtrim($userrequest, '/');
    $do_redirect = '';
    if ($userrequest == 'admin.php?page=product-enquiry-for-woocommerce' || $userrequest == 'admin.php?page=product-enquiry-details-new' || false !== strpos($userrequest, '?page=product-enquiry-details-edit')) {
        if ($userrequest == 'admin.php?page=product-enquiry-for-woocommerce') {
            $do_redirect = admin_url('admin.php?page=quoteup-for-woocommerce');
        } elseif ($userrequest == 'admin.php?page=product-enquiry-details-new') {
            $do_redirect = admin_url('admin.php?page=quoteup-details-new');
        } else {
            $do_redirect = admin_url('admin.php?page=quoteup-details-edit&id='.$_GET[ 'id' ]);
        }
        if (empty($do_redirect)) {
            return;
        }
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: '.$do_redirect);
        exit();
    }
}

add_action('init', 'quoteupRedirectOldAdminLinks', 1);

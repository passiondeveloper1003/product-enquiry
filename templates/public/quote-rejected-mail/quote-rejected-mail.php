<?php
/**
 * Quote Rejected Mail
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/quote-rejected-mail/quote-rejected-mail.php.
 *
 * HOWEVER, on occasion QuoteUp will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author  WisdmLabs
 * @version 6.5.0
 */

if (!defined('ABSPATH')) :
    exit; // Exit if accessed directly
endif;

// This loads the template for email header
quoteupGetPublicTemplatePart('quote-rejected-mail/heading', '', $args);

// This loads the template for products table
quoteupGetPublicTemplatePart('quote-rejected-mail/mail-body', '', $args);

// This loads the template for email footer
quoteupGetPublicTemplatePart('quote-rejected-mail/footer', '', $args);

<?php
/**
 * Enquiry mail
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/enquiry-mail/enquiry-mail.php.
 *
 * HOWEVER, on occasion QuoteUp will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author  WisdmLabs
 * @version 6.1.0
 */

if (!defined('ABSPATH')) :
    exit; // Exit if accessed directly
endif;

/**
 * This action is documented in /product-enquiry-pro/includes/class-quoteup-wpml-compatibility.php.
 */
do_action('quoteup_change_lang', $data_obtained_from_form['wdmLocale']);

do_action('quoteup_before_enquiry_mail', $data_obtained_from_form);

//This loads the template for product table
quoteupGetPublicTemplatePart('enquiry-mail/heading', '', $args);

//This loads the template for product table
quoteupGetPublicTemplatePart('enquiry-mail/mail-body', '', $args);

do_action('woocommerce_email_footer');

<?php
/**
 * Enquiry Heading
 *
 * This template can be overridden by copying it to yourtheme/quoteup/public/enquiry-mail/heading.php.
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

do_action('quoteup_before_enquiry_mail_heading', $data_obtained_from_form);

if ($optionData['enable_disable_quote'] == 1) :
    $heading = "<b>".__('Enquiry from', QUOTEUP_TEXT_DOMAIN)."  $customerName </b>";
else :
    $heading = "<b>".__('Quote Request from', QUOTEUP_TEXT_DOMAIN)."  $customerName </b>";
endif;

$heading = apply_filters('quoteup_enquiry_mail_heading', $heading, $data_obtained_from_form, $source);

// Using this action woocommerce adds heading to mail.
do_action('woocommerce_email_header', $heading, $args['emailObject']);

do_action('quoteup_after_enquiry_mail_heading', $data_obtained_from_form);

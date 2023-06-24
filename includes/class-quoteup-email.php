<?php

namespace Includes;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
* This class performs the function related to sending quote emails.
* Gets the headers, from name, from emails from quoteUp settings by admin.
* Sends the email for quoatation.
*/
if (!class_exists('QuoteUpEmail')) {
    class QuoteUpEmail
    {
        /**
         * Constructor.
         */
        public function __construct()
        {
        }

        /**
         * Get email content type.
         *
         * @return string content type
         */
        public function getContentType()
        {
            return 'text/html';
        }

        /**
         * Get the from name for outgoing emails.
         *
         * @return string email from name
         */
        public function getFromName()
        {
            $blog_name = get_option('woocommerce_email_from_name');
            if (empty($blog_name)) {
                $blog_name = get_option('blogname');
            }
            $from_name = apply_filters('pep_email_from_name', $blog_name);

            return wp_specialchars_decode(esc_html($from_name), ENT_QUOTES);
        }

        /**
         * Get the from address for outgoing emails.
         *
         * @return string address for mails
         */
        public function getFromAddress()
        {
            $from_address = get_option('woocommerce_email_from_address');
            if (empty($from_address)) {
                $from_address = get_option('admin_email');
            }
            $from_address = apply_filters('pep_email_from_address', $from_address);

            return sanitize_email($from_address);
        }

        /**
         * Send an email.
         *
         * @param string $too Reciepient email address
         * @param string $subject Subject of mail
         * @param string $message Message in mail
         * @param string $headers Headers for mail
         * @param string $attachments attachments if any
         *
         * @return bool success if sent succesfully
         */
        public function send($too, $subject, $message, $headers, $attachments = '')
        {
            add_filter('wp_mail_from', array($this, 'getFromAddress'));
            add_filter('wp_mail_from_name', array($this, 'getFromName'));
            add_filter('wp_mail_content_type', array($this, 'getContentType'));

            $return = wp_mail($too, $subject, $message, $headers, $attachments);

            remove_filter('wp_mail_from', array($this, 'getFromAddress'));
            remove_filter('wp_mail_from_name', array($this, 'getFromName'));
            remove_filter('wp_mail_content_type', array($this, 'getContentType'));

            return $return;
        }
    }
}

$GLOBALS[ 'quoteupEmail' ] = new QuoteUpEmail();

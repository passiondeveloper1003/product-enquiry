<?php

namespace Includes\Vendor\Emails;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

WC()->mailer();

/**
 * This abstract class is used to send mail.
 */
if (!class_exists('QuoteupAbstractReminderEmails')) {
    abstract class QuoteupAbstractReminderEmails extends \WC_Email
    {
        public function __construct()
        {
            parent::__construct();
        }

        /**
         * Show email heading.
         */
        public function emailHeading($heading, $hookName, $enquiryId, $deadline, $emailObject)
        {
            do_action('quoteup_before_'.$hookName, $enquiryId, $deadline, $heading);
            $heading = apply_filters('quoteup_' . $hookName, $heading, $enquiryId, $deadline);
            do_action('woocommerce_email_header', $heading, $emailObject);
            do_action('quoteup_after_'.$hookName, $enquiryId, $deadline, $heading);
        }

        /**
         * Show email footer.
         */
        public function emailFooter($hookName, $enquiryId, $deadline)
        {
            do_action('quoteup_before_'.$hookName, $enquiryId, $deadline);
            do_action('woocommerce_email_footer');
            do_action('quoteup_after_'.$hookName, $enquiryId, $deadline);
        }

        abstract public function getEmailHeaders();
    }
}

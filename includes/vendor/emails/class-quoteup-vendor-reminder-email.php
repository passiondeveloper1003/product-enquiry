<?php

namespace Includes\Vendor\Emails;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * This class is used to send email to vendors.
 * It sends email to all vendors when 'Quote Send Deadline' is changed.
 * It also sends emails to the vendors who have not updated the product price
 * to remind them about the deadline of the quotation.
 *
 * Initial Quote Deadline Email - This email is used to inform all vendors
 *     about the deadline of the quotation set by the admin. The email is
 *     sent when admin sets or modifies earlier set deadline for the
 *     quotation.
 */
if (!class_exists('QuoteupVendorReminderEmail')) {
    class QuoteupVendorReminderEmail extends QuoteupAbstractReminderEmails
    {
        private static $instance;
        protected $emailType;
        protected $enquiryId;
        protected $deadline;

        /**
        * This function returns the Singleton emailObject.
        */
        public static function getInstance()
        {
            if (null === static::$instance) {
                static::$instance = new self();
            }
    
            return static::$instance;
        }

        public function __construct()
        {
            parent::__construct();

            if (!quoteupIsVendorCompEnabled()) {
                return;
            }
    
            add_action('quoteup_initial_quote_dl_email_bd', array($this, 'prepareInitailQuoteDlEmailBody'), 20, 2);
            add_action('quoteup_vendor_dl_reminder_email_bd', array($this, 'prepareDlReminderEmailBody'), 20, 2);
        }

        /**
         * Send email to all vendors to inform about the deadline date when
         * adming modified the 'Quote Send Deadline'.
         *
         * @param int $enquiryId Enquiry Id.
         * @param string $deadline String containing deadline date.
         */
        public function sendInitialQuoteDlEmail($enquiryId, $deadline)
        {
            $this->emailType = 'initial';
            $this->enquiryId = $enquiryId;
            $this->deadline  = $deadline;

            $recipients = $this->getRecipients();
            $subject    = $this->getInitialQuoteDlEmailSubject();
            $message    = $this->get_content();
            $headers    = $this->getEmailHeaders();

            if (!empty($recipients)) {
                $this->send($recipients, $subject, $message, $headers, array());
            }
        }

        /**
         * Send email to all vendors who have not product prices to remind them
         * about the deadline date and to update the product prices.
         *
         * @param int $enquiryId Enquiry Id.
         */
        public function sendDlReminderEmail($enquiryId)
        {
            $this->emailType = 'quoteup_remind_vendors';
            $this->enquiryId = $enquiryId;
            $this->deadline  = quoteupGetDeadlineOfEnquiry($enquiryId);

            $recipients = $this->getRecipients();
            $subject    = $this->getDlReminderEmailSubject();
            $message    = $this->get_content();
            $headers    = $this->getEmailHeaders();

            if (!empty($recipients)) {
                $this->send($recipients, $subject, $message, $headers, array());
            }
        }

        // @codingStandardsIgnoreLine
        public function get_content_html()
        {
            ob_start();

            if (empty($this->emailType) || 'initial' == $this->emailType) {
                $this->prepareInitialQuoteDlEmailContent($this->enquiryId, $this->deadline);
            } else {
                // Prepare email for vendor reminder about the deadline.
                $this->prepareDlReminderEmailContent($this->enquiryId, $this->deadline);
            }

            $message = ob_get_clean();
            return wp_specialchars_decode($message, ENT_QUOTES);
        }

        public function getEmailHeaders()
        {
            $admin  = get_option('admin_email');
            $header = "Reply-to: " . $admin . "\r\n";
            return apply_filters('quoteup_vendor_quote_dl_mail_headers', $header);
        }

        private function getInitialQuoteDlEmailSubject()
        {
            $subject = sprintf(__('Quote Deadline set for #%d', 'quoteup'), $this->enquiryId);
            return $subject;
        }

        public function getDlReminderEmailSubject()
        {
            $subject = sprintf(__('Quote Deadline for Enquiry #%d', QUOTEUP_TEXT_DOMAIN), $this->enquiryId);
            $subject = apply_filters('quoteup_dl_reminder_vendor_email_sub', $subject, $this->enquiryId);
            return $subject;
        }

        public function getRecipients()
        {
            $vendorEmails = array();
            $hookName  = '';

            if (empty($this->emailType) || 'initial' == $this->emailType) {
                $hookName  = 'initial_quote_dl_recipients';
            } else {
                // Remind vendors about the deadline.
                $hookName = 'dl_reminder_vendor_recipients';
            }

            $vendorEmails = quoteupGetVendorsNotUpdatedPrice($this->enquiryId, true);
            $vendorEmails = apply_filters('quoteup_'.$hookName, $vendorEmails, $this->enquiryId, $this->deadline);
            return $vendorEmails;
        }

        /**
         * This function prepares the email content to be sent to all vendors.
         * The email notifies about the quote deadline set by an admin to all vendors.
         *
         * @param int $enquiryId    Enquiry Id.
         * @param int $deadline     Deadline of quote.
         *
         * @return void
         */
        public function prepareInitialQuoteDlEmailContent($enquiryId, $deadline)
        {
            // Email heading.
            $heading = sprintf(__('Quotation Deadline for enquiry #%d', QUOTEUP_TEXT_DOMAIN), $enquiryId);
            $this->emailHeading($heading, 'initial_quote_dl_email_hd', $enquiryId, $deadline, $this);

            // Email body.
            do_action('quoteup_initial_quote_dl_email_bd', $enquiryId, $deadline);

            // Email footer
            $this->emailFooter('initial_quote_dl_email_ft', $enquiryId, $deadline);
        }

        /**
         * This function prepares the email content.
         * The email notifies about the deadline to all the vendors who have
         * not updated the product prices.
         *
         * @param int $enquiryId    Enquiry Id.
         * @param int $deadline     Deadline of quote.
         *
         * @return void
         */
        public function prepareDlReminderEmailContent($enquiryId, $deadline)
        {
            // Email heading.
            $heading = sprintf(__('Reminder about Quotation Deadline for enquiry #%d', QUOTEUP_TEXT_DOMAIN), $enquiryId);
            $this->emailHeading($heading, 'vendor_dl_reminder_email_hd', $enquiryId, $deadline, $this);

            // Email body.
            do_action('quoteup_vendor_dl_reminder_email_bd', $enquiryId, $deadline);

            // Email footer
            $this->emailFooter('vendor_dl_reminder_email_ft', $enquiryId, $deadline);
        }

        /**
         * Prepare the message for the initial deadline notification email.
         */
        public function prepareInitailQuoteDlEmailBody($enquiryId, $deadline)
        {
            $message = sprintf(__('The deadline for the Enquiry #%1$d is %2$s. Kindly update the product prices as soon as possible before the deadline.', QUOTEUP_TEXT_DOMAIN), $enquiryId, $deadline);
            $message = apply_filters('quoteup_initial_quote_dl_email_bd_msg', $message, $enquiryId, $deadline);
            ?>
            <br>
            <p><?php echo $message; ?></p>
            <?php
        }

        /**
         * Prepare the message for the reminder email.
         */
        public function prepareDlReminderEmailBody($enquiryId, $deadline)
        {
            $message = sprintf(__('The deadline for the Enquiry #%1$d is %2$s. Kindly update the product prices as soon as possible before the deadline.', QUOTEUP_TEXT_DOMAIN), $enquiryId, $deadline);
            $message = apply_filters('quoteup_vendor_dl_reminder_email_bd_msg', $message, $enquiryId, $deadline);
            ?>
            <br>
            <p><?php echo $message; ?></p>
            <?php
        }
    }
}


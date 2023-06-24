<?php

namespace Includes\Vendor\Emails;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * This class is used to send email to the admin.
 * It sends an email to the admin about the deadline of the quotation.
 * The email contains the information about the vendors who have not updated
 * the product prices.
 */
if (!class_exists('QuoteupAdminReminderEmail')) {
    class QuoteupAdminReminderEmail extends QuoteupAbstractReminderEmails
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
    
            add_action('quoteup_admin_dl_reminder_email_bd', array($this, 'prepareDlReminderEmailBody'), 20, 2);
            add_action('quoteup_admin_dl_reminder_email_2_bd', array($this, 'prepareDlReminderEmail2Body'), 20, 2);
            add_action('quote_sent_notify_admin_email_bd', array($this, 'prepareQuoteSentNotifyAdminBody'), 20, 2);
        }

        /**
         * Send an email to the admin to remind about the deadline and to
         * inform about the vendors who have not product prices.
         *
         * @param int $enquiryId Enquiry Id.
         */
        public function sendDlReminderEmail($enquiryId)
        {
            $this->emailType = 'quoteup_remind_admin';
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

        /**
         * Send an email to the admin to remind about the deadline and to
         * inform about the vendors who have not product prices.
         *
         * @param int $enquiryId Enquiry Id.
         */
        public function sendDlReminderEmail2($enquiryId)
        {
            $this->emailType = 'quoteup_remind_admin_2';
            $this->enquiryId = $enquiryId;
            $this->deadline  = quoteupGetDeadlineOfEnquiry($enquiryId);
            $recipients      = $this->getRecipients();
            $isQuoteSent     = quoteupCheckAndSendQuote($enquiryId);
            
            if ($isQuoteSent) {
                $this->emailType = 'quoteup_quote_sent_notify_admin';
                $subject    = $this->getQuoteSentEmailSubject();
                $headers    = $this->getQuoteSentNotifyEmailHeaders();
            } else {
                $subject    = $this->getDlReminderEmailSubject();
                $headers    = $this->getEmailHeaders();
            }
            $message         = $this->get_content();

            if (!empty($recipients)) {
                $this->send($recipients, $subject, $message, $headers, array());
            }
        }

        // @codingStandardsIgnoreLine
        public function get_content_html()
        {
            ob_start();

            // Check if it is first reminder email.
            if (empty($this->emailType) || 'quoteup_remind_admin' == $this->emailType) {
                $this->prepareDlReminderEmailContent($this->enquiryId, $this->deadline);
            } elseif ('quoteup_remind_admin_2' == $this->emailType) {
                $this->prepareDlReminderEmail2Content($this->enquiryId, $this->deadline);
            } else {
                $this->prepareQuoteSentNotifyAdminContent($this->enquiryId, $this->deadline);
            }

            $message = ob_get_clean();
            return wp_specialchars_decode($message, ENT_QUOTES);
        }

        public function getEmailHeaders()
        {
            $header       = '';
            $vendorIds    = quoteupGetVendorsNotUpdatedPrice($this->enquiryId);

            if (!empty($vendorIds)) {
                $vendorEmails = quoteupGetUserEmailsByIds($vendorIds);
                $vendorEmails = implode(', ', $vendorEmails);
                $header = "Reply-to: " . $vendorEmails . "\r\n";
            }

            return apply_filters('quoteup_admin_dl_reminder_email_headers', $header, $this->enquiryId, $this->emailType);
        }

        /**
         * Return email header about quote has been sent to the customer.
         *
         * @return  string
         */
        public function getQuoteSentNotifyEmailHeaders()
        {
            $enquiryData   = getEnquiryData($enquiryId);
            $customerEmail = $enquiryData['email'];
            $header        = "Reply-to: " . $customerEmail . "\r\n";

            return apply_filters('quote_sent_notify_admin_email_headers', $header, $enquiryId, $this->emailType);
        }

        public function getDlReminderEmailSubject()
        {
            $subject = sprintf(__('Quote Deadline for Enquiry #%d', QUOTEUP_TEXT_DOMAIN), $this->enquiryId);
            $subject = apply_filters('quoteup_admin_dl_reminder_email_sub', $subject, $this->enquiryId, $this->emailType);
            return $subject;
        }

        /**
         * Return the subject to be added in the email which is sent to the
         * admin to notify that the quote has been sent to the customer.
         *
         * @return  string  Return email subject.
         */
        public function getQuoteSentEmailSubject()
        {
            $subject = sprintf(__('Quote Sent for Enquiry #%d', QUOTEUP_TEXT_DOMAIN), $this->enquiryId);
            $subject = apply_filters('quote_sent_notify_admin_email_sub', $subject, $this->enquiryId, $this->emailType);
            return $subject;
        }

        public function getRecipients()
        {
            $recipients = array();

            // Fetch recipients.
            $recipients[] = get_option('admin_email');

            $recipients = apply_filters('quoteup_admin_dl_reminder_recipients_recipients', $recipients, $this->emailType, $this->enquiryId, $this->deadline);
            return $recipients;
        }

        /**
         * This function prepares the email content.
         * The email notifies about the deadline to the admin and also informs
         * about the vendors who have not updated the product prices.
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
            $this->emailHeading($heading, 'admin_dl_reminder_email_hd', $enquiryId, $deadline, $this);
            
            // Email body.
            do_action('quoteup_admin_dl_reminder_email_bd', $enquiryId, $deadline);

            // Email footer
            $this->emailFooter('admin_dl_reminder_email_ft', $enquiryId, $deadline);
        }

        /**
         * This function prepares the second reminder email content.
         * The email notifies about the deadline to the admin and also informs
         * about the vendors who have not updated the product prices.
         *
         * @param int $enquiryId    Enquiry Id.
         * @param int $deadline     Deadline of quote.
         *
         * @return void
         */
        public function prepareDlReminderEmail2Content($enquiryId, $deadline)
        {
            // Email heading.
            $heading = sprintf(__('Reminder about Quotation Deadline for enquiry #%d', QUOTEUP_TEXT_DOMAIN), $enquiryId);
            $this->emailHeading($heading, 'admin_dl_reminder_email_2_hd', $enquiryId, $deadline, $this);
            
            // Email body.
            do_action('quoteup_admin_dl_reminder_email_2_bd', $enquiryId, $deadline);

            // Email footer
            $this->emailFooter('admin_dl_reminder_email_2_ft', $enquiryId, $deadline);
        }

        /**
         * This function prepares the quote sent notification email content.
         * The email notifies about quote has been sent to the customer.
         *
         * @param int $enquiryId    Enquiry Id.
         * @param int $deadline     Deadline of quote.
         *
         * @return void
         */
        public function prepareQuoteSentNotifyAdminContent($enquiryId, $deadline)
        {
            // Email heading.
            $heading = sprintf(__('Quote has been sent to the customer for enquiry #%d', QUOTEUP_TEXT_DOMAIN), $enquiryId);
            $this->emailHeading($heading, 'quote_sent_notify_admin_email_hd', $enquiryId, $deadline, $this);
            
            // Email body.
            do_action('quote_sent_notify_admin_email_bd', $enquiryId, $deadline);

            // Email footer
            $this->emailFooter('quote_sent_notify_admin_email_ft', $enquiryId, $deadline);
        }

        /**
         * Prepare the message for the reminder email.
         */
        public function prepareDlReminderEmailBody($enquiryId, $deadline)
        {
            $message   = sprintf(__('The deadline for the Enquiry #%1$d is %2$s.', QUOTEUP_TEXT_DOMAIN), $enquiryId, $deadline);
            $vendorIds = quoteupGetVendorsNotUpdatedPrice($enquiryId);

            if (!empty($vendorIds)) {
                $vendorEmails = quoteupGetUserEmailsByIds($vendorIds);
                $message  = '<br>' . $message;
                $message .= __(' The following vendor(s) have not updated product prices:', QUOTEUP_TEXT_DOMAIN);
                $message .= '<table>';
                foreach ($vendorEmails as $email) {
                    $message .= '<tr><td>' . $email . '</td></tr>';
                }
                $message .= '</table>';
            }

            $message = apply_filters('quoteup_admin_dl_reminder_email_bd_msg', $message, $enquiryId, $deadline, $vendorIds);
            ?>
            <p><?php echo $message; ?></p>
            <?php
        }

        /**
         * Prepare the message for the admin reminder email 2.
         */
        public function prepareDlReminderEmail2Body($enquiryId, $deadline)
        {
            $message = sprintf(__('The deadline for the Enquiry #%1$d is %2$s.', QUOTEUP_TEXT_DOMAIN), $enquiryId, $deadline);
            $vendorIds = quoteupGetVendorsNotUpdatedPrice($enquiryId);

            if (!empty($vendorIds)) {
                $vendorEmails = quoteupGetUserEmailsByIds($vendorIds);
                $message  = '<br>' . $message;
                $message .= __(' The following vendor(s) have not updated product prices:', QUOTEUP_TEXT_DOMAIN);
                $message .= '<table>';
                foreach ($vendorEmails as $email) {
                    $message .= '<tr><td>' . $email . '</td></tr>';
                }
                $message .= '</table>';
            }

            $message = apply_filters('quoteup_admin_dl_reminder_email_2_bd_msg', $message, $enquiryId, $deadline, $vendorIds);
            ?>
            <p><?php echo $message; ?></p>
            <?php
        }

        /**
         * Prepare the message for the notification email.
         */
        public function prepareQuoteSentNotifyAdminBody($enquiryId, $deadline)
        {
            $enquiryLink = admin_url('admin.php?page=quoteup-details-edit&id='.$enquiryId);
            $message     = sprintf(__('To view the enquiry, click <a href=%s>here</a>.', QUOTEUP_TEXT_DOMAIN), $enquiryLink);

            $message = apply_filters('quote_sent_notify_admin_email_bd_msg', $message, $enquiryId, $deadline);
            ?>
            <br>
            <p><?php echo $message; ?></p>
            <?php
        }
    }
}

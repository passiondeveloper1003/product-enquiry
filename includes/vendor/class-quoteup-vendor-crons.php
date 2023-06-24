<?php

namespace Includes\Admin\Vendor;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * This class handles the cron related functionality.
 * It manipulates following crons:
 *     quoteup_remind_vendors - args(<enquiry_id>)
 *     quoteup_remind_admin - args(<enquiry_id>)
 *     quoteup_remind_admin_deadline - args(<enquiry_id>)
 */
class QuoteupVendorCrons
{
    private static $instance;

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            QuoteupVendorCrons::$instance = new self();
        }
        
        return QuoteupVendorCrons::$instance;
    }

    protected function __construct()
    {
        // If Multi-Vendor is not enabled, then return.
        if (!quoteupIsVendorCompEnabled()) {
            return;
        }

        add_action('quoteup_after_quote_send_deadline_set', array($this, 'manipulateAfterDeadlineChanged'), 20, 2);
        add_action('quoteup_remind_vendors', array($this, 'sendDlReminderEmailToVendor'));
        add_action('quoteup_remind_admin', array($this, 'sendDlReminderEmailToAdmin'));
        add_action('quoteup_remind_admin_deadline', array($this, 'sendDlReminderEmailSecToAdmin'));
    }

    /**
     * Callback for 'quoteup_after_quote_send_deadline_set' hook.
     * Manipulate crons when 'Quote Send Deadline' is changed.
     * Clear earlier crons.
     * Set new crons based on new deadline.
     */
    public function manipulateAfterDeadlineChanged($enquiryId, $deadline)
    {
        $this->setCrons($enquiryId, $deadline);
    }

    /**
     * Set the following required crons:
     *     quoteup_remind_vendors - args(<enquiry_id>)
     *     quoteup_remind_admin - args(<enquiry_id>)
     *     quoteup_remind_admin_deadline - args(<enquiry_id>)
     * Clear the earliers crons before setting the new crons.
     *
     * @param int $enquiryId    Enquuiry Id.
     * @param string $deadline  Deadline.
     */
    public function setCrons($enquiryId, $deadline)
    {
        $enquiryId = (int) $enquiryId;

        // Reminder cron collections.
        $remCronColl = array (
            'quoteup_remind_admin_deadline'
        );

        if (quoteupIsVendorReminderEnabled()) {
            $remCronColl[] = 'quoteup_remind_vendors';
        }

        if (quoteupIsAdminReminderEnabled()) {
            $remCronColl[] = 'quoteup_remind_admin';
        }

        // Use the filter to configure the reminder crons.
        $remCronColl = apply_filters('quoteup_admin_vendor_rem_cron_coll', $remCronColl);

        // Clear all crons.
        $this->clearCrons($enquiryId, $deadline);

        // Set 'quoteup_remind_vendors' cron if it exists in array.
        if (in_array('quoteup_remind_vendors', $remCronColl)) {
            $this->setVendorReminderCron($enquiryId, $deadline);
        }

        // Set 'quoteup_remind_admin' cron if it exists in array.
        if (in_array('quoteup_remind_admin', $remCronColl)) {
            $this->setAdminReminderCron($enquiryId, $deadline);
        }

        // Set 'quoteup_remind_admin_deadline' cron if it exists in array.
        if (in_array('quoteup_remind_admin_deadline', $remCronColl)) {
            $this->setAdminDlReminderCron($enquiryId, $deadline);
        }
    }

    /**
     * Remove the earlier set crons.
     *
     */
    public function clearCrons($enquiryId, $deadline = '')
    {
        $enquiryId = (int) $enquiryId;
        do_action('quoteup_vendor_before_crons_cleared', $enquiryId, $deadline);

        wp_clear_scheduled_hook('quoteup_remind_vendors', array($enquiryId));
        wp_clear_scheduled_hook('quoteup_remind_admin', array($enquiryId));
        wp_clear_scheduled_hook('quoteup_remind_admin_deadline', array($enquiryId));

        do_action('quoteup_vendor_after_crons_cleared', $enquiryId, $deadline);
    }

    /**
     * Set 'quoteup_remind_vendors - args(<enquiry_id>)' cron.
     * Set the cron for two days before the actual deadline date.
     */
    public function setVendorReminderCron($enquiryId, $deadline)
    {
        $nextRun = strtotime($deadline);

        // Change time to two days before.
        $nextRun = $nextRun - (DAY_IN_SECONDS * 2);
        $nextRun = apply_filters('quoteup_remind_vendors_cron_time', $nextRun, $enquiryId, $deadline);
        $this->addCron($nextRun, '_oneoff', 'quoteup_remind_vendors', array($enquiryId));

        do_action('quoteup_after_remind_vendors_cron_set', $enquiryId, $deadline);
    }

    /**
     * Set 'quoteup_remind_admin - args(<enquiry_id>)' cron.
     * Set the cron for one day before the actual deadline date.
     */
    public function setAdminReminderCron($enquiryId, $deadline)
    {
        $nextRun = strtotime($deadline);

        // Change time to one day before.
        $nextRun = $nextRun - DAY_IN_SECONDS;
        $nextRun = apply_filters('quoteup_remind_admin_cron_time', $nextRun, $enquiryId, $deadline);
        $this->addCron($nextRun, '_oneoff', 'quoteup_remind_admin', array($enquiryId));

        do_action('quoteup_after_remind_admin_cron_set', $enquiryId, $deadline);
    }

    /**
     * Set 'quoteup_remind_admin_deadline - args(<enquiry_id>)' cron.
     * Set the cron for the actual deadline date.
     */
    public function setAdminDlReminderCron($enquiryId, $deadline)
    {
        $nextRun = strtotime($deadline);

        $nextRun = apply_filters('quoteup_remind_admin_deadline_cron_time', $nextRun, $enquiryId, $deadline);
        $this->addCron($nextRun, '_oneoff', 'quoteup_remind_admin_deadline', array($enquiryId));

        do_action('quoteup_after_remind_admin_cron_set', $enquiryId, $deadline);
    }

    /**
     * Adds a new cron event.
     *
     * @param string $next_run Unix timestamp (the number of seconds) that the
     *                         event should be run at in the local timezone.
     * @param string $schedule The recurrence of the cron event
     * @param string $hookname The name of the hook to execute
     * @param array $args Arguments to add to the cron event
     */
    public function addCron($next_run, $schedule, $hookname, $args)
    {
        $next_run = get_gmt_from_date(date('Y-m-d H:i:s', $next_run), 'U');

        // Return if cron time is passed.
        if ($next_run <= time()) {
            return;
        }

        if (! is_array($args)) {
            $args = array();
        }

        if ('_oneoff' === $schedule) {
            wp_schedule_single_event($next_run, $hookname, $args);
        } else {
            wp_schedule_event($next_run, $schedule, $hookname, $args);
        }
    }

    public function sendDlReminderEmailToVendor($enquiryId)
    {
        $emailInstance = \Includes\Vendor\Emails\QuoteupVendorReminderEmail::getInstance();
        $emailInstance->sendDlReminderEmail($enquiryId);
    }

    public function sendDlReminderEmailToAdmin($enquiryId)
    {
        $emailInstance = \Includes\Vendor\Emails\QuoteupAdminReminderEmail::getInstance();
        $emailInstance->sendDlReminderEmail($enquiryId);
    }

    public function sendDlReminderEmailSecToAdmin($enquiryId)
    {
        $emailInstance = \Includes\Vendor\Emails\QuoteupAdminReminderEmail::getInstance();
        $emailInstance->sendDlReminderEmail2($enquiryId);
    }
}

QuoteupVendorCrons::getInstance();

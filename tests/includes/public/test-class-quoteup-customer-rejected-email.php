<?php

namespace Includes\Frontend;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * This class is used to send mail to admin when a customer rejects the quote.
 *
 * @since 6.5.0
 */
class TestQuoteupCustomerRejectedEmail extends \WP_UnitTestCase
{
    public $emailObject;

    public function setUp()
    {
        global $wpdb;
        quoteupCreateTables();

        include_once QUOTEUP_PLUGIN_DIR.'/includes/public/class-quoteup-customer-rejected-email.php';
        $this->emailObject = \Includes\Frontend\QuoteupCustomerRejectedEmail::getInstance(16, 'Testing PHP Unit');

        update_option('blogname', 'Amaron');
    }

    public function tearDown()
    {
        // unset($this->testsub);
        quoteupDropTables();
    }

    public function test_getAdminHeaders()
    {
        $this->emailObject->customerEmail  = 'harry.potter@example.com';

        $testAdminHeaders = "Reply-to: " . $this->emailObject->customerEmail . "\r\n";
        $this->assertEquals($testAdminHeaders, $this->emailObject->getAdminHeaders());
    }

    public function test_getMailSubject()
    {
        $this->emailObject->enquiryId    = 16;
        $this->emailObject->customerName = 'Harry';

        $testSubject = 'Quote (#16) Rejected by Harry - Amaron';
        $this->assertEquals($testSubject, $this->emailObject->getMailSubject());
    }
}

<?php

namespace Includes\Admin\Abstracts;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * This abstract class provides the basic structure
 * to derived classes which create the default pages
 * required by PEP on fresh installation or during
 * Setup Wizard.
 */
abstract class QuoteupDefaultPage
{
    /**
     * Set up page.
     */
    public abstract function setUpPage();

    /**
     * Create a page.
     *
     * @return int  Return page Id.
     */
    public abstract function createPage();

    /**
     * Set the page Id in PEP setting.
     *
     * @param   int     $pageId     Page Id to set in PEP setting.
     */
    public abstract function setPageIdInSetting($pageId);
}

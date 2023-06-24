<?php

//get site url
$str = get_home_url();
$site_url = preg_replace('#^https?://#', '', $str);


return [
    /**
     * Plugins short name appears on the License Menu Page
     */
    'pluginShortName' => EDD_WPEP_ITEM_NAME,

    /**
     * this slug is used to store the data in db. License is checked using two options viz edd_<slug>_license_key and edd_<slug>_license_status
     */
    'pluginSlug' => 'pep',

    /**
     * Download Id on EDD Server
     */
    'itemId'  => 3212,

    /**
     * Current Version of the plugin. This should be similar to Version tag mentioned in Plugin headers
     */
    'pluginVersion' => QUOTEUP_VERSION,

    /**
     * Under this Name product should be created on WisdmLabs Site
     */
    'pluginName' => EDD_WPEP_ITEM_NAME,

    /**
     * Url where program pings to check if update is available and license validity
     */
    'storeUrl' => EDD_WPEP_STORE_URL,

    /**
     * Site url which will pass in API request.
     */
    'siteUrl' => $site_url,


    /**
     * Author Name
     */
    'authorName' => 'WisdmLabs',

    /**
     * Text Domain used for translation
     */
    'pluginTextDomain' => 'quoteup',

    /**
     * Base Url for accessing Files
     * if code is integrated in theme use ''
     * default is plugins_url('/', __FILE__) for plugins.
     */
    'baseFolderUrl' => plugins_url('/', __FILE__),

    /**
     * Base Directory path for accessing Files
     */
    'baseFolderDir' => untrailingslashit(plugin_dir_path(__FILE__)),

    /**
     * Plugin Main file name
     */
    'mainFileName' => 'product-enquiry-pro.php',

    /**
     * Set true if theme
     */
    'isTheme' => false,

    /**
    *  Changelog page link for theme
    *  should be false for plugin
    */
    'themeChangelogUrl' =>  false,

    /**
     * Dependent plugins for plugin
     */
    'dependencies' => array(
        'woocommerce' => WC_VERSION,
        ),
];

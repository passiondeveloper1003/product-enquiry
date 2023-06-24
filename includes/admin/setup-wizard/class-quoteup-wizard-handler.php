<?php
namespace Includes\Admin\SetupWizard;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * A class to process Quoteup Setup Wizard.
 *
 * @since 6.5.0
 */
if (! class_exists('QuoteupWizardData')) {
    class QuoteupWizardHandler
    {
        /**
         * A class instance
         *
         * @var QuoteupWizardHandler
         */
        private static $instance = null;

         /**
         * Quoteup unique wizard slug.
         *
         * @since 6.5.0
         *
         * @var string
         */
        public $wizardSlug;

        /**
         * Returns the *Singleton* instance of this class.
         *
         * @return QuoteupWizardHandler The *Singleton* instance.
         */
        public static function getInstance($wizardSlug)
        {
            if (null === self::$instance) {
                self::$instance = new self($wizardSlug);
            }
            return self::$instance;
        }

        /**
         * Constructor.
         */
        private function __construct($wizardSlug)
        {
            $this->wizardSlug = $wizardSlug;
            add_filter('wisdm_setup_wizards', array($this, 'returnWizardPrerequisite'));
            add_action('quoteup-setup-wizard_setup_wizard_enqueue_scripts', array( $this, 'enqueueScripts'));
        }

        /**
         * Enqueue the scripts.
         *
         * Callback for action 'quoteup-setup-wizard_setup_wizard_enqueue_scripts'.
         *
         * @since 6.5.0
         *
         * @return void
         */
        public function enqueueScripts($currentStepSlug)
        {
            // If the current step is 'System Setup' step.
            if ('system-setup' == $currentStepSlug) {
                // Enqueue script
                // wordpress/wp-content/plugins/product-enquiry-pro-into-setup-wizard/js/admin/setup-wizard/system-setup.js
                wp_enqueue_script(
                    'setup-wizard-system-setup',
                    QUOTEUP_PLUGIN_URL . '/js/admin/setup-wizard/system-setup.js',
                    array(
                    'jquery',
                    ),
                    filemtime(QUOTEUP_PLUGIN_DIR . '/js/admin/setup-wizard/system-setup.js')
                );
            }
        }

        /**
         * Return prerequisite data.
         *
         * Callback to filter 'wisdm_setup_wizards'.
         *
         * @since 6.5.0
         */
        public function returnWizardPrerequisite($wizards)
        {
            $slug = $this->wizardSlug;
            $quoteupWizard = array(
                $slug => array( // Unique wizard slug.
                    'title'      => 'WISDM Product Enquiry Pro', // Product Name
                    'capability' => 'manage_options', // The user must have this capability to load the wizard.
                    'steps'      => array( // Sequential steps.
                        'introduction'    => array( // step slug, every step slug must be unique.
                            'step_title'    => __('Introduction', QUOTEUP_TEXT_DOMAIN), // This will display at the top as a step title.
                            'view_callback' => array($this, 'introView'), // A callback function to display content of this step.
                        ),
                        'enquiry-button-setup' => array(
                            'step_title'    => __('Enquiry Button', QUOTEUP_TEXT_DOMAIN),
                            'view_callback' => array( $this, 'enquiryButtonSetupView' ),
                            'save_callback' => array( $this, 'enquiryButtonSetupSave' ), // A callback function to save the data of this step. Optional.
                        ),
                        'system-setup' => array(
                            'step_title'    => __('System Setup', QUOTEUP_TEXT_DOMAIN),
                            'view_callback' => array( $this, 'systemSetupView' ),
                            'save_callback' => array( $this, 'systemSetupSave' ), // A callback function to save the data of this step. Optional.
                        ),
                        'email-setup' => array(
                            'step_title'    => __('Email Setup', QUOTEUP_TEXT_DOMAIN),
                            'view_callback' => array( $this, 'emailSetupView' ),
                            'save_callback' => array( $this, 'emailSetupSave' ), // A callback function to save the data of this step. Optional.
                        ),
                        'ready'           => array(
                            'step_title'    => 'Done!',
                            'view_callback' => array( $this, 'readyView' ),
                        ),
                    ),
                ),
            );

            return array_merge($wizards + $quoteupWizard);
        }

        /**
         * Display the introduction screen.
         *
         * @since 6.5.0
         *
         * @return void
         */
        public function introView()
        {
            $wizard_handler = \Wisdm_Wizard_Handler::get_instance();

            $args = array(
                'wizard_handler' => $wizard_handler
            );

            quoteupGetAdminTemplatePart('setup-wizard/introduction', '', $args);
        }

        /**
         * Display the Enquiry Button setup screen.
         *
         * @since 6.5.0
         *
         * @return void
         */
        public function enquiryButtonSetupView()
        {
            $wizard_handler  = \Wisdm_Wizard_Handler::get_instance();
            $quoteupSettings = quoteupSettings();

            $args = array(
                'wizard_handler'       => $wizard_handler,
                'enq_btn_visibility'   => quoteupReturnEnqBtnVisibilitySetting($quoteupSettings),
                'show_enquiry_on_shop' => quoteupIsEnquiryEnabledOnArchive($quoteupSettings),
                'only_if_out_of_stock' => quoteupIsOutOfStockSettingEnabled($quoteupSettings)
            );

            quoteupGetAdminTemplatePart('setup-wizard/enquiry-button-setup', '', $args);
        }

        /**
         * Display the System Setup screen.
         *
         * @since 6.5.0
         *
         * @return void
         */
        public function systemSetupView()
        {
            $wizard_handler  = \Wisdm_Wizard_Handler::get_instance();
            $quoteupSettings = quoteupSettings();
            $cart_page       = get_option('woocommerce_cart_page_id');
            $checkout_page   = get_option('woocommerce_checkout_page_id');
            $exclude_tree    = $this->returnExcludeTree($cart_page, $checkout_page);

            $args = array(
                'wizard_handler'        => $wizard_handler,
                'enable_mpe'            => quoteupIsMPEEnabled($quoteupSettings),
                'mpe_cart_page_id'      => quoteupGetEnquiryCartPageId($quoteupSettings),
                'exclude_tree'          => $exclude_tree,
                'enable_quote'          => isQuotationModuleEnabled($quoteupSettings),
                'enable_pdf'            => quoteupIsQuotePDFEnabled($quoteupSettings),
                'company_name'          => quoteupReturnCompanyName($quoteupSettings),
                'company_email'         => quoteupReturnCompanyEmail($quoteupSettings),
                'quote_app_rej_page_id' => quoteupGetQuoteAppRejPageId($quoteupSettings),
            );

            quoteupGetAdminTemplatePart('setup-wizard/system-setup', '', $args);
        }

        /**
         * Display the Email Setup screen.
         *
         * @since 6.5.0
         *
         * @return void
         */
        public function emailSetupView()
        {
            $wizard_handler  = \Wisdm_Wizard_Handler::get_instance();
            $quoteupSettings = quoteupSettings();

            $args = array(
                'wizard_handler'              => $wizard_handler,
                'recipient_email_addresses'   => quoteupReturnReceipientEmailAddresses($quoteupSettings),
                'send_mail_to_admin'          => quoteupIsSendMailToAdminSettingEnabled($quoteupSettings),
                'send_mail_to_author'         => quoteupIsSendMailToAuthorSettingEnabled($quoteupSettings),
                'default_sub'                 => quoteupReturnDefaultSubject($quoteupSettings),
            );

            quoteupGetAdminTemplatePart('setup-wizard/email-setup', '', $args);
        }

        /**
         * Display the Ready screen.
         *
         * @since 6.5.0
         *
         * @return void
         */
        public function readyView()
        {
            $setup_wizard         = \Wisdm_Setup_Wizard::get_instance();
            $create_new_quote_url = admin_url('/admin.php?page=quoteup-create-quote');
            $settings_url         = admin_url('/admin.php?page=quoteup-for-woocommerce');
            $quoteupSettings      = quoteupSettings();

            $args = array(
                'setup_wizard'         => $setup_wizard,
                'create_new_quote_url' => $create_new_quote_url,
                'settings_url'         => $settings_url,
                'enable_quote'         => isQuotationModuleEnabled($quoteupSettings),
            );

            quoteupGetAdminTemplatePart('setup-wizard/ready', '', $args);
        }

        /**
         * Save the settings for Enquiry Button setup.
         *
         * @since 6.5.0
         *
         * @return void
         */
        public function enquiryButtonSetupSave()
        {
            // Verify nonce.
            if (false == check_admin_referer('quoteup_enquiry_button_setup', 'quoteup_setup_nonce')) {
                return;
            }

            // Get the settings.
            $postData = isset($_POST['wdm_form_data']) ? $_POST['wdm_form_data'] : false;
            if (empty($postData)) {
                return;
            }

            // Sanitize settings.
            foreach ($postData as $key => $val) {
                $postData[$key] = sanitize_text_field($val);
            }

            if (isset($postData['show_enquiry_on_shop']) && 'on' == $postData['show_enquiry_on_shop']) {
                $postData['show_enquiry_on_shop'] = '1';
            } else {
                $postData['show_enquiry_on_shop'] = '0';
            }

            if (isset($postData['only_if_out_of_stock']) && 'on' == $postData['only_if_out_of_stock']) {
                $postData['only_if_out_of_stock'] = '1';
            } else {
                $postData['only_if_out_of_stock'] = '0';
            }

            $quoteupSettings = quoteupSettings();
            $quoteupSettings = array_merge($quoteupSettings, $postData);
            update_option('wdm_form_data', $quoteupSettings);
        }

        /**
         * Save the settings for System Setup.
         *
         * @since 6.5.0
         *
         * @return void
         */
        public function systemSetupSave()
        {
            // Verify nonce.
            if (false == check_admin_referer('quoteup_system_setup', 'quoteup_setup_nonce')) {
                return;
            }

            // Get the settings.
            $postData = isset($_POST['wdm_form_data']) ? $_POST['wdm_form_data'] : false;
            if (empty($postData)) {
                return;
            }

            // Sanitize settings.
            foreach ($postData as $key => $val) {
                $postData[$key] = sanitize_text_field($val);
            }

            if (isset($postData['enable_disable_mpe']) && 'on' == $postData['enable_disable_mpe']) {
                $postData['enable_disable_mpe'] = '1';

                if (isset($_POST['gen_enq_cart_page']) && 'on' == sanitize_text_field($_POST['gen_enq_cart_page'])) {
                    // Generate and set enquiry cart page.
                    $enquiryCartPage           = \Includes\Admin\DefaultPage\QuoteupEnquiryCartPage::getInstance();
                    $postData['mpe_cart_page'] = $enquiryCartPage->createPage();
                }
            } else {
                $postData['enable_disable_mpe'] = '0';
            }

            if (isset($postData['enable_disable_quote']) && 'on' == $postData['enable_disable_quote']) {
                $postData['enable_disable_quote'] = '0';

                if (isset($_POST['gen_quote_app_rej_page']) && 'on' == sanitize_text_field($_POST['gen_quote_app_rej_page'])) {
                    // Generate and set quote approval/ rejection page.
                    $approvalRejectionPage               = \Includes\Admin\DefaultPage\QuoteupApprovalRejectionPage::getInstance();
                    $postData['approval_rejection_page'] = $approvalRejectionPage->createPage();
                }
            } else {
                $postData['enable_disable_quote'] = '1';
            }

            if (isset($postData['enable_disable_quote_pdf']) && 'on' == $postData['enable_disable_quote_pdf']) {
                $postData['enable_disable_quote_pdf'] = '1';
            } else {
                $postData['enable_disable_quote_pdf'] = '0';
            }

            $quoteupSettings = quoteupSettings();
            $quoteupSettings = array_merge($quoteupSettings, $postData);
            update_option('wdm_form_data', $quoteupSettings);
        }

        /**
         * Save the settings for Email Setup.
         *
         * @since 6.5.0
         *
         * @return void
         */
        public function emailSetupSave()
        {
            // Verify nonce.
            if (false == check_admin_referer('quoteup_email_setup', 'quoteup_setup_nonce')) {
                return;
            }

            // Get the settings.
            $postData = isset($_POST['wdm_form_data']) ? $_POST['wdm_form_data'] : false;
            if (empty($postData)) {
                return;
            }

            // Sanitize settings.
            foreach ($postData as $key => $val) {
                $postData[$key] = sanitize_text_field($val);
            }

            if (isset($postData['send_mail_to_admin']) && 'on' == $postData['send_mail_to_admin']) {
                $postData['send_mail_to_admin'] = '1';
            } else {
                $postData['send_mail_to_admin'] = '0';
            }

            if (isset($postData['send_mail_to_author']) && 'on' == $postData['send_mail_to_author']) {
                $postData['send_mail_to_author'] = '1';
            } else {
                $postData['send_mail_to_author'] = '0';
            }

            $quoteupSettings = quoteupSettings();
            $quoteupSettings = array_merge($quoteupSettings, $postData);
            update_option('wdm_form_data', $quoteupSettings);
        }

        /**
         * Returns the checkout page or cart page or both comma separated based on condition for wp_dropdown_pages.
         *
         * @since 6.5.0
         *
         * @param  int  $cart_page  Woocommerce Cart page id.
         * @param  int  $checkout_page  Woocommerce checkout page id.
         *
         * @return  string  Return checkout page or cart page or both comma separated.
         */
        function returnExcludeTree($cartPage, $checkoutPage)
        {
            if (empty($checkoutPage) && empty($cartPage)) {
                return '';
            } elseif (!empty($checkoutPage) && !empty($cartPage)) {
                return "$checkoutPage,$cartPage";
            } else {
                $excTree = !empty($checkoutPage) ? $checkoutPage : $cartPage;
                return $excTree;
            }
        }
    }
}

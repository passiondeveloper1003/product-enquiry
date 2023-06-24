<?php
namespace Includes\Admin\SetupWizard;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * A class to initialize Setup Wizard.
 *
 * @since 6.5.0
 */
if (! class_exists('QuoteupSetupWizard')) {
    class QuoteupSetupWizard
    {
        /**
         * A class instance.
         *
         * @var QuoteupSetupWizard
         */
        private static $instance = null;

        /**
         * Contains option name with which Setup Wizard status is maintained in db.
         *
         * @since 6.5.0
         *
         * @var string
         */
        public $optionName;

        /**
         * Quoteup unique wizard slug.
         *
         * @since 6.5.0
         *
         * @var string
         */
        public $wizardSlug;

        /**
         * Return the *Singleton* instance of this class.
         *
         * @return QuoteupSetupWizard The *Singleton* instance.
         */
        public static function getInstance()
        {
            if (null === self::$instance) {
                self::$instance = new self;
            }
            return self::$instance;
        }

        /**
         * Constructor.
         */
        private function __construct()
        {
            $this->optionName = 'quoteup_did_setup_wizard_run';
            $this->wizardSlug = 'quoteup-setup-wizard';

            register_activation_hook(PEP_PLUGIN_BASENAME, array($this, 'pluginActivated'));
            add_action('quoteup_after_version_updated_in_db', array($this, 'checkAndUpdateSetupWizardStatus'));
            add_action('quoteup_before_general_settings', array($this, 'showSetupWizardIcon'));

            $this->initializeSetupWizard();
        }

        /**
         * Initialize Setup Wizard.
         *
         * Delete the 'quoteup_plugin_activated' option.
         * Add Setup Wizard to 'admin_init' action hook.
         *
         * @since 6.5.0
         *
         * @return void
         */
        public function initializeSetupWizard()
        {
            include_once QUOTEUP_PLUGIN_DIR . '/includes/admin/setup-wizard/class-quoteup-wizard-handler.php';
            QuoteupWizardHandler::getInstance($this->wizardSlug);

            if ('yes' == get_option('quoteup_plugin_activated', 'no')) {
                $this->removePluginActivatedOption();
                add_action('admin_init', array($this, 'runSetupWizard'));
            }
        }

        /**
         * Run the Setup Wizard if it has not run yet.
         *
         * @since 6.5.0
         *
         * @return void
         */
        public function runSetupWizard()
        {
            if ($this->shouldRunSetupWizard()) {
                $this->updateSetupWizardStatus();
                wp_safe_redirect($this->returnSetupWizardURL());
                exit;
            }
        }

        /**
         * Check whether Setup Wizard should be run.
         *
         * @since 6.5.0
         *
         * @return  bool|string  $previousVersion  True if setup wizard should be run, false otherwise.
         */
        public function shouldRunSetupWizard()
        {
            $shouldRunSetupWizard = false;

            if ('no' == get_option($this->optionName, 'no')) {
                $shouldRunSetupWizard = true;
            }

            /**
             * Determine whether Setup Wizard should be run.
             *
             * @since 6.5.0
             *
             * @param  bool  $shouldRunSetupWizard  True if Setup Wizard
             *                            should be run, false otherwise.
             */
            $shouldRunSetupWizard = apply_filters('quoteup_should_run_setup_wizard', $shouldRunSetupWizard);
            return $shouldRunSetupWizard;
        }

        /**
         * Update Setup Wizard status after performing check.
         *
         * @since 6.5.0
         *
         * @return  void
         */
        public function checkAndUpdateSetupWizardStatus($oldVersion)
        {
            if (false !== $oldVersion) {
                $this->updateSetupWizardStatus();
            }
        }

        /**
         * Show an icon to start Setup Wizard.
         *
         * @since 6.5.0
         *
         * @return  void
         */
        public function showSetupWizardIcon()
        {
            $setupWizardURL = $this->returnSetupWizardURL();
            ?>
            <div class="quoteup-setup-wizard-help-tip">
                <a href="<?php echo esc_url($setupWizardURL); ?>" title="<?php esc_attr_e('Launch setup wizard', QUOTEUP_TEXT_DOMAIN); ?>">
                    <img src="<?php echo esc_url(QUOTEUP_PLUGIN_URL.'/images/setup-wizard/setup-wizard-gear.svg'); ?>" >
                </a>
            </div>
            <?php
        }

        /**
         * Update Setup Wizard status.
         *
         * @since 6.5.0
         *
         * @return  void
         */
        public function updateSetupWizardStatus()
        {
            update_option($this->optionName, 'yes');

            /**
             * After Setup Wizard status updated to 'yes'.
             *
             * @since 6.5.0
             */
            do_action('quoteup_after_setup_wizard_status_updated');
        }

        /**
         * Return the URL for quoteup Setup Wizard.
         *
         * @since 6.5.0
         *
         * @return  string  Return Setup Wizard URL.
         */
        public function returnSetupWizardURL()
        {
            include_once QUOTEUP_PLUGIN_DIR . '/includes/admin/setup-wizard/wisdm-setup/class-wisdm-wizard-handler.php';
            
            $wizard_handler = \Wisdm_Wizard_Handler::get_instance();
            $url            = $wizard_handler->get_wizard_first_step_link($this->wizardSlug);

            return $url;
        }

        /**
         * Add an option when Quoteup plugin is activated.
         *
         * @since 6.5.0
         *
         * @return void
         */
        public function pluginActivated()
        {
            add_option('quoteup_plugin_activated', 'yes');
        }

        /**
         * Remove an option added when Quoteup plugin was activated.
         *
         * @since 6.5.0
         *
         * @return void
         */
        public function removePluginActivatedOption()
        {
            delete_option('quoteup_plugin_activated');
        }
    }

    QuoteupSetupWizard::getInstance();
}

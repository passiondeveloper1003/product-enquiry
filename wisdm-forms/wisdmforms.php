<?php

/*
  Plugin Name: Wisdm Forms
  Plugin URI: https://wisdmlabs.com/
  Description: Drag and Drop Form Builder Form WordPress
  Author: WisdmLabs
  Version: 1.0.0
  Author URI: https://wisdmlabs.com/
 */


define("WF_BASE_DIR", dirname(__FILE__) . "/");
define("WF_BASE_URL", plugins_url('/', __FILE__));
$uploads_dir = wp_upload_dir();
$uploadPath = $uploads_dir[ 'basedir' ].'/QuoteUp_Files/';
define("WF_UPLOAD_PATH", $uploadPath);
define('WF_ACTIVATED', true);

// Include libraries

require_once WF_BASE_DIR . 'libs/settingsapi.class.php';
require_once WF_BASE_DIR . 'libs/advanced-fields.class.php';
require_once WF_BASE_DIR . 'libs/payment.class.php';
require_once WF_BASE_DIR . 'libs/payment_methods/Paypal/class.Paypal.php';
require_once WF_BASE_DIR . 'libs/field_defs.php';
require_once WF_BASE_DIR . 'libs/form-fields.class.php';
require_once WF_BASE_DIR . 'libs/functions.php';
// require_once WF_BASE_DIR . 'libs/phpcaptcha/captcha.php';
require_once WF_BASE_DIR . 'libs/font-awesome.php';
require_once WF_BASE_DIR . 'settings.php';
require_once WF_BASE_DIR . 'form-functions.php';

class WisdmForm
{

    public $fields_common;
    public $fields_generic;
    public $fields_advaced;
    public $set_methods;

    public static function getInstance()
    {
        static $instance;
        if ($instance == null) {
            $instance = new self;
        }
        return $instance;
    }

    /**
     * Constructor function
     */
    private function __construct()
    {
        // Public view shortcodes
        add_shortcode('wisdmform', array($this, 'view_showform'));
        // add_shortcode('wisdmform_agent', array($this, 'view_agent'));
        add_shortcode('wisdmform_query', array($this, 'view_public_token'));

        // Deploy installer
        register_activation_hook(__FILE__, array($this, 'install'));

        // Activate init hooks
        add_action('init', array($this, 'form_post_type_init'));
        add_action('init', array($this, 'ajax_get_request_list'));
        add_action('init', array($this, 'ajax_submit_reply'));
        // add_action('init', array($this, 'ajax_action_submit_form'));
        add_action('wp_ajax_submitCustomForm', array($this, 'ajax_action_submit_form'));
        add_action('wp_ajax_nopriv_submitCustomForm', array($this, 'ajax_action_submit_form'));
        add_action('init', array($this, 'ajax_submit_change_request_state'));
        // add_action('init', array($this, 'ajax_action_upadate_agent'));
        // add_action('init', array($this, 'show_captcha_image'));
        add_action('init', array($this, 'add_payment_verification_hook'));
        add_action('init', array($this, 'autoload_field_classes'));

        // Custom UI elements
        add_action('admin_menu', array($this, 'register_custom_menu_items'));
        add_action('add_meta_boxes_form', array($this, 'add_meta_box'));
        add_action('add_meta_boxes_form', array($this, 'register_form_settings_meta_box'));
        // add_filter('post_row_actions', array($this, 'add_option_showreqs'), 10, 2);
        // add_filter('manage_form_posts_columns', array($this, 'add_columns_to_form_list'));
        // add_action('manage_form_posts_custom_column', array($this, 'populate_form_list_custom_columns'), 10, 2);
        add_filter("wisdmform_submitform_thankyou_message", array($this, 'wisdmform_submitform_thankyou_message'), 10, 1);

        // wisdmform bindings
        add_action('save_post_form', array($this, 'action_save_form'));
        add_action('quoteup_after_custom_form_data_updated', array($this, 'save_form_fields_labels_status'));
        add_action('admin_notices', array($this, 'my_admin_notice'));
        add_action("wp_ajax_get_reqlist", array($this, "action_get_reqlist"));
        add_filter("the_content", array($this, "form_preview"));

        add_action('quoteup_custom_form_shortcode_run', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));


        $this->setup_fields();


        if (is_admin()) {
            WisdmFormSettings::getInstance();
        }
    }

    /*
     * Installer script to create
     * - Necessary custom tables
     * - Add additional roles
     */

    function install()
    {
        // Invoke wordpress Database object
        global $wpdb;

        // SQLs for creating custom tables
        // Create table to save the "contact requests"/"form entries"
        $sqls[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wisdmform_conreqs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `fid` int(11) NOT NULL,
            `uid` int(11) NOT NULL,
            `data` text NOT NULL,
            `reply_for` int(11) NOT NULL,
            `status` varchar(20) NOT NULL,
            `token` varchar(20) NOT NULL,
            `time` int(11) NOT NULL,
            `agent_id` int(11) NOT NULL,
            `replied_by` varchar(500) NOT NULL,
            PRIMARY KEY (`id`))";

        $sqls[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wisdmform_stats` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `fid` int(11) NOT NULL,
            `author_id` int(11) NOT NULL,
            `action` varchar(20) NOT NULL,
            `ip` varchar(30) NOT NULL,
            `time` int(11) NOT NULL,
            PRIMARY KEY (`id`)
        )";

        // Add necessary roles
        // Agent role that helps "agent" users to manage
        // the forms that have been assigned to them
        // $agent_caps = get_role('subscriber');
        // add_role('agent', 'Agent', $agent_caps->capabilities);

        // Execute the SQLs
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        foreach ($sqls as $sql) {
            dbDelta($sql);
        }
    }

    /**
     * @function setup_fields
     * @uses     Add the field definitions
     *                    - Common Field types
     *                    - Advanced Field types
     *                    - Generic Field types
     *                    - Method Set
     */
    function setup_fields()
    {
        $this->fields_common = apply_filters("common_fields", $this->fields_common);
        $this->fields_generic = apply_filters("generic_fields", $this->fields_generic);
        $this->fields_advanced = apply_filters("advanced_fields", $this->fields_advaced);
        $this->set_methods = apply_filters("method_set", $this->set_methods);
    }

    // Custom menu items for the Admin UI
    /**
     * @function register_custom_menu_items
     * @uses     Adds various additional menu and list items to wordpress
     * @global   type $submenu to modify the wordpress menu items
     */
    function register_custom_menu_items()
    {
        // Submenu item in the "Forms" menu item
        // add_submenu_page('edit.php?post_type=form', __('Form entries'), __('Form entries'), 'manage_options', 'form-entries', array($this, 'admin_view_submitted_forms'));
        // add_submenu_page('edit.php?post_type=form', __('Statistics'), __('Statistics'), 'manage_options', 'statistics', array($this, 'admin_view_global_stats'));
        // add_submenu_page('edit.php?post_type=form', __('Add-ons'), __('Add-ons'), 'manage_options', 'addons', array($this, 'addons_list'));
        // global $submenu;
        // Agent creation panel
        // $submenu['edit.php?post_type=form'][501] = array(__('Add agents'), 'manage_options', admin_url('user-new.php'));
    }

    /**
     * @function add_option_showreqs
     * @param    array $actions Add link to 'Entries' list for a form
     * @param    type $post Get the details of the 'Form'
     * @return   string
     * @uses     Add a link to all the 'Entries' that have been posted through a 'Form'. This link
     *        is added to the Forms list in the Administration backend
     */
    // function add_option_showreqs($actions, $post) {
    //     if ($post->post_type == 'form') {
    //         // Entries finder item for the "Forms" list
    //         $actions['showreqs'] = "<a class='submitdelete' title='" . esc_attr(__('List all entries')) . "' href='" . admin_url("edit.php?section=requests&post_type=form&page=form-entries&form_id={$post->ID}") . "'>" . __('Entries') . "</a>";
    //         $actions['showstats'] = "<a class='submitdelete' title='" . esc_attr(__('Statistics')) . "' href='" . admin_url("edit.php?post_type=form&page=statistics&form_id={$post->ID}&ipp=5&paged=1") . "'>" . __('Statistics') . "</a>";
    //     }
    //     return $actions;
    // }

    /**
     * @param  $url
     * @return mixed
     */

    function remote_get($url)
    {
        $options = array(
            CURLOPT_RETURNTRANSFER => true, // return web page
            CURLOPT_HEADER => false, // don't return headers
            CURLOPT_FOLLOWLOCATION => true, // follow redirects
            CURLOPT_ENCODING => "", // handle all encodings
            CURLOPT_USERAGENT => "spider", // who am i
            CURLOPT_AUTOREFERER => true, // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
            CURLOPT_TIMEOUT => 120, // timeout on response
            CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
        );

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        $header = curl_getinfo($ch);
        curl_close($ch);
        return $content;
    }

    /**
     * @function addons_list
     * @uses     Fetch add-on list from server
     */
    // function addons_list(){

    //     if(!isset($_SESSION['wpdm_addon_store_data'])){
    //         $data = $this->remote_get('http://wisdmform.org/?wpdm_api=wisdmformapiz&task=getPackageList');
    //         $cats = $this->remote_get('http://wisdmform.org/?wpdm_api_req=getCategoryList');
    //         $_SESSION['wpdm_addon_store_data'] = $data;
    //         $_SESSION['wpdm_addon_store_cats'] = $cats;
    //     }
    //     else {
    //         $data = $_SESSION['wpdm_addon_store_data'];
    //         $cats = $_SESSION['wpdm_addon_store_cats'];
    //     }

    //     include(dirname(__FILE__)."/views/addons-list.php");
    // }

    /**
     * @function is_ajax
     * @uses     Library fucntion to check if an ajax request
     * is being handled
     * @return   type boolean
     */
    function is_ajax()
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }

    /**
     * Enqueue the styles and scripts.
     *
     * Enqueue the styles and scripts when custom form is rendered on frontend.
     * Callback for action 'quoteup_custom_form_shortcode_run'.
     *
     * @return void
     */
    function enqueue_scripts()
    {
        if (!is_admin()) {
            $quoteupSettings = quoteupSettings();
            
            wp_enqueue_style('lf_bootstrap_css', WF_BASE_URL . 'views/css/bootstrap.min.css', array(), filemtime(WF_BASE_DIR.'views/css/bootstrap.min.css'));
            wp_enqueue_style('lf_fontawesome_css', WF_BASE_URL . 'views/css/font-awesome.min.css', array(), filemtime(WF_BASE_DIR.'views/css/font-awesome.min.css'));
            wp_enqueue_style('lf_style_css', WF_BASE_URL . 'views/css/front.css', array(), filemtime(WF_BASE_DIR.'views/css/front.css'));

            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-validate', WF_BASE_URL . 'views/js/jquery.validate.min.js', array('jquery'), filemtime(WF_BASE_DIR.'views/js/jquery.validate.min.js'), true);

            // Don't enqueue bootstrap if it is disabled.
            if (!quoteupIsBootstrapDisabled($quoteupSettings)) {
                wp_enqueue_script('lf_bootstrap_js', WF_BASE_URL . 'views/js/bootstrap.min.js', array('jquery'), filemtime(WF_BASE_DIR.'views/js/bootstrap.min.js'), true);
            }

            // Check if Date field is present in the custom form.
            if (quoteupDateFieldExists()) {
                // jQuery UI date picker
                wp_enqueue_style('lf_jquery_ui', WF_BASE_URL . 'views/css/jquery-ui.css', array(), filemtime(WF_BASE_DIR.'views/css/jquery-ui.css'));
                wp_enqueue_script('jquery-ui-core');
                // jQuery UI date picker
                wp_enqueue_script('jquery-ui-datepicker');
            }

            // Check if Rating field is present in the custom form.
            if (quoteupRatingFieldExists()) {
                wp_enqueue_style('lf_rateit_css', WF_BASE_URL. 'views/css/rateit.css', array(), filemtime(WF_BASE_DIR.'views/css/rateit.css'));
                wp_enqueue_script('lf_jquery_rateit_js', WF_BASE_URL . 'views/js/jquery.rateit.min.js', array('jquery'), filemtime(WF_BASE_DIR.'views/js/jquery.rateit.min.js'), true);
                wp_enqueue_script('cf-handle-rating', WF_BASE_URL . 'views/js/handle-rating.js', array('jquery'), array('jquery'), filemtime(WF_BASE_DIR.'views/js/handle-rating.js'), true);
            }

            quoteupCustomFormErrorMessages();

            // wp_enqueue_style('lf_select2_css', WF_BASE_URL . 'views/css/select2.css');
            // wp_enqueue_style('lf_jquery_ui_timepicker_addon_css', WF_BASE_URL.'views/css/jquery-ui-timepicker-addon.css');

            // wp_enqueue_script('jquery-form');
            // wp_enqueue_script("lf_mustache_js", WF_BASE_URL . "views/js/mustache.js");
            // wp_enqueue_script("lf_sha256_js", WF_BASE_URL . "views/js/sha256.js");
            // wp_enqueue_script("jquery-ui-sortable");

            // wp_enqueue_script("jquery-ui-slider");
            // wp_enqueue_script('lf_jquery_ui_timepicker_addon_js', WF_BASE_URL . 'views/js/jquery-ui-timepicker-addon.js', array('jquery', 'jquery-ui-core','jquery-ui-datepicker', 'jquery-ui-slider'));

            // wp_enqueue_script("jquery-ui-draggable");
            // wp_enqueue_script("jquery-ui-droppable");
            // wp_enqueue_script("jquery-select2-jquery-js", WF_BASE_URL . "views/js/select2.js");
        }
    }


    /**
     * @function admin_enqueue_scripts
     * @uses     Add the JS and CSS dependencies for loading on the admin accessible sections
     */
    function admin_enqueue_scripts()
    {
        if ((get_post_type()!='form' && !(isset($_GET['post_type']) && $_GET['post_type']=='form')) && (!isset($_GET['page']) || $_GET['page'] != "quoteup-create-quote")) {
            return;
        }
        wp_enqueue_style("lf_bootstrap_css", WF_BASE_URL . "views/css/bootstrap.min.css");
        wp_enqueue_style("lf_bootstrap_theme_css", WF_BASE_URL . "views/css/bootstrap-theme.min.css");
        wp_enqueue_style("lf_fontawesome_css", WF_BASE_URL . "views/css/font-awesome.min.css");
        wp_enqueue_style("lf_style_css", WF_BASE_URL . "views/css/style.css");
        wp_enqueue_style('lf_select2_css', WF_BASE_URL. "views/css/select2.css");

        //jQuery UI datetime picker
        wp_enqueue_style('lf_jquery_ui', WF_BASE_URL . "views/css/jquery-ui.css");
        wp_enqueue_style('lf_jquery_ui_timepicker_addon_css', WF_BASE_URL.'views/css/jquery-ui-timepicker-addon.css');
        wp_enqueue_style("font-awesome", WF_BASE_URL."views/css/font-awesome.min.css");

        // RateIt!
        wp_enqueue_style('lf_rateit_css', WF_BASE_URL. "views/css/rateit.css");

        wp_enqueue_script("jquery");
        wp_enqueue_script('jquery-form');
        wp_register_script('jquery-validation-plugin', WF_BASE_URL.'views/js/jquery.validate.min.js', array('jquery'));
        wp_enqueue_script('jquery-validation-plugin');
        wp_enqueue_script("lf_bootstrap_js", WF_BASE_URL . "views/js/bootstrap.min.js");
        wp_enqueue_script("lf_mustache_js", WF_BASE_URL . "views/js/mustache.js");
        wp_enqueue_script("jquery-ui-core");
        wp_enqueue_script("jquery-ui-sortable");

        //jQuery UI datetime picker
        wp_enqueue_script("jquery-ui-datepicker");
        wp_enqueue_script("jquery-ui-slider");
        wp_enqueue_script('lf_jquery_ui_timepicker_addon_js', WF_BASE_URL.'views/js/jquery-ui-timepicker-addon.js', array('jquery', 'jquery-ui-core','jquery-ui-datepicker', 'jquery-ui-slider'));

        wp_enqueue_script("jquery-ui-draggable");
        wp_enqueue_script("jquery-ui-droppable");
        // wp_enqueue_script("jquery-select2-jquery-js", WF_BASE_URL . "views/js/select2.js");

        // RateIt!
        wp_enqueue_script("lf_jquery_rateit_js", WF_BASE_URL . "views/js/jquery.rateit.min.js");

        wp_enqueue_script('customJs', WF_BASE_URL . "views/js/common.js");
        wp_enqueue_style('customCss', WF_BASE_URL . "views/css/common2.css");
        wp_localize_script(
            'customJs',
            'dateData',
            array(
            'date_format' => $this->dateFormatTojQueryUIDatePickerFormat(get_option('date_format')),
            )
        );

        // Validation Localization
        quoteupCustomFormErrorMessages();

        //  Beacon hook will display on New Form and Edit Form .
        do_action('quoteup_pep_backend_page');
    }

    /**
     * Convert a date format to a jQuery UI DatePicker format.
     *
     * @param string $dateFormat a date format
     *
     * @return string
     */
    function dateFormatTojQueryUIDatePickerFormat($dateFormat)
    {
        $chars = array(
            // Day
            'd' => 'dd', 'j' => 'd', 'l' => 'DD', 'D' => 'D',
            // Month
            'm' => 'mm', 'n' => 'm', 'F' => 'MM', 'M' => 'M',
            // Year
            'Y' => 'yy', 'y' => 'y',
        );

        return strtr((string) $dateFormat, $chars);
    }

    /**
     * @function add_meta_box
     * @uses     Adds the metaboxes in the 'Form' creation
     *        section of the Administration dashboard
     *        -- Form creation panel
     *        -- Agent selection panel
     */
    function add_meta_box($post_type)
    {
        $post_types = array('form'); //limit meta box to certain post types
        //if (in_array($post_type, $post_types)) {
        // Add the 'Form' creation panel
        add_meta_box(
            'createnew',
            __("Form builder", QUOTEUP_TEXT_DOMAIN),
            array($this, 'view_createnew'),
            'form',
            'advanced',
            'high'
        );
        // Add the 'Agent selection panel'
        // add_meta_box(
        //     'agents'
        //     , __('Assign agents', QUOTEUP_TEXT_DOMAIN)
        //     , array($this, 'view_list_agents')
        //     , 'form'
        //     , 'side'
        //     , 'high'
        // );
    }

    /**
     * Register Form Settings meta box.
     *
     * @since 6.5.0
     *
     * @return void
     */
    function register_form_settings_meta_box()
    {
        add_meta_box(
            'form_settings',
            __('Form Settings', QUOTEUP_TEXT_DOMAIN),
            array($this, 'render_form_settings_metabox_content'),
            'form',
            'side'
        );
    }

    /**
     * Render the custom form settings.
     *
     * @since 6.5.0
     *
     * @param WP_Post $post The post object.
     *
     * @return void
     */
    function render_form_settings_metabox_content($post)
    {
        $labels_status = get_post_meta($post->ID, 'show_labels_to_fields', true);
   
        // Display the form settings.
        ?>
        <ul>
            <li>
                <label>
                    <input type="checkbox" id="show_labels_to_fields" name="show_labels_to_fields" value="1" <?php checked('1', $labels_status); ?> />
                    <?php _e('Show labels to form fields', QUOTEUP_TEXT_DOMAIN); ?>
                </label>
            </li>
        </ul>
        <?php
    }

    /**
     * @function form_post_type_init
     * @uses     Initiate the custom post type
     */
    function form_post_type_init()
    {
        $form_post_type_labels = array(
            'name' => _x('Wisdm Forms', 'post type general name', QUOTEUP_TEXT_DOMAIN),
            'singular_name' => _x('Form', 'post type singular name', QUOTEUP_TEXT_DOMAIN),
            'menu_name' => _x('Wisdm Forms', 'admin menu', QUOTEUP_TEXT_DOMAIN),
            'name_admin_bar' => _x('Form', 'add new on admin bar', QUOTEUP_TEXT_DOMAIN),
            'add_new' => _x('Add New', 'book', QUOTEUP_TEXT_DOMAIN),
            'add_new_item' => __('Add New Form', QUOTEUP_TEXT_DOMAIN),
            'new_item' => __('New Form', QUOTEUP_TEXT_DOMAIN),
            'edit_item' => __('Edit Form', QUOTEUP_TEXT_DOMAIN),
            'view_item' => __('View Form', QUOTEUP_TEXT_DOMAIN),
            'all_items' => __('All Forms', QUOTEUP_TEXT_DOMAIN),
            'search_items' => __('Search Forms', QUOTEUP_TEXT_DOMAIN),
            'parent_item_colon' => __('Parent Forms:', QUOTEUP_TEXT_DOMAIN),
            'not_found' => __('No forms found.', QUOTEUP_TEXT_DOMAIN),
            'not_found_in_trash' => __('No forms found in Trash.', QUOTEUP_TEXT_DOMAIN),
        );

        $form_post_type_args = array(
            'labels' => $form_post_type_labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => false,
            'query_var' => true,
            'rewrite' => array('slug' => 'wisdmform'),
            'capability_type' => 'page',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title'),
            'menu_icon' => 'dashicons-feedback'
        );

        register_post_type('form', $form_post_type_args);
    }

    /**
     * @function action_save_form
     * @uses     Save the form after creation through the 'Form' creation panel
     */
    function action_save_form($post_id)
    {
        $error = false;
        if (isset($_REQUEST['contact'])) {
            $formadata = $_REQUEST['contact'];
            if (count($formadata) > 0 && get_post_type() == 'form') {
                foreach ($formadata['fieldsinfo'] as $key => $value) {
                    if (isset($value['primary']) && $value['primary'] == 'yes') {
                        $formadata['fieldsinfo'][$key]['required'] = 1;
                        if (empty($formadata['fieldsinfo'][$key]['reqmsg'])) {
                            $formadata['fieldsinfo'][$key]['reqmsg'] = "Please enter ".$value['label'];
                        }
                    }
                }

                $labelsArray = array();

                foreach ($formadata['fieldsinfo'] as $key => $value) {
                    if (isset($value['conditioned']) && $value['conditioned'] == 1 && isset($value['condition'])) {
                        foreach ($value['condition']['field'] as $k => $v) {
                            if ($v == $value['condition']['value'][$k]) {
                                $error = new WP_Error('conditional_error', 'you can not have identical fields in conditional logic. Form not updated');
                            }
                        }
                    }
                }


                foreach ($formadata['fieldsinfo'] as $key => $value) {
                    $labelsArray[] = $value['label'];
                }

                $duplicateResult = $this->hasDuplicate($labelsArray);

                if ($error) {
                    if (!session_id()) {
                        session_start();
                    }
                    $_SESSION['form_errors'] = $error->get_error_message();
                    return;
                }
                // if($duplicateResult) {
                //     return false;
                // } else {
                    update_post_meta($post_id, 'form_data', $formadata);
                    update_post_meta($post_id, 'frontend_owner_id', get_current_user_id());
                // }
            }
        }

        /**
         * After custom form data is updated.
         *
         * @since 6.5.0
         *
         * @param  int  $post_id  The ID of the post being saved.
         */
        do_action('quoteup_after_custom_form_data_updated', $post_id);

        /*
         * Check if this form is set as enquiry form. If yes, then don't allow to change
         * the status to other than 'publish'.
         */
        $quoteSettings = get_option('wdm_form_data');
        $editPageUrl   = false;
        $formStatus    = get_post_status($post_id);

        if (isset($quoteSettings['enquiry_form']) && 'custom' == $quoteSettings['enquiry_form'] && isset($quoteSettings['custom_formId']) && $post_id == $quoteSettings['custom_formId'] && 'publish' != $formStatus) {
            // unhook this function so it doesn't loop infinitely
            remove_action('save_post_form', array($this, 'action_save_form'));
            do_action('quoteup_remove_hooks_before_form_status_update');
            // update the post status to 'publish'
            wp_update_post(array('ID' => $post_id, 'post_status' => 'publish'));
            // re-hook this function
            add_action('save_post_form', array($this, 'action_save_form'));
            do_action('quoteup_add_hooks_after_form_status_update');

            if ('trash' == $formStatus) {
                $editPageUrl = get_edit_post_link($post_id, '');
                $error = new WP_Error('form_status_error', 'Form can\'t be trashed because it is set as an enquiry form.');
            } else {
                $error = new WP_Error('form_status_error', 'Form updated, but the form status can\'t be changed to other than the \'publish\' state, because it is set as the enquiry form.');
            }
        }

        if ($error) {
            if (!session_id()) {
                session_start();
            }
            $_SESSION['form_errors'] = $error->get_error_message();

            if ($editPageUrl) {
                wp_redirect($editPageUrl);
                exit();
            }
            return;
        }
    }

    /**
     * Save the meta box setting 'Show labels to form fields'.
     *
     * Callback for action 'quoteup_after_custom_form_data_updated'.
     *
     * @since 6.5.0
     */
    function save_form_fields_labels_status($post_id)
    {
        // Sanitize the user input.
        $labels_status = isset($_POST['show_labels_to_fields']) ? filter_var($_POST['show_labels_to_fields'], FILTER_SANITIZE_NUMBER_INT) : 0;

        // Update the meta field.
        if (1 == $labels_status) {
            update_post_meta($post_id, 'show_labels_to_fields', $labels_status);
            return;
        }
        
        update_post_meta($post_id, 'show_labels_to_fields', 0);
    }

    function my_admin_notice()
    {
        @session_start();
        if (isset($_SESSION) && array_key_exists('form_errors', $_SESSION)) {?>
            <div class="error">
                <p><?php echo $_SESSION['form_errors']; ?></p>
            </div><?php

            unset($_SESSION['form_errors']);
        }
    }



    function hasDuplicate($array)
    {
        $dups = array();
        foreach (array_count_values($array) as $val => $c) {
            if ($c > 1) {
                return true;
            }
        }
        return false;
    }



    /**
     * @function ajax_get_request_list
     * @uses     Respond to ajax request for list of "List of entry replies"
     * @return   string HTML output for the table of requests
     */
    function ajax_get_request_list()
    {
        if ($this->is_ajax() && isset($_REQUEST['section']) && $_REQUEST['section'] == 'stat_req') {
            $_REQUEST['paged'] = 1;
            $ajax_html = $this->action_get_reqlist(
                $args = array(
                'form_id' => (int)$_REQUEST['form_id'],
                'status' => esc_attr($_REQUEST['status']),
                'template' => 'showreqs_ajax'
                )
            );
            echo $ajax_html;
            die();
        }
    }

    /**
     * @function ajax_submit_reply
     * @uses     Respond to ajax request for list of "List of entry replies"
     * @return   string HTML output for the recent reply
     */
    function ajax_submit_reply()
    {
        if ($this->is_ajax() && isset($_REQUEST['section']) && $_REQUEST['section'] == 'reply') {
            // Add reply to DB
            $reply_id = $this->handle_replies();
            global $wpdb;
            $reply = $wpdb->get_row("select * from {$wpdb->prefix}wisdmform_conreqs where `id`='{$reply_id}'", ARRAY_A);
            $replier_id = $reply['uid'];
            if ($replier_id != -1) {
                $replier_data = get_userdata($replier_id);
                // if (!$replier_data) {
                //     $replier_id = $reply['agent_id'];
                //     $replier_data = get_userdata($replier_id);
                // }
                $reply['icon'] = md5(strtolower(trim($replier_data->user_email)));
            } else {
                $reply['icon'] = md5(rand());
            }

            if ($reply_id) {
                $image_code = base64_encode($reply['icon']);
                $reply_time = date('Y-m-d H:m', $reply['time']);
                $reply['user_name'] = esc_attr($_REQUEST['user_name']);
                $reply['data'] = esc_attr($reply['data']);
                $ajax_html = " <div class='media thumbnail'><div class='pull-left'>
                                    <img src='http://www.gravatar.com/avatar/{$image_code}' />
                                </div>
                                <div class='media-body'>
                                    <h3 class='media-heading'>{$reply['user_name']}</h3>
                                    ({$reply_time})
                                    <p>{$reply['data']}</p>
                                </div>
                            </div>";
                echo $ajax_html;
            } else {
                echo "<<div class='media thumbnail'><div class='pull-left'>"
                    . "Sorry!"
                    . "</div>"
                    . "<div class='media-body'>"
                    . "<h3 class='media-heading'>Failed</h3>"
                    . "<p>The reply could not be saved</p>"
                    . "</div></div>";
            }
            die();
        }
    }

    /**
     * @function ajax_submit_change_request_state
     * @uses     Respond to ajax request to change the state of a request
     * @global   type $wpdb Wordpress databse object
     */
    function ajax_submit_change_request_state()
    {
        if ($this->is_ajax() && isset($_REQUEST['action']) && $_REQUEST['action'] == 'change_req_state') {
            if (isset($_REQUEST['ids'])) {
                foreach ($_REQUEST['ids'] as $id) {
                    $ids[] = (int)$id;
                }
                $ids = implode(",", $ids);
            }
            $args = array();

            if (isset($_REQUEST['status'])) {
                global $wpdb;
                $status = esc_attr($_REQUEST['status']);
                $query_status = esc_attr($_REQUEST['query_status']);
                $args['status'] = $query_status;
                $query = '';
                switch ($status) {
                    case "delete":
                        $query = "delete from {$wpdb->prefix}wisdmform_conreqs where `id` in ({$ids})";
                        break;
                    default:
                        $query = "update {$wpdb->prefix}wisdmform_conreqs set `status`='{$status}' where `id` in ({$ids})";
                        //                        $get_count_query = "select * from {$wpdb->prefix}wisdmforms_conreqs where `status`='{$query_status}'";
                        //                        $new_stat_count = $wpdb->query($get_count_query, ARRAY_A);
                }
                $query = apply_filters('wisdmform_form-entries_action_query', $query, $status, $ids);
                $wpdb->query($query);

                // Get counts
                $form_id = (int)$_REQUEST['form_id'];
                $get_count_query = "select * from {$wpdb->prefix}wisdmform_conreqs where `status`='{$query_status}' and `fid`='{$form_id}'";
                $request_count = $wpdb->query($get_count_query, ARRAY_A);
            }

            if (isset($_REQUEST['form_id'])) {
                $form_id = (int)$_REQUEST['form_id'];
                $args['form_id'] = $form_id;
            }

            $args['template'] = 'showreqs_ajax';
            $ajax_html = $this->action_get_reqlist($args);

            $data = array(
                'count' => $request_count,
                'html' => $ajax_html,
                'changed' => isset($_REQUEST['ids']) ? count($_REQUEST['ids']) : 0
            );
            echo json_encode($data);
            die();
        }
    }

    /**
     * @function view_public_token
     * @uses     Render view for Token/Query entry page
     * @return   type string(html)
     */
    function view_public_token()
    {
        $html = '';
        if (isset($_REQUEST['section'])) {
            $args = array();
            // Check if token was given
            if (isset($_REQUEST['token'])) {
                // If token exists request has to be fetched
                if ($_REQUEST['token'] == '') {
                    return 'No requests found';
                }
                $args = array('token' => esc_attr($_REQUEST['token']));
            }
            // If a reply was given using a token
            if ($_REQUEST['section'] == 'reply') {
                // Save the replies
                $this->handle_replies(); // @TODO make a separate function called "handle_public_replies"
            }

            $html .= $this->view_get_request_data($args);
        } else {
            // No token given. Render the regular query view
            $html_data = array();
            $html .= $this->get_html('query', $html_data);
        }

        return $html;
    }

    /**
     * @function view_agent
     * @uses     Render view for the agent
     * @return   type string HTML
     */
    // function view_agent() {
    //     $html = '';
    //     // Check if the current user is agent
    //     if (current_user_can('agent')) {
    //         // Validate if a certain section was requested
    //         if (isset($_REQUEST['section'])) {
    //             // Setup default arguments to fetch data for the view HTML
    //             $args = array(
    //                 'fid' => (int)$_REQUEST['form_id'],
    //             );
    //             // Return HTML for request/entry list
    //             switch ($_REQUEST['section']) {
    //                 case 'requests':
    //                     $html .= $this->view_agent_requests();
    //                     break;
    //                 case 'request':
    //                     $args['reply_for'] = (int)$_REQUEST['req_id'];
    //                     $html .= $this->view_get_request_data($args);
    //                     break;
    //                 case 'reply':
    //                     $this->handle_replies();
    //                     $html .= $this->view_get_request_data($args);
    //                     break;
    //             }
    //         } else {
    //             // Generate list of assigned forms
    //             $html_data = array();
    //             $agent_forms = get_user_meta($user_id = get_current_user_id(), $meta_key = 'form_ids', $single = true);
    //             $forms = array();
    //             if (is_array($agent_forms) && count($agent_forms) > 0) {
    //                 foreach ($agent_forms as $form) {
    //                     $forms[] = get_post($form_id = $form, ARRAY_A);
    //                 }
    //             }

    //             $html_data['agent_forms'] = $forms;
    //             $html .= $this->get_html('agent_dashboard', $html_data);
    //         }
    //     } else {
    //         $html_data = array();
    //         $html = $this->get_html('agent_login', $html_data);
    //     }

    //     return $html;
    // }

    /**
     * @function view_list_agent
     * @uses     Render view for the list of agents
     *        in the metabox for 'Agent selection' of a form
     * @return   type string HTML
     */
    // function view_list_agents($post) {
    //     $formdata = get_post_meta($post->ID, 'form_data', true);
    //     $agent_users = get_users(array('role' => 'agent'));
    //     $html_data = array(
    //         'agents' => $agent_users,
    //         'agent_id' => isset($formdata['agent']) ? $formdata['agent'] : null
    //     );
    //     $html = $this->get_html('list_agents', $html_data);
    //     echo $html;
    // }

    /**
     * @function view_agent_requests
     * @uses     Render view for the list of requests
     *        that are accessilble for the current
     *        logged in agent user
     * @return   type string HTML
     */
    // function view_agent_requests() {
    //     $html = "<div class='w3eden'>";
    //     $html .= '<div class="wrap">';
    //     if (current_user_can('agent')) {
    //         $html .= '<div id="icon-tools" class="icon32">'
    //             . '</div> ';
    //         if (isset($_REQUEST['form_id'])) {
    //             $args = array(
    //                 'form_id' => (int)$_REQUEST['form_id'],
    //             );
    //             if (isset($_REQUEST['status']))
    //                 $args['status'] = esc_attr($_REQUEST['status']);
    //             $args['template'] = 'showreqs';
    //             $html .= $this->action_get_reqlist($args);
    //         } else {
    //             $html .= 'You cannot manage this form';
    //         }
    //     } else {
    //         $html .= 'You are not an agent';
    //     }

    //     $html .= '</div></div>';

    //     return $html;
    // }

    /**
     * @function admin_view_submitted_forms
     * @uses     Render view for the list of requests
     *        for the Admin
     * @return   type string HTML
     */
    function admin_view_submitted_forms()
    {
        $html = '';
        $forms_list = query_posts('post_type=form');
        wp_reset_query();
        $select_html = "<div class='w3eden'>";
        $select_html .= "<div class='container-fluid'><div class='row row-bottom-buffer'><form class='form' method='post' action='' >";
        $select_html .= '<input type="hidden" name="section" value="requests" />';
        $select_html .= "<div class='col-md-11'><select class='form-control' name='form_id'><option>Select a form</option>";

        foreach ($forms_list as $form) {
            if (isset($_REQUEST['form_id']) && $_REQUEST['form_id'] == $form->ID) {
                $selected = 'selected="selected"';
            } else {
                $selected = '';
            }
            $select_html .= "<option {$selected} value='{$form->ID}'>{$form->post_title}</option>";
        }

        $select_html .= '</select></div>';
        $select_html .= "<div class='col-md-1 text-right'><button class='btn btn-primary' type='submit'>Go!</button></div>";
        $select_html .= "</form></div></div>";

        $html .= '<div class="wrap">'
            . '<div id="icon-tools" class="icon32">'
            . '</div> '
            . $select_html;

        if (isset($_REQUEST['section'])) {
            $section = esc_attr($_REQUEST['section']);
            if ($section == 'requests' && isset($_REQUEST['form_id'])) {
                $args = array(
                    'form_id' => (int)$_REQUEST['form_id'],
                    'template' => 'admin_showreqs'
                );

                $html .= $this->action_get_reqlist($args);
            }
            if ($section == 'request' && isset($_REQUEST['req_id'])) {
                $html .= $this->view_get_request_data(
                    $args = array(
                    'fid' => (int)$_REQUEST['form_id'],
                    'reply_for' => (int)$_REQUEST['req_id'],
                    'template' => 'admin_reply_req'
                    )
                );
            }
            if ($section == 'reply') {
                $this->handle_replies();
                $html .= $this->view_get_request_data(
                    $args = array(
                    'fid' => (int)$_REQUEST['form_id'],
                    'reply_for' => (int)$_REQUEST['req_id'],
                    'template' => 'admin_reply_req'
                    )
                );
            }
        }
        $html .= '</div></div>';
        echo $html;
    }


    /**
     * @function admin_view_global_stats
     * @uses     Render the statistics page in the Admin panel
     * @return   type string HTML
     */
    function admin_view_global_stats()
    {
        global $wpdb;

        $form_query = 'post_type=form';

        $all_stats_query = "SELECT * FROM {$wpdb->prefix}wisdmform_stats";
        $all_stats = $wpdb->get_results($all_stats_query, ARRAY_A);

        $forms_list = query_posts('post_type=form');
        wp_reset_query();

        $formtitles = array();
        $form_ids = array();
        foreach ($forms_list as $form) {
            $form_ids[$form->ID] = $form->post_title;
            $formtitles[$form->ID] = $form->post_title;
        }

        $max_views = -1;
        $max_submits = -1;

        $max_viewed_form = null;
        $max_submitted_form = null;

        $view_counts = array();
        $submit_counts = array();
        $view_count = 0;
        $submit_count = 0;
        $submit_count_stats = null;


        foreach ($all_stats as $stat) {
            switch ($stat['action']) {
                case 'v':
                    $view_counts[$stat['fid']] = isset($view_counts[$stat['fid']]) ? $view_counts[$stat['fid']]++ : 1;
                    $view_count += $view_counts[$stat['fid']];
                    $view_count_stats[$stat['fid']][] = array(
                    'ip' => $stat['ip'],
                    'time' => array(
                        'second' => date('Y-m-d H:m:s', $stat['time']),
                        'minute' => date('Y-m-d H:m', $stat['time']),
                        'hour' => date('Y-m-d h', $stat['time']),
                        'day' => date('Y-m-d', $stat['time']),
                        'month' => date('Y-m', $stat['time']),
                        'year' => date('Y', $stat['time'])
                    )
                    );
                    break;
                case 's':
                    $submit_counts[$stat['fid']] = isset($submit_counts[$stat['fid']]) ? $submit_counts[$stat['fid']]++ : 1;
                    $submit_count += $submit_counts[$stat['fid']];
                    $submit_count_stats[$stat['fid']][] = array(
                    'ip' => $stat['ip'],
                    'time' => array(
                        'second' => date('Y-m-d H:m:s', $stat['time']),
                        'minute' => date('Y-m-d H:m', $stat['time']),
                        'hour' => date('Y-m-d h', $stat['time']),
                        'day' => date('Y-m-d', $stat['time']),
                        'month' => date('Y-m', $stat['time']),
                        'year' => date('Y', $stat['time'])
                    )
                    );
                    break;
            }
            if (isset($formtitles[$stat['fid']])) {
                if ($view_count > $max_views) {
                    $max_views = $view_count;
                    $max_viewed_form = array(
                        'label' => $formtitles[$stat['fid']],
                        'value' => $stat['fid']
                    );
                }
                if ($submit_count > $max_submits) {
                    $max_submits = $submit_count;
                    $max_submitted_form = array(
                        'label' => $formtitles[$stat['fid']],
                        'value' => $stat['fid']
                    );
                }
            }
        }


        $stats = array(
            'max_submitted_form' => array(
                'label' => 'Most submitted form',
                'value' => $max_submitted_form
            ),
            'max_viewed_form' => array(
                'label' => 'Most viewed form',
                'value' => $max_viewed_form
            ),
            'total_forms' => array(
                'label' => 'Total number of forms',
                'value' => array(
                    'label' => 'Total forms',
                    'value' => count($forms_list)
                )
            )
        );

        // If a single form was requested
        if (isset($_REQUEST['form_id'])) {
            $selected_form_id = (int)$_REQUEST['form_id'];
        } else {
            $selected_form_id = 'none';
        }

        $html_data = array(
            'views' => json_encode($view_count_stats),
            'submits' => json_encode($submit_count_stats),
            'form_ids' => $form_ids,
            'selected_form_id' => $selected_form_id,
            'stats' => $stats
        );

        $html = $this->get_html('stats_global', $html_data);

        echo $html;
    }

    /**
     * View callers *
     */

    /**
     * @function view_createnew
     * @uses     Render the Form builder window for building form
     * @return   type string HTML
     */
    function view_createnew($post)
    {
        $formdata = get_post_meta($post->ID, 'form_data', $single = true);
        $html_data = array(
            'commonfields' => $this->fields_common,
            'generic_fields' => $this->fields_generic,
            'advanced_fields' => $this->fields_advanced,
            'methods_set' => $this->set_methods,
            'form_post_id' => $post->ID
        );
        if (!empty($formdata)) {
            $html_data['form_data'] = $formdata;
        }
        $view = $this->get_html("createnew", $html_data);
        echo $view;
    }

    function view_showform($params)
    {
        /**
         * When custom form shortcode is run.
         *
         * @since 6.5.0
         *
         * @param  array  $params  Cusrom form Id passed in the shortcode.
         */
        do_action('quoteup_custom_form_shortcode_run', $params);

        $form_id = $params['form_id'];
        $formdata = get_post_meta($form_id, 'form_data', true);
        if (!empty($formdata)) {
            $paginated_form = paginate_form(
                $formdata,
                array(
                'fields_common' => $this->fields_common,
                'fields_generic' => $this->fields_generic,
                'fields_advanced' => $this->fields_advanced
                )
            );
            $html_data = array_merge($paginated_form, array('form_id' => $form_id, 'formsetting' => $formdata));
            $view = $this->get_html("showform", $html_data);

            // Record the view
            // $this->record_view_stat($form_id, get_client_ip());
        } else {
            $view = "No forms defined";
        }
        return $view;
    }

    /**
     * Action callers *
     */
    /**
     * @function ajax_action_upadate_agent
     * @uses     Update the agent info using AJAX from the User
     * @return   type ajax response
     */
    // public function ajax_action_upadate_agent() {
    //     if ($this->is_ajax() and isset($_REQUEST['section']) and $_REQUEST['section'] == 'update_agent') {
    //         $agent_info = $_REQUEST['agentinfo'];
    //         $display_name = esc_attr($agent_info['display_name']);
    //         $password = esc_attr($agent_info['password']);
    //         $email = esc_attr($agent_info['email']);

    //         $response = '';

    //         if ($password != $agent_info['confirm_password']) {
    //             $reponse = array('message' => 'Password fields did not match', 'action' => 'danger');
    //         } else if (strlen($display_name)<5) {
    //             $reponse = array('message' => 'Display name must be at least 5 characters long', 'action' => 'danger');
    //         } else if (!is_valid_email($email)) {
    //             $reponse = array('message' => 'You must enter a valid email address', 'action' => 'danger');
    //         } else {
    //             $reponse = array('message' => 'Your profile has been updated successfully', 'action' => 'success');
    //             // Update the info
    //             $info = array(
    //                 'ID' => get_current_user_id(),
    //                 'display_name' => $display_name,
    //                 'email' => $email
    //             );
    //             if (strlen($password) > 0) {
    //                 $info['user_pass'] = $password;
    //             }
    //             wp_update_user($info);
    //         }

    //         $response = json_encode($reponse);

    //         echo $response;

    //         die();
    //     }
    // }

    /**
     * @function ajax_action_submit_form
     * @uses     Submit form using AJAX
     * @return   type ajax response
     */
    public function ajax_action_submit_form()
    {
        global $wpdb, $quoteup;
        $formSetting = quoteupSettings();
        if (isset($_REQUEST['g-recaptcha-response'])) {
            $secretKey          = $formSetting[ 'google_secret_key' ];
            $errorMsg           = isset($formSetting['google_captcha_err_msg']) && !empty($formSetting['google_captcha_err_msg']) ? quoteupReturnWPMLVariableStrTranslation($formSetting['google_captcha_err_msg']) : __('Captcha could not be verified.', QUOTEUP_TEXT_DOMAIN);
            $response           = isset($_REQUEST['g-recaptcha-response']) ? $_REQUEST['g-recaptcha-response'] : '';
            $verify             = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$response}");
            $captcha_success    = json_decode($verify);
            if (!$captcha_success->success || (quoteupIsCaptchaVersion3($formSetting) && !quoteupVerifyCaptchaV3($captcha_success))) {
                //This user was not verified by recaptcha.
                echo json_encode(
                    array(
                    'status'     => 'failed',
                    'message'    => $errorMsg,
                    )
                );
                die();
            }
        }
        if (isset($formSetting['deactivate_nonce']) && $formSetting['deactivate_nonce'] == 1) {
            $nonce = true;
        } else {
            $nonce = wp_verify_nonce($_REQUEST['__iswisdmform'], NONCE_KEY);
        }
        if (!$nonce) {
            $return_data = array();
            $return_data['message'] = apply_filters("wisdmform_submitform_error_message", 'Invalid Data!');
            $return_data['action'] = 'danger';
            echo json_encode($return_data);
            die();
        }
        $form_id = (int)$_REQUEST['form_id'];

        // Update the submit count for this form
        // $this->record_submission_stat($form_id, get_client_ip());

        $file_paths = array();
        $form_data = get_post_meta($form_id, 'form_data', true);
        $validMedia = validateAttachField($quoteup);
        // if (count($_FILES)) {
        //     foreach ($_FILES['upload']['name'] as $file_index => $file_name) {
        //         //Code to check max allowed file size upload
        //         $maxSize = $form_data['fieldsinfo'][$file_index]['filesize'];
        //         $maxSize = $maxSize * 1024 * 1024;
        //         $mediaSize = $_FILES['upload']['size'][$file_index];
        //         if ($mediaSize > $maxSize) {
        //             $message = sprintf(__('Size of file you are trying to upload is %s which is too large. Max file size allowed is %s', QUOTEUP_TEXT_DOMAIN), $this->formatFileSizeUnits($mediaSize), $this->formatFileSizeUnits($maxSize));
        //             echo json_encode(
        //                 array(
        //                 'status'     => 'failed',
        //                 'message'    => $message,
        //                 )
        //             );
        //             die();
        //         }
        //         //End of Code to check max allowed file size upload
                
        //         $allowedExtension = $form_data['fieldsinfo'][$file_index]['extensions'];
        //         $mediaInfo = pathinfo($file_name);
        //         $mediaExtension = strtolower($mediaInfo[ 'extension' ]); //media extension
        //         $allowedExtensionArray = explode(',', $allowedExtension);
        //         if(!in_array('.'.$mediaExtension, $allowedExtensionArray)) {
        //             $message = sprintf(__('File extension not allowed. Allowed extensions are %s', QUOTEUP_TEXT_DOMAIN), $allowedExtension);
        //             echo json_encode(
        //                 array(
        //                 'status'     => 'failed',
        //                 'message'    => $message,
        //                 )
        //             );
        //             die();
        //         }
        //         $prepend_key = uniqid("wisdmform", true) . '_';
        //         if(!file_exists(WF_UPLOAD_PATH)){
        //             mkdir(WF_UPLOAD_PATH);
        //             \WPDM\FileSystem::blockHTTPAccess(WF_UPLOAD_PATH);
        //         }
        //         $new_path = WF_UPLOAD_PATH . $prepend_key . $file_name;

        //         $ext = explode(".", $file_name);
        //         $ext = end($ext);
        //         $unsafe_file_exts = array('.php', '.js', '.html');
        //         $unsafe_file_exts = apply_filters("wisdmform_blocked_file_exts", $unsafe_file_exts);
        //         if(!in_array($ext, $unsafe_file_exts)) {
        //             move_uploaded_file($_FILES['upload']['tmp_name'][$file_index], $new_path);
        //             $file_paths[$file_index] = $new_path;
        //         }
        //     }
        // }


        $data = isset($_REQUEST) ? $_REQUEST : array();


        if (count($file_paths)) {
            $data = array_merge($data, $file_paths);
        }
        $data = serialize($data);
        $token = uniqid();
        $emails = $this->entry_has_emails(maybe_unserialize($data));
        $form_entry = array('data' => $data, 'fid' => $form_id, 'status' => 'new', 'token' => $token, 'time' => time());

        $form_entry = apply_filters("wisdmform_before_form_submit", $form_entry);

        do_action("wisdmform_before_form_submit", $form_entry);

        $form_entry = maybe_unserialize($form_entry);
        $data = maybe_unserialize($form_entry['data']);

        $name = $data['submitform']['custname'];
        $email = $data['submitform']['txtemail'];

        $product_table_and_details = getEmailAndDbDataOfProducts($formSetting, $data['submitform']);
        $product_details           = setProductDetails($product_table_and_details);
        $gaProducts                = setGaProducts($product_table_and_details);
        $authorEmail               = setAuthorEmail($product_table_and_details);
        $address                   = getEnquiryIP();
        $type                      = 'Y-m-d H:i:s';
        $date                      = current_time($type);
        $totalPrice                = quoteupReturnSumOfProductPrices($product_details);
        $tbl                       = getEnquiryDetailsTable();

        if ($wpdb->insert(
            $tbl,
            array(
                'name' => $name,
                'email' => $email,
                'enquiry_ip' => $address,
                'product_details' => serialize($product_details),
                'enquiry_date' => $date,
                'total' => $totalPrice,
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%f',
            )
        )
        ) {
            $enquiryID = $wpdb->insert_id;
            processEnquiryAfterInsertion($enquiryID, $product_details, $quoteup, true, $data['submitform'], $authorEmail, $formSetting['default_sub']);
        }

        $return_data = array();
        $return_data['message'] = apply_filters("wisdmform_submitform_thankyou_message", stripslashes($form_data['thankyou']));
        $return_data['action'] = 'success';
        $return_data['gaProducts'] = $gaProducts;
        $return_data['enquiryID'] = $enquiryID;
        echo json_encode($return_data);
        die();
    }



    /**
     * Converts Number of Bytes to Human Readable Format.
     *
     * @param type $bytes Number of Bytes
     *
     * @return string Conversion of Number to Human Readable format
     */
    function formatFileSizeUnits($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2).' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2).' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2).' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes.' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes.' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }


    /**
     * @function add_payment_verification_hook
     * @uses     Payment verification hook
     * @return   type ajax response
     */
    function add_payment_verification_hook()
    {
        $pay_object = new WisdmForm_Payment();
        $pay_object->payment_verification();
    }

    /**
     * Library to get template *
     */
    /**
     * @function get_html
     * @uses     Main rendering engine for views
     * @return   type HTML output
     */
    function get_html($view, $html_data)
    {
        if (empty($view)) {
            return null;
        }
        extract($html_data);
        ob_start();
        include(WF_BASE_DIR . "views/{$view}.php");
        $data = ob_get_clean();
        return $data;
    }


    /**
     * @function entry_has_emails
     * @uses     Check if form submission (form structure) has any email fields or not
     * @returns  List of emails submitted via the form
     * @return   type formatted array of string
     */
    function entry_has_emails($data)
    {
        $emails = array();
        if (!is_array($data)) {
            return $emails;
        }
        foreach ($data as $value) {
            if (is_valid_email($value)) {
                $emails[] = $value;
            }
        }
        return $emails;
    }

    /**
     * @function view_get_request_data
     * @uses     Gather and return all the data submitted during a form submission along
     *          with any responses done afterwards to that request
     * @return   type HTML output
     */
    function view_get_request_data($args = array())
    {
        global $wpdb;
        // initialize view output

        if (!$args || count($args) == 0) {
            return "No requests found";
        }

        if (isset($args['template'])) {
            $template_name = $args['template'];
            unset($args['template']);
        }
        $html = '';
        // Build the query
        $request_data_query = "select * from {$wpdb->prefix}wisdmform_conreqs where ";
        $tmp = array();
        foreach ($args as $key => $value) {
            $tmp[] = "`{$key}`='{$value}'";
        }
        $args_query = implode(" and ", $tmp);
        $request_data_query .= $args_query;


        // Check if token was used to access the response
        // If token is used then fetch the reqply_history using the id of the token
        if (isset($args['token'])) {
            $reply_db_fetch = $wpdb->get_row($request_data_query, ARRAY_A);

            // Terminate further execution since token enquiry is invalid
            if (count($reply_db_fetch) < 1) {
                return "No requests found";
            }

            $args = array(); // rebuild args for second query
            $args['fid'] = $reply_db_fetch['fid'];
            $args['reply_for'] = $reply_db_fetch['id'];
            $request_data_query = "select * from {$wpdb->prefix}wisdmform_conreqs where ";
            $tmp = array();
            foreach ($args as $key => $value) {
                $tmp[] = "`{$key}`='{$value}'";
            }
            $args_query = implode(" and ", $tmp);
            $request_data_query .= $args_query;
        }

        $request_data_query .= " order by `time` desc";
        $reply_db_fetch = $wpdb->get_results($request_data_query, ARRAY_A);

        $req_data = $wpdb->get_row("select * from {$wpdb->prefix}wisdmform_conreqs where `id`='{$args['reply_for']}'", ARRAY_A);
        $form_data = get_post_meta($args['fid'], 'form_data', true);
        $field_values = unserialize($req_data['data']);
        $reply_user_name = '';
        foreach ($form_data['fields'] as $key => $field) {
            if ($field == 'name') {
                $reply_user_name = esc_attr($field_values[$key]);
            }
        }

        if (!isset($_REQUEST['token'])) {
            $current_user = wp_get_current_user();
            $user_name = $current_user->user_login;
        }

        $reply_history = array();
        foreach ($reply_db_fetch as $reply) {
            if ($reply['replied_by'] == 'user') {
                if ($reply['uid'] == -1) {
                    $tmp_user = null;
                } else {
                    $tmp_user = get_userdata(intval($reply['uid']));
                }
            } else {
                $tmp_user = get_userdata(intval($reply['agent_id']));
            }
            $tmp_reply = $reply;
            $tmp_reply['username'] = $tmp_user != null ? $tmp_user->user_login : $reply_user_name;
            $tmp_reply['icon'] = md5(strtolower(trim($tmp_user->user_email)));
            $reply_history[] = $tmp_reply;
        }
        $html_data = array();
        $html_data['reply_history'] = $reply_history;
        $html_data['form_fields'] = $form_data['fieldsinfo'];
        $html_data['field_values'] = $field_values;
        $html_data['req_data'] = $req_data;
        $html_data['current_user_name'] = isset($_REQUEST['token']) ? $reply_user_name : $user_name;

        $html .= $this->get_html(isset($template_name) ? $template_name : 'reply_req', $html_data);
        return $html;
    }

    /**
     * @function handle_replies
     * @uses     Record replies done via the response system
     * @return   type Reply insertion id
     */
    function handle_replies()
    {
        global $wpdb;
        $user_id = is_user_logged_in() ? get_current_user_id() : -1;

        $reply_data = array();
        // if (!current_user_can('agent') && !current_user_can('manage_options')) {
            $reply_data['uid'] = $user_id;
            $reply_data['replied_by'] = "user";
        // } else {
        //     $reply_data['agent_id'] = $user_id;
        //     $reply_data['replied_by'] = "agent";
        // }
        $reply_data['data'] = esc_attr($_REQUEST['reply_msg']);
        $req_id = $reply_data['reply_for'] = (int)$_REQUEST['req_id'];
        $reply_data['fid'] = (int)$_REQUEST['form_id'];
        $reply_data['time'] = time();


        if ($_REQUEST['req_status'] == "new") { // no previous replies have been issued
            $request_status_update_query = "update {$wpdb->prefix}wisdmform_conreqs set `status`='inprogress' where `id`='{$req_id}'";
            $wpdb->query($request_status_update_query);
        }
        $sql_part = '';
        $tmp_sqls_parts = array();
        foreach ($reply_data as $key => $value) {
            $tmp_sqls_parts[] = "`{$key}`='{$value}'";
        }
        $sql_part = implode(", ", $tmp_sqls_parts);
        $reply_add_query = "insert into {$wpdb->prefix}wisdmform_conreqs set {$sql_part}";
        $wpdb->query($reply_add_query);

        return $wpdb->insert_id;
    }

    /**
     * @function action_get_reqlist
     * @uses     Get a list of requests submitted via a particular form
     * @return   type html render ouptut
     */
    function action_get_reqlist($args)
    {
        global $wpdb;

        $form_id = $args['form_id'];

        if (!isset($args['fid'])) {
            $args['fid'] = $form_id;
            unset($args['form_id']);
        }

        if (isset($args['template'])) {
            $template_name = $args['template'];
            unset($args['template']);
        }


        $count_query_prefix = "select count(*) from {$wpdb->prefix}wisdmform_conreqs where ";
        $query_prefix = "select * from {$wpdb->prefix}wisdmform_conreqs where ";
        $query_args = array();

        foreach ($args as $key => $value) {
            $query_args[] = "`{$key}` = '{$value}'";
        }

        $query_suffix = implode(" and ", $query_args);
        if (!isset($args['token'])) {
            $query_suffix .= " and `token` != ''";
        }
        $query = $query_prefix . $query_suffix;
        $count_query = $count_query_prefix . $query_suffix;
        $req_count = $wpdb->get_row($count_query, ARRAY_A);

        // Counting query states [new, inprogress, onhold, resolved]
        $new_request_query = $count_query . " and `status`='new'";
        $new_request_count = $wpdb->get_row($new_request_query, ARRAY_A);
        $inprogress_request_query = $count_query . " and `status`='inprogress'";
        $inprogress_request_count = $wpdb->get_row($inprogress_request_query, ARRAY_A);
        $onhold_request_query = $count_query . " and `status`='onhold'";
        $onhold_request_count = $wpdb->get_row($onhold_request_query, ARRAY_A);
        $resolved_request_query = $count_query . " and `status`='resolved'";
        $resolved_request_count = $wpdb->get_row($resolved_request_query, ARRAY_A);

        //Pagination
        $items_per_page = isset($_REQUEST['ipp']) ? (int)$_REQUEST['ipp'] : 20;
        $page_id = isset($_REQUEST['paged']) ? intval($_REQUEST['paged']) - 1 : 0 ;
        $starting_item = intval($page_id) * intval($items_per_page);
        $query .= " limit {$starting_item}, {$items_per_page}";

        $form_meta = get_post_meta($form_id, 'form_data', true);
        $form_title = get_post_field('post_title', $form_id);

        $reqlist = $wpdb->get_results($query, ARRAY_A);

        $form = array(
            'id' => $form_id,
            'title' => $form_title
        );

        $counts = array(
            'inprogress' => $inprogress_request_count['count(*)'],
            'new' => $new_request_count['count(*)'],
            'resolved' => $resolved_request_count['count(*)'],
            'onhold' => $onhold_request_count['count(*)']
        );

        if (empty($reqlist)) {
            return 'No requests found';
        }

        $html_data = array(
            'form' => $form,
            'form_fields' => $form_meta['fieldsinfo'],
            'reqlist' => $reqlist,
            'counts' => $counts,
            'total_request' => $req_count['count(*)'],
        );
        $form_html = $this->get_html(isset($template_name) ? $template_name : 'showreqs', $html_data);
        return $form_html;
    }


    /**
     * @function add_columns_to_form_list
     * @uses     Modify the form(post) list in the admin panel and add extra columns
     * @return   type modified list of colums for wp native post list
     */
    // function add_columns_to_form_list($column) {
    //     $column['form_id'] = 'Shortcode';
    //     $column['view_count'] = 'Views';
    //     $column['submit_count'] = 'Submissions';

    //     return $column;
    // }

    /**
     * @function populate_form_list_custom_columns
     * @uses     Fill up the custom columns added via the 'add_columns_to_form_list' method
     * @return   type null
     */
    // function populate_form_list_custom_columns($column_name, $post_id) {
    //     // $custom_field = get_post_custom($post_id);
    //     $view_count = get_post_meta($post_id, 'view_count', true) == '' ? 0 : get_post_meta($post_id, 'view_count', true);
    //     $submit_count = get_post_meta($post_id, 'submit_count', true) == '' ? 0 : get_post_meta($post_id, 'submit_count', true);
    //     switch ($column_name) {
    //         case 'form_id':
    //             echo "<input type='text' readonly='readonly' value='[wisdmform form_id={$post_id}]'/>";
    //             break;
    //         case 'view_count':
    //             echo $view_count;
    //             break;
    //         case 'submit_count':
    //             echo $submit_count;
    //             break;
    //         default:
    //     }
    // }

    /**
     * @function form_preview
     * @uses     Generate a preview of form
     * @return   type  HTML render string
     */
    function form_preview($content)
    {
        if (get_post_type() != "form") {
            return $content;
        }
        return do_shortcode("[wisdmform form_id='" . get_the_ID() . "']");
    }

    /**
     * @function show_captcha_image
     * @uses     Generate and server a captcha image
     * @return   type  binary image file
     */
    function show_captcha_image()
    {
        if (isset($_REQUEST['show_captcha'])) {
            $coj = new SimpleCaptcha();
            echo json_encode($coj->get_image());
            die();
        }
    }

    function payment_fields($submission)
    {
        $payment_fields = array();
        foreach ($submission as $key => $value) {
            if (strstr($key, 'PaymentMethods_')) {
                $payment_fields = array('field' => $key,
                    'method' => $submission[$key]
                );
                return $payment_fields;
            }
        }

        return null;
    }

    /**
     * @function has_payment_fields
     * @uses     Checks if submission has any pay methods
     * @return   type boolean
     */
    function has_payment_fields($submission)
    {
        foreach ($submission as $key => $value) {
            if (strstr($key, 'PaymentMethods_')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @function record_view_stat
     * @uses     Record and increment the view count of a form by 1 and store the ip used
     * @return   type  null
     */
    function record_view_stat($form_id, $ip = 'not acquired')
    {
        global $wpdb;
        $form_data = get_post($form_id);
        $form_author_id = $form_data->post_author;
        $view_count = get_post_meta($form_id, 'view_count', true);
        if ($view_count == '') {
            $view_count = 0;
        }
        update_post_meta($form_id, 'view_count', $view_count + 1);

        $current_time = time();
        $wpdb->query("INSERT into {$wpdb->prefix}wisdmform_stats SET `fid`='{$form_id}', `author_id`='{$form_author_id}', `action`='v', `ip`='{$ip}', `time`='{$current_time}' ");
    }


    /**
     * @function get_field_names
     * @uses     Extract field names from serialized form data and prepare an array with ID => Label
     * @return   type array
     */
    function get_field_names($ef_data, $ef_form_data)
    {
        $ef_data = maybe_unserialize($ef_data);
        $ef_form_data = maybe_unserialize($ef_form_data);
        $ef_prep_fields = array();

        foreach ($ef_data as $ef_name => $ef_value) {
            $ef_prep_fields[$ef_name] = $ef_form_data['fieldsinfo'][$ef_name]['label'];
        }

        return $ef_prep_fields;
    }

    /**
     * @function record_submission_stat
     * @uses     Record and increment the submission count of a form by 1 and store the ip used
     * @return   type  null
     */
    function record_submission_stat($form_id, $ip = 'not acquired')
    {
        global $wpdb;
        $form_data = get_post($form_id);
        $form_author_id = $form_data->post_author;
        $submit_count = get_post_meta($form_id, 'submit_count', true);
        if ($submit_count == '') {
            $submit_count = 0;
        }
        update_post_meta($form_id, 'submit_count', $submit_count + 1);

        $current_time = time();
        $wpdb->query("INSERT into {$wpdb->prefix}wisdmform_stats SET `fid`='{$form_id}', `author_id`='{$form_author_id}', `action`='s', `ip`='{$ip}', `time`='{$current_time}' ");
    }

    function wisdmform_submitform_thankyou_message($message)
    {
        return $message;
    }

    /**
     * @function autoload_field_classes
     * @uses     Autoloader to load field classes when they are used
     * @return   type  null
     */
    public static function autoload_field_classes()
    {
        $field_class_directories = array(
            WF_BASE_DIR . 'formfields/common/',
            WF_BASE_DIR . 'formfields/generic/',
            WF_BASE_DIR . 'formfields/advanced/'
        );
        foreach ($field_class_directories as $dir) {
            $class_files = scandir($dir);
            for ($it=2; $it<count($class_files); $it++) {
                include $dir.$class_files[$it];
            }
        }
    }
}

/**
 * Initialize *
*/
//new wisdmform();

wisdmform::getInstance();

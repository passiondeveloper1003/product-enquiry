<?php

namespace Includes\Admin\Abstracts;

/**
* This class handles the edit quote page functions.
*/
abstract class QuoteupEdit
{
    /**
    * Function constructor to add actions.
    * Action for adding scripts and styles.
    * Action to remove WPML Admin bar menu
    */
    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'addScriptsAndStyles'));
        add_action('wp_before_admin_bar_render', 'quoteupWpmlRemoveAdminBarMenu');
    }

    /**
    * This function is to register scripts if not already registered.
     *
    * @param [array] $scritHandles [Scripts to be registered]
    */
    protected function registerScripts($scriptHandles = array())
    {
        if (!empty($scriptHandles)) {
            foreach ($scriptHandles as $scriptHandle => $scriptUrl) {
                if (wp_script_is($scriptHandle, 'registered')) {
                    continue;
                }
                wp_register_script($scriptHandle, $scriptUrl, array(), QUOTEUP_VERSION);
            }
        }
    }
    /**
    * This function is to enqueue scripts
     *
    * @param [array] $scritHandles [Scripts to be enqueued]
    */
    protected function enqueueScripts($scriptHandles = array())
    {
        if (!empty($scriptHandles)) {
            foreach ($scriptHandles as $scriptHandle) {
                if (shouldScriptBeEnqueued($scriptHandle)) {
                    wp_enqueue_script($scriptHandle);
                }
            }
        }
    }
    /**
    * This function is to register styles if not already registered.
     *
    * @param [array] $scritHandles [styles to be registered]
    */
    protected function registerStyles($styleHandles = array())
    {
        if (!empty($styleHandles)) {
            foreach ($styleHandles as $styleHandle => $styleUrl) {
                if (wp_style_is($styleHandle, 'registered')) {
                    continue;
                }
                wp_register_style($styleHandle, $styleUrl, array(), QUOTEUP_VERSION);
            }
        }
    }
    /**
    * This function is to enqueue styles
     *
    * @param [array] $scriptHandles [styles to be enqueued]
    */
    protected function enqueueStyles($styleHandles = array())
    {
        if (!empty($styleHandles)) {
            foreach ($styleHandles as $styleHandle) {
                if (shouldStyleBeEnqueued($styleHandle)) {
                    wp_enqueue_style($styleHandle);
                }
            }
        }
    }

    /**
    * This function is for the localize scripts needed for some scripts.
    * For example for ajax calls ,the dependancies are sent, the objects are specified here through which dependancies can be accesed in the js.
     *
    * @param [array] $scriptHandles [array of the scripts]
    */
    protected function localizeScripts($scriptHandles = array())
    {
        if (!empty($scriptHandles)) {
            foreach ($scriptHandles as $scriptHandle) {
                switch ($scriptHandle) {
                    case 'quoteup-edit-quote':
                        $aryArgs = getDateLocalizationArray();
                        $aryArgs['unreadEnquiryFlag'] = getEnquiryMeta($_GET['id'], '_unread_enquiry');
                        wp_localize_script($scriptHandle, 'dateData', $aryArgs);
                        break;
                
                    case 'products-selection-js':
                        wp_localize_script(
                            $scriptHandle,
                            'productsSelectionData',
                            array(
                                'ajax_url' => admin_url('admin-ajax.php'),
                            )
                        );
                        $data = array(
                            'quoteup_no_products'   => sprintf(__('%1$s No Products Selected %2$s', QUOTEUP_TEXT_DOMAIN), "<tr class='quoteup-no-product'><td colspan='6' style='text-align: center;'>", '</td></tr>'),
                            'update_quotation_text' => __('Update Quotation', QUOTEUP_TEXT_DOMAIN),
                            );
                
                        wp_localize_script($scriptHandle, 'wdm_data', $data);
                        break;

                    case 'wc-enhanced-select-extended':
                        $enquiryLanguage = '';

                        if (quoteupIsWpmlActive()) {
                            $enquiryLanguage = getEnquiryMeta($_GET['id'], 'enquiry_lang_code');
                        }
                    
                        wp_localize_script(
                            $scriptHandle,
                            'wc_enhanced_select_params',
                            array(
                            'i18n_matches_1' => _x('One result is available, press enter to select it.', 'enhanced select', 'quoteup'),
                            'i18n_matches_n' => _x('%qty% results are available, use up and down arrow keys to navigate.', 'enhanced select', 'quoteup'),
                            'i18n_no_matches' => _x('No matches found', 'enhanced select', 'quoteup'),
                            'i18n_ajax_error' => _x('Loading failed', 'enhanced select', 'quoteup'),
                            'i18n_input_too_short_1' => _x('Please enter 1 or more characters', 'enhanced select', 'quoteup'),
                            'i18n_input_too_short_n' => _x('Please enter %qty% or more characters', 'enhanced select', 'quoteup'),
                            'i18n_input_too_long_1' => _x('Please delete 1 character', 'enhanced select', 'quoteup'),
                            'i18n_input_too_long_n' => _x('Please delete %qty% characters', 'enhanced select', 'quoteup'),
                            'i18n_selection_too_long_1' => _x('You can only select 1 item', 'enhanced select', 'quoteup'),
                            'i18n_selection_too_long_n' => _x('You can only select %qty% items', 'enhanced select', 'quoteup'),
                            'i18n_load_more' => _x('Loading more results', 'enhanced select', 'quoteup'),
                            'i18n_searching' => _x('Searching', 'enhanced select', 'quoteup'),
                            'ajax_url' => admin_url('admin-ajax.php'),
                            'search_products_nonce' => wp_create_nonce('search-products'),
                            'enquiryLanguage' => $enquiryLanguage,
                            )
                        );
                        break;
                    default:
                        break;
                }
            }
        }
    }

    /**
    * This function id to add scripts and styles to the specific hook
     *
    * @param string $hook hook for enquueuing scripts
    */
    public function addScriptsAndStyles($hook)
    {
        if ('admin_page_quoteup-details-edit' != $hook) {
            return;
        }

        if (!is_callable('WC')) {
            return;
        }

        $stylesToBeRegistered = array(
            'wdm-data-css'             =>  QUOTEUP_PLUGIN_URL.'/css/admin/edit-quote.css',
            'wdm-mini-cart-css2'       =>  QUOTEUP_PLUGIN_URL.'/css/common.css',
            'quoteup-select2-css'      =>  QUOTEUP_PLUGIN_URL.'/css/admin/quoteup-select2.css',
            'woocommerce-admin-css'    =>  QUOTEUP_PLUGIN_URL.'/css/admin/woocommerce-admin.css',
            'woocommerce_admin_styles' =>  WC()->plugin_url().'/assets/css/admin.css',
            'products-selection-css'   => QUOTEUP_PLUGIN_URL . '/css/admin/products-selection.css',
            'quoteup-price-change-css' => QUOTEUP_PLUGIN_URL.'/css/admin/price-change.css',
        );

        $this->registerStyles($stylesToBeRegistered);

        $this->enqueueStyles(array_keys($stylesToBeRegistered));

        if (version_compare(WC_VERSION, '2.6', '>')) {
            $cssString = 'th.item-head-img, td.item-content-img {display: none;}';
            wp_add_inline_style('wdm-mini-cart-css2', $cssString);
        }

        $scriptsToBeRegistered = array(
            'quoteup-edit-quote'          =>  QUOTEUP_PLUGIN_URL.'/js/admin/edit-quote.js',
            'quoteup-encode'              =>  QUOTEUP_PLUGIN_URL.'/js/admin/encode-md5.js',
            'quoteup-functions'           =>  QUOTEUP_PLUGIN_URL.'/js/admin/functions.js',
            'quoteup-select2'             =>  QUOTEUP_PLUGIN_URL.'/js/admin/quoteup-select2.js',
            'products-selection-js'       =>  QUOTEUP_PLUGIN_URL.'/js/admin/products-selection.js',
            'wc-enhanced-select-extended' => QUOTEUP_PLUGIN_URL . '/js/admin/enhanced-select-extended.js',
            'quoteup-price-change-manipulation-js' => QUOTEUP_PLUGIN_URL . '/js/admin/price-change-manipulation.js',
        );
        $this->registerScripts($scriptsToBeRegistered);

        //Enqueue Essential Scripts
        $this->enqueueScripts(
            array_merge(
                array(
                    'jquery',
                    'jquery-ui-core',
                    'jquery-ui-datepicker',
                    'select2',
                    'postbox',
                ),
                array_keys($scriptsToBeRegistered)
            )
        );


        $this->localizeScripts(array_keys($scriptsToBeRegistered));

        quoteupGetAdminTemplatePart('quote-edit', '', array());
    }

    /*
     * This function is used to display data on enquiry or quote edit page
     * Get the Enquiry details for selected enquiry.
     * Display meta boxes for customer details and the their enquiry details for products.
     */
    public function editQuoteDetails()
    {
        global $quoteup_admin_menu;
        $quoteupSettings = quoteupSettings();
        $enquiry_id = filter_var($_GET[ 'id' ], FILTER_SANITIZE_NUMBER_INT);
        $this->resetNewEnquiryStatus($enquiry_id);
        $this->enquiry_details = getEnquiryData($enquiry_id);

        if ($this->enquiry_details == null) {
            echo '<br /><br /><p><strong>'.__('No Enquiry Found.', QUOTEUP_TEXT_DOMAIN).'</strong></p>';

            return;
        }

        /**
         * Use the hook to validate the user when user tries to edit an enquiry.
         *
         * @param  int  $enquiry_id  Current enquiry id.
         */
        do_action('quoteup_validate_enquiry_edit_action', $enquiry_id);

        ?>
        <div class="wrap">
            <h1>
                <?php
                /**
                 * Heading for enquiry/ quote edit page.
                 *
                 * @hooked displayEnquiryHeading (class QuoteupEnquiriesEdit) - 10
                 * @hooked displayQuoteHeading (class QuoteupQuotesEdit) - 10
                 *
                 * @param  array  $enquiry_details  Array containing enquiry data.
                 */
                do_action('quoteup-edit-heading', $this->enquiry_details);
                ?>
            </h1>
            <form name="editQuoteDetailForm" method="post">
                <input type="hidden" name="action" value="editQuoteDetail" />
        <?php
        wp_nonce_field('editQuoteDetail-nonce');
        /* Used to save closed meta boxes and their order */
        wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false);
        wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false);
        ?>
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder">

                        <div id="post-body-content">
                            <p>Admin Page for Editing Product Enquiry Detail.</p>
                        </div>
                        <div id="postbox-container-2" class="postbox-container">
        <?php
        add_meta_box('editCustomerData', __('Customer Data', QUOTEUP_TEXT_DOMAIN), array($this,'customerDataSection'), $quoteup_admin_menu, 'normal');
        /**
         * After 'Customer Data' section on the enquiry/ quote edit page.
         *
         * @hooked displayEnquiryProductDetails (class QuoteupEnquiriesEdit)- 10
         * @hooked displayQuoteProductDetails (class QuoteupQuotesEdit)- 10
         *
         * @param  array  $enquiry_details  Array containing enquiry data.
         */
        do_action('quoteup_after_customer', $this->enquiry_details);

        $peDetailHeading = __('Enquiry Messages', QUOTEUP_TEXT_DOMAIN);
        if (quoteupIsMPEEnabled($quoteupSettings) && !quoteupIsRemarksColumnDisabled($quoteupSettings)) {
            $peDetailHeading = __('Enquiry Remarks and Messages', QUOTEUP_TEXT_DOMAIN);
        }

        add_meta_box('editPEDetailMsg', $peDetailHeading, array($this,'editPEDetailMsgFn'), $quoteup_admin_menu, 'normal');
        /**
         * After 'Enquiry Messages'/ 'Enquiry Remarks and Messages' section
         * on enquiry/ quote edit page.
         *
         * @hooked addMetaBoxAfterMessage (class QuoteupQuotesEdit) - 10
         *
         * @param  array  $enquiry_details  Array containing enquiry data.
         */
        do_action('PEDetailEdit', $this->enquiry_details);
        do_meta_boxes($quoteup_admin_menu, 'normal', '');
        ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>
                <?php
    }

    /**
    * This function reset the New enquiry status.
    * Update unread_enquiry key from yes to no.
     *
    * @param int $enquiry_id Enquiry Id
    */
    public function resetNewEnquiryStatus($enquiry_id)
    {
        global $wpdb;
        $metaTbl = getEnquiryMetaTable();
        $sql = "SELECT meta_value FROM $metaTbl WHERE meta_key = '_unread_enquiry' AND enquiry_id= $enquiry_id";
        $metaValue = $wpdb->get_var($sql);
        if ($metaValue == 'yes') {
            $wpdb->update(
                $metaTbl,
                array(
                'meta_value' => 'no',
                ),
                array(
                'enquiry_id' => $enquiry_id,
                'meta_key' => '_unread_enquiry',
                ),
                array(
                '%s',
                ),
                array('%d', '%s')
            );
        }
    }

    /**
     * This function renders the customer data section on enquiry or quote edit page.
     * And displays the customer data on edit quote page.
     */
    public function customerDataSection()
    {
        $form_data = quoteupSettings();
        $enquiryId = $_GET['id'];
        $customerDataToShow = array('name', 'email', 'enquiry_ip', 'message', 'subject', 'phone_number', 'date_field', 'enquiry_date');
        $customerDataToShow = apply_filters('quoteup_customer_default_data_to_show', $customerDataToShow, $enquiryId)
        ?>

        <div class='cust_section'>
        <input type='hidden' class='wdm-enq-id' value="<?php echo $enquiryId; ?>">
        <input type='hidden' class='admin-url' value='<?php echo admin_url('admin-ajax.php');
        ?>'>
            <article class='wdm-tbl-gen clearfix'>
                <section class='wdm-tbl-gen-sec clearfix wdm-tbl-gen-sec-1'>
                    <div class='wdm-tbl-gen-detail'>
                    <?php
                    if (in_array('name', $customerDataToShow)) :
                        ?>
                        <div class='wdm-user' title='<?php echo $this->enquiry_details['name']; ?>'>
                            <textarea id="input-name" type='text' class='wdm-input input-field input-name' disabled name='cust_name' required><?php echo esc_textarea($this->enquiry_details['name']); ?></textarea>
                            <label placeholder="<?php _e('Client\'s Full Name', QUOTEUP_TEXT_DOMAIN); ?>" alt="<?php _e('Full Name', QUOTEUP_TEXT_DOMAIN); ?>"></label>
                        </div>
                        <?php
                    endif;

                    if (in_array('email', $customerDataToShow)) :
                        ?>
                        <div class='wdm-user-email' title='<?php echo $this->enquiry_details['email']; ?>'>
                            <textarea id="input-email" type='email' class='wdm-input input-field input-email' disabled name='cust_email' required><?php echo esc_textarea($this->enquiry_details['email']); ?></textarea>
                            <label placeholder="<?php _e('Client\'s Email Address', QUOTEUP_TEXT_DOMAIN); ?>" alt="<?php _e('Email', QUOTEUP_TEXT_DOMAIN); ?>"></label>
                        </div>
                        <?php
                    endif;

                    if (in_array('enquiry_ip', $customerDataToShow)) :
                        ?>
                        <div class='wdm-user-ip' title='<?php echo $this->enquiry_details['enquiry_ip']; ?>'>
                            <textarea type='text' class='wdm-input-ip wdm-input' disabled name='cust_ip' required><?php echo esc_textarea($this->enquiry_details['enquiry_ip']); ?></textarea>
                            <label placeholder="<?php _e('Client\'s IP Address', QUOTEUP_TEXT_DOMAIN); ?>" alt="<?php _e('IP Address', QUOTEUP_TEXT_DOMAIN); ?>"></label>
                        </div>
                        <?php
                    endif;

                    if (in_array('enquiry_date', $customerDataToShow)) :
                        ?>
                        <div class='wdm-user-enquiry-date' title='<?php echo date('M d, Y', strtotime($this->enquiry_details['enquiry_date'])); ?>'>
                            <textarea type='text' class='wdm-input-enquiry-date wdm-input' disabled name='enquiry_date'><?php echo esc_textarea(date('M d, Y', strtotime($this->enquiry_details['enquiry_date']))); ?></textarea>
                            <label placeholder="<?php _e('Enquiry Date', QUOTEUP_TEXT_DOMAIN); ?>" alt="<?php _e('Enquiry Date', QUOTEUP_TEXT_DOMAIN); ?>"></label>
                        </div>
                        <?php
                    endif;
                    ?>
        <?php
        if (in_array('phone_number', $customerDataToShow)) {
            $this->getPhoneNumberField($form_data);
        }

        if (in_array('date_field', $customerDataToShow)) {
            $this->getDateField($form_data);
        }

        do_action_deprecated('mep_custom_fields', array($enquiryId), '6.5.0', 'quoteup_custom_fields_customer_data_section');
        /**
         * Display the custom fields in the 'Customer Data' section on the
         * enquiry/ quote edit page.
         *
         * @hooked displayCustomFieldsDataEnquiryEdit (class QuoteUpAddCustomField) - 10
         *
         * @since 6.5.0
         *
         * @param  int  $enquiryId  Enquriy Id.
         */
        do_action('quoteup_custom_fields_customer_data_section', $enquiryId);
        ?>
                    </div>
                </section>
            </article>
            </div>
            <?php
    }
    /**
    * Gets the phone number from the form.
     *
     * @param [array] $form_data [settings stored in database]
    */
    public function getPhoneNumberField($form_data)
    {
        $enable_ph = 0;
        if (isset($form_data[ 'enable_telephone_no_txtbox' ]) && isset($form_data[ 'enquiry_form' ]) && $form_data[ 'enquiry_form' ] == 'default') {
            $enable_ph = $form_data[ 'enable_telephone_no_txtbox' ];
        } else {
            $enable_ph = 0;
        }

        if (1 == $enable_ph) {
            /**
             * Before displaying the telephone number in the 'Customer Data' section on the enquiry/ quote edit page.
             *
             */
            do_action('quoteup_before_customer_telephone_column');
            do_action_deprecated('pep_before_customer_telephone_column', array(), '6.5.0');
            $phNumber = $this->enquiry_details['phone_number'];
            if (empty($phNumber)) {
                $phNumber = '-';
            }
            ?>
            <div class='wdm-user-telephone' title='<?php echo esc_attr($phNumber); ?>'>
                <textarea type='text' class='wdm-input-telephone wdm-input' disabled name='cust_telephone' required><?php echo esc_textarea($phNumber); ?></textarea>
                <label placeholder="<?php _e('Telephone', QUOTEUP_TEXT_DOMAIN); ?>" alt="<?php _e('Telephone', QUOTEUP_TEXT_DOMAIN); ?>"></label>
            </div>
            <?php
        }
        /**
         * After displaying the telephone number in the 'Customer Data' section on the enquiry/ quote edit page.
         *
         */
        do_action('quoteup_after_customer_telephone_column');
        do_action_deprecated('pep_after_customer_telephone_column', array(), '6.5.0');
    }

    /**
    * Gets the date from the form.
     *
    * @param [array] $form_data [settings stored in database]
    */
    public function getDateField($form_data)
    {
        $enable_dt = 0;
        if (isset($form_data[ 'enable_date_field' ]) && isset($form_data[ 'enquiry_form' ]) && $form_data[ 'enquiry_form' ] == 'default') {
            $enable_dt = $form_data[ 'enable_date_field' ];
        } else {
            $enable_dt = 0;
        }

        if ($enable_dt == 1) {
            /**
             * Before displaying the date field in the 'Customer Data' section on the enquiry/ quote edit page.
             */
            do_action('quoteup_before_customer_date_field');
            do_action_deprecated('pep_before_customer_date_field', array(), '6.5.0');
            $dateField = '';
            $dateLabel = 'Date';

            if (isset($form_data[ 'date_field_label' ])) {
                $dateLabel = $form_data[ 'date_field_label' ];
            }

            if (!empty($this->enquiry_details['date_field']) && $this->enquiry_details['date_field'] != '0000-00-00 00:00:00' && $this->enquiry_details['date_field'] != '1970-01-01 00:00:00') {
                $dateField = date('M d, Y', strtotime($this->enquiry_details['date_field']));
            }

            if (empty($dateField)) {
                $dateField = '-';
            }
            ?>
            <div class='wdm-user-date-field' title='<?php echo esc_attr($dateField); ?>'>
                <textarea type='text' class='wdm-input-user-date wdm-input' disabled name='cust_date_field' required><?php echo esc_textarea($dateField); ?></textarea>
                <label placeholder="<?php _e($dateLabel, QUOTEUP_TEXT_DOMAIN); ?>" alt="<?php _e($dateLabel, QUOTEUP_TEXT_DOMAIN); ?>"></label>
            </div>
            <?php
            /**
             * After displaying the date field in the 'Customer Data' section on the enquiry/ quote edit page.
             */
            do_action('quoteup_after_customer_date_field');
            do_action_deprecated('pep_after_customer_date_field', array(), '6.5.0');
        }
    }

    /**
    * This function is to get the Product details in the enquiry for edit quote page.
    * Display the products based on whether they are simple or variable accordingly.
    */
    public function editPEDetailMsgFn()
    {
        global $pep_admin_menu;

        $enquiryId       = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
        $quoteupSettings = quoteupSettings();
        $enquiryProducts = getEnquiryProducts($enquiryId);
        ?>
        <div id="postbox-container-1" class=""> 
            <table class="remarks-table" cellspacing="0">
                <thead>
                    <tr>
                        <th class="product-name-head"> <?php _e('Product Name', QUOTEUP_TEXT_DOMAIN); ?></th>
                        <?php
                        if (quoteupIsMPEEnabled($quoteupSettings) && !quoteupIsRemarksColumnDisabled($quoteupSettings)) :
                            ?>
                            <th class="remarks-head"><?php echo quoteupGetRemarksThNameEnqCart($quoteupSettings); ?></th>
                            <?php
                        endif;
                        ?>
                    </tr>
                </thead>
                <tbody>
                <?php
                foreach ($enquiryProducts as $singleProduct) {
                    $productId = $singleProduct['product_id'];
                    $url = admin_url("/post.php?post={$productId}&action=edit");
                    $productTitle = '<a href='.$url." target='_blank'>".get_the_title($productId).'</a>';
                    if (!$productTitle) {
                        $productTitle = $singleProduct['product_title'];
                    }
                    $remarks = $singleProduct['remark'];

                    ?>
                    <tr>
                        <td class='product-name-content' data-title="Product Name">
                          <p>
                            <?php
                            echo $productTitle;
                            if (isset($singleProduct[ 'variation_id' ]) && $singleProduct[ 'variation_id' ] != '' && $singleProduct[ 'variation_id' ] != 0) {
                                    $variationString = printVariations($singleProduct);
                                    $variationString = preg_replace(']<br>]', '<br>&#8627 ', $variationString); // Used to add arrow symbol
                                    $variationString = preg_replace(']<br>]', '', $variationString, 1); // Used to remove first br tag
                                    echo "<div style='margin-left:10px'>";
                                    echo $variationString;
                                    echo '</div>';
                            }
                            ?>                            
                          </p>                           
                        </td>
                        <?php
                        if (quoteupIsMPEEnabled($quoteupSettings) && !quoteupIsRemarksColumnDisabled($quoteupSettings)) :
                            ?>
                                <td class='remarks-content' data-title="Remarks">
                                    <p><?php echo $remarks; ?></p>                           
                                </td>
                            <?php
                        endif;
                        ?>                      
                    </tr>
                    <?php
                }
                ?>
                    
                </tbody>
            </table>
        <?php
        if (apply_filters('quoteup_enb_reply_in_enq_rem_msg_sec', true, $enquiryId)) {
            $this->editPEDetailEnquiryNotesFn();
        }
        do_meta_boxes($pep_admin_menu, 'side', '');
        ?>
        </div>
                        <?php
    }

    /**
    * Get the details of enquiry from enquiry_details_new table.
    * This is for show original enquiry button.
    */
    public function editPEDetailEnquiryNotesFn()
    {
        global $enquiry_details, $wpdb;
        $enquiryID = filter_var($_GET[ 'id' ], FILTER_SANITIZE_NUMBER_INT);
        $enquiry_tbl = getEnquiryDetailsTable();
        $sql = $wpdb->prepare("SELECT * FROM $enquiry_tbl WHERE enquiry_id = '%d'", $enquiryID);
        $enquiry_details = $wpdb->get_row($sql);
        $enq_tbl = getEnquiryThreadTable();
        $url = admin_url('admin-ajax.php');
        $sql = $wpdb->prepare("SELECT * FROM $enq_tbl WHERE enquiry_id=%d", $enquiryID);
        $reply = $wpdb->get_results($sql);
        echo "<input type='hidden' class='wdm-enquiry-usr' value='{$enquiry_details->email}'/>";
        echo "<input type='hidden' class='admin-url' value='{$url}'/>";
        echo "<div class='msg-wrapper'><div class='wdm-input-ip wdm-enquirymsg'><em>$enquiry_details->subject</em></div>";
        echo "<div class='wdm-input-ip enquiry-message'>$enquiry_details->message</div>";
        echo " <hr class='msg-border'/>";
        $thr_id = $enquiryID;
        foreach ($reply as $msg) {
            $thr_id = $msg->id;
            $sub = $msg->subject;
            $message = $msg->message;
            echo "<div class='msg-wrapper'><div class='wdm-input-ip hide wdm-enquirymsg'><em>{$sub}</em></div>";
            echo "<div wdm-input-ip>{$message}</div>";
            echo " <hr class='msg-border'/>";
            echo '</div>';
        }

        // Check if enquiry anonymized.
        if (!$this->isEnquiryAnonymized($enquiry_details->email)) {
            echo "<a href='#' class='rply-link'><button class = 'button'>".__('Reply', QUOTEUP_TEXT_DOMAIN).' &crarr; </button></a>';
            $this->replyThreadSection($thr_id);
        }
        echo '</div>';
    }

    /**
    * This function is for displaying the reply section in the edit quote page.
     *
    * @param int $thr_id enquiry Id
    */
    public function replyThreadSection($thr_id)
    {
        global $enquiry_details;
        $sub = $enquiry_details->subject;
        if ($sub == '') {
            $sub = 'Reply for Enquiry';
        }
        ?>
        <div class='reply-div' data-thred-id = '<?php echo esc_attr($thr_id); ?>'>
            <input type='hidden' class='parent-id' value='<?php echo esc_attr($thr_id); ?>'>

            <div class="reply-field-wrap hide" >

                <input type='text' placeholder='Subject' value="<?php echo esc_attr($sub);
                ?>" name='wdm_reply_subject' class='wdm_reply_subject_<?php echo esc_attr($thr_id); ?> wdm-field reply-field'/>
            </div>

            <div class="reply-field-wrap">
                <?php $classAttr =  'wdm_reply_msg_' . $thr_id; ?>
                <textarea class='wdm-field <?php echo esc_attr($classAttr); ?> reply-field' name='wdm_reply_msg' placeholder="<?php _e('Message', QUOTEUP_TEXT_DOMAIN); ?>"></textarea>
            </div>
            <?php
            /**
             * Before 'Send' button when replying to customer.
             */
            do_action('quoteup_before_reply_customer_enquiry_btn');
            ?>
            <div class="reply-field-wrap reply-field-submitwrap">
                <input type='submit' value='
                <?php
                echo __('Send', QUOTEUP_TEXT_DOMAIN);
                ?>
                ' name='btn_submit' class='button button-rply-user button-primary' data_thread_id='<?php echo esc_attr($thr_id); ?>'/>
                <span class='load-ajax'></span>
            </div>
        </div>

        <div class='msg-sent'>

            <div>
                <span class="wdm-pepicon wdm-pepicon-done"></span> 
                <?php
                echo __('Reply sent successfully', QUOTEUP_TEXT_DOMAIN);
                ?>
            </div>
        </div>
        <!--       <hr class="msg-border"/>
              </div> -->
        <?php
    }

    /**
     * This function is used to get image url.
     *
     * @param array $prod Product details
     * @return string $img_url image url for product
     */
    public function getImageURL($prod)
    {
        $img_url = '';
        if (isset($prod[ 'variation_id' ]) && $prod[ 'variation_id' ] != '') {
            $img_url = wp_get_attachment_url(get_post_thumbnail_id($prod[ 'variation_id' ]));
        }
        if (!$img_url || $img_url == '') {
            $img_url = wp_get_attachment_url(get_post_thumbnail_id($prod[ 'product_id' ]));
        }
        if (!$img_url || $img_url == '') {
            $img_url = WC()->plugin_url().'/assets/images/placeholder.png';
        }

        return $img_url;
    }

    /**
     * This function is used to send sku value.
     * If sku is blank then '-' is sent.
     *
     * @param [string] $sku [sku value]
     *
     * @return [string] [updated sku value]
     */
    public function getSkuValue($sku)
    {
        return empty($sku) ? '-' : $sku;
    }

    /**
    * If there is any attachment associated with the current enquiry it fetches it  from the uploads->QuoteUp_Files directory
    * Displays the list of attachments.
    */
    public function displayAttachments()
    {
        $upload_dir = wp_upload_dir();
        $attachmentDirectory = $upload_dir[ 'basedir' ].'/QuoteUp_Files/'.$_GET['id'].'/';
        $attachmentDirURL = $upload_dir[ 'baseurl' ].'/QuoteUp_Files/'.$_GET['id'].'/';
        if (file_exists($attachmentDirectory) && count(glob("$attachmentDirectory/*")) !== 0) {
            ?>
        <div class="display-attachment-main">
            <?php
            if ($handle = opendir($attachmentDirectory)) {
                $thelist = '';
                while (false !== ($file = readdir($handle))) {
                    if ('.' != $file && '..' != $file) {
                        $thelist .= '<div class="attachment-div"><img class="wdm-attachment-img" src="'.QUOTEUP_PLUGIN_URL.'/images/attachment.png"/> <a href="'.esc_url($attachmentDirURL.$file).'" download="'.esc_attr($file).'">'.esc_html($file).'</a></div>';
                    }
                }
                closedir($handle);
            }
            echo '<h3>' . __('Attachments', QUOTEUP_TEXT_DOMAIN) . ':</h3>';
            echo $thelist;
            ?>
        </div>
            <?php
        }
    }

    /**
     * Check if particular enquiry is anonymized i.e. if customer data
     * assocoated with an enquiry has been removed.
     *
     * @since 6.3.4
     * @param string $customerEmail Customer email address.
     * @param int $enquiryId  Enquiry Id. Optional. Default 0,
     *
     * @return bool True if enquiry is anonymized, false otherwise.
     */
    public function isEnquiryAnonymized($customerEmail, $enquiryId = 0)
    {
        if (empty($customerEmail)) {
            if ($enquiryId > 0) {
                $enquiryDetails  = getEnquiryData($enquiryId);
                $customerEmail   = $enquiryDetails['email'];
            }
        }
        $anonymizedEmail = wp_privacy_anonymize_data('email');

        if ($customerEmail == $anonymizedEmail) {
            return true;
        }

        return false;
    }
}

<?php

namespace Admin\Includes;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

add_action('load-edit.php', 'Admin\Includes\cloneWPPostsListTable');

/*
 * This function is used to clone wp post list table
 */
function cloneWPPostsListTable()
{
    // Target the products edit screen
    if ('edit-product' !== get_current_screen()->id) {
        return;
    }

    // Include the WP_Posts_List_Table class
    require_once ABSPATH.'wp-admin/includes/class-wp-posts-list-table.php';

    // Extend the WP_Posts_List_Table class and add extra actions
    /**
    * This class is to Display the Product lists table.
    * It is for handling the bulk actions on the enquiries.
    */
    class QuoteupProductsTable extends \WP_Posts_List_Table
    {
        public $quoteupBulkActions = array();
        public $visibilityIconsTooltip = array();
        public $settings = array();
        /**
        * Set the bulk actions array and visibility icon messages.
        * Action for admin notices.
        * Registering and displayng the column
        * Action to enqueue scripts and styles,
        */
        public function __construct($args = array())
        {
            $this->set_bulk_actions_array();
            $this->set_visibility_icon_messages();
            $this->retrieveSettings();
            add_action('admin_notices', array($this, 'bulk_action_admin_notices'));
            add_filter('manage_product_posts_columns', array($this, 'register_visibility_column'));
            add_action('manage_product_posts_custom_column', array($this, 'render_visibility_column_data'), 10, 2);
            add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts_and_styles'));
            //Call Parent class's constructor
            parent::__construct($args);
        }

        /*
         * This function is used to get quoteup settings
         */
        protected function retrieveSettings()
        {
            $this->settings = get_option('wdm_form_data');
        }

        /*
         * This function is used to add quoteup bluk actions every product in woocommerce.
         */
        protected function set_bulk_actions_array()
        {
            $quoteupBulkActions = array(
                'enable-enquiry'  => __('Enable Enquiry', QUOTEUP_TEXT_DOMAIN),
                'disable-enquiry' => __('Disable Enquiry', QUOTEUP_TEXT_DOMAIN),
            );

            if (!quoteupIsHidePriceSettingEnabled()) {
                if (!quoteupIsHideAddToCartSettingEnabled()) {
                    $quoteupBulkActions = $quoteupBulkActions + array(
                        'enable-add-to-cart'  => __('Enable Add to Cart', QUOTEUP_TEXT_DOMAIN),
                        'disable-add-to-cart' => __('Disable Add to Cart', QUOTEUP_TEXT_DOMAIN),
                    );
                }

                $quoteupBulkActions = $quoteupBulkActions + array(
                    'show-price' => __('Show Price', QUOTEUP_TEXT_DOMAIN),
                    'hide-price' => __('Hide Price', QUOTEUP_TEXT_DOMAIN),
                );
            }

            /**
             * Filter to modify the bulk actions on product list page.
             *
             * @since 6.5.0
             *
             * @param  array  $quoteupBulkActions  Bulk actions.
             */
            $quoteupBulkActions = apply_filters('quoteup_bulk_actions', $quoteupBulkActions);
            $this->quoteupBulkActions = $quoteupBulkActions;
        }

        /*
         * This function is used to add visiblity icon messages
         */
        protected function set_visibility_icon_messages()
        {
            $this->visibilityIconsTooltip = array(
                'add-to-cart-enabled' => __('Add to Cart Enabled', QUOTEUP_TEXT_DOMAIN),
                'add-to-cart-disabled' => __('Add to Cart Disabled', QUOTEUP_TEXT_DOMAIN),
                'price-visible' => __('Price Visible', QUOTEUP_TEXT_DOMAIN),
                'price-hidden' => __('Price Hidden', QUOTEUP_TEXT_DOMAIN),
                'enquiry-enabled' => __('Enquiry Enabled', QUOTEUP_TEXT_DOMAIN),
                'enquiry-enabled-if-out-of-stock' => __('Enquiry is enabled but Enquiry button will be shown when product goes out of stock', QUOTEUP_TEXT_DOMAIN),
                'enquiry-disabled' => __('Enquiry Disabled', QUOTEUP_TEXT_DOMAIN),
            );
        }

        /*
         * This function is used to enqueue styles and script for product listing, product table
         */
        public function enqueue_scripts_and_styles()
        {
            wp_enqueue_style('quoteup_products_listing', QUOTEUP_PLUGIN_URL.'/css/admin/dashboard-products-listing.css', false);
            wp_enqueue_script('quoteup_products_listing_js', QUOTEUP_PLUGIN_URL.'/js/admin/woocommerce-products-table.js', array(
                'jquery', ));

            wp_localize_script('quoteup_products_listing_js', 'quoteup_products_listing_localization', array(
                'confirm_message' => __('Hiding Price for products will also disable \'Add To Cart\' for those products. Do you want to continue?', QUOTEUP_TEXT_DOMAIN), ));
        }

        /**
         * Add new options to Bulk Options Dropdown.
         * @return array $actions actions to apply
         */
        protected function get_bulk_actions()
        {
            $actions = array();
            $post_type_obj = get_post_type_object($this->screen->post_type);
            $is_trash = isset($_REQUEST['post_status']) && $_REQUEST['post_status'] === 'trash';

            if (current_user_can($post_type_obj->cap->edit_posts)) {
                if ($is_trash) {
                    $actions[ 'untrash' ] = __('Restore');
                } else {
                    $actions[ 'edit' ] = __('Edit');
                    $actions = $actions + $this->quoteupBulkActions;
                }
            }

            if (current_user_can($post_type_obj->cap->delete_posts)) {
                if ($is_trash || !EMPTY_TRASH_DAYS) {
                    $actions[ 'delete' ] = __('Delete Permanently');
                } else {
                    $actions[ 'trash' ] = __('Move to Trash');
                }
            }

            return $actions;
        }

        /**
         * This function is used to perform enable enquiry action.
         * @param array $post_ids product post ids
        * @param object $post_type_obj Post object
        * @param string $sendback url to redirect to
         * @return [array] [action to enable]
         */
        public function actionEnableEnquiry($post_ids, $post_type_obj, $sendback)
        {
            $enabledEnquiries = $locked = $enabledEnquiriesForFuture = $invalidProducts = 0;

            foreach ((array) $post_ids as $post_id) {
                if (!current_user_can($post_type_obj->cap->edit_posts, $post_id)) {
                    wp_die(sprintf(__('You are not allowed to edit %s. Therefore you can not enable enquiry for this product', QUOTEUP_TEXT_DOMAIN), get_the_title($post_id)));
                }

                if (wp_check_post_lock($post_id)) {
                    ++$locked;
                    continue;
                }
                update_post_meta($post_id, '_enable_pep', 'yes');
                if (!$this->settings || (isset($this->settings[ 'only_if_out_of_stock' ]) && $this->settings[ 'only_if_out_of_stock' ] == 1)) {
                    //Check if product is in stock or not
                    if (\quoteupIsProductInStock($post_id)) {
                        //Enquiry enabled for instock product
                        ++$enabledEnquiriesForFuture;
                    } else {
                        //Enquiry enabled for out of stock product
                        ++$enabledEnquiries;
                    }
                } else {
                    ++$enabledEnquiries;
                }
            }

            // build the redirect url
            return add_query_arg(array('enabled-enquiries' => $enabledEnquiries, 'enabled-enquiries-if-out-of-stock' => $enabledEnquiriesForFuture, 'invalid-products' => $invalidProducts, 'locked' => $locked, 'ids' => implode(',', $post_ids)), $sendback);
        }
        /**
         * This function is used to perform disable enquiry action.
        * @param array $post_ids product post ids
        * @param object $post_type_obj Post object
        * @param string $sendback url to redirect to
         * @return [array] [action to disable]
         */
        public function actionDisableEnquiry($post_ids, $post_type_obj, $sendback)
        {
            $disabledEnquiries = $locked = $invalidProducts = 0;

            foreach ((array) $post_ids as $post_id) {
                if (!current_user_can($post_type_obj->cap->edit_posts, $post_id)) {
                    wp_die(sprintf(__('You are not allowed to edit %s. Therefore you can not disable enquiry for this product', QUOTEUP_TEXT_DOMAIN), get_the_title($post_id)));
                }

                if (wp_check_post_lock($post_id)) {
                    ++$locked;
                    continue;
                }
                update_post_meta($post_id, '_enable_pep', '');
                ++$disabledEnquiries;
            }

                    // build the redirect url
                    return add_query_arg(array('disabled-enquiries' => $disabledEnquiries, 'invalid-products' => $invalidProducts, 'locked' => $locked, 'ids' => implode(',', $post_ids)), $sendback);
        }

        /**
         * This function is used to perform Show price action.
         * @param array $post_ids product post ids
         * @param object $post_type_obj Post object
         * @param string $sendback url to redirect to
         * @return [array] [action to show price] 
         */
        public function actionShowPrice($post_ids, $post_type_obj, $sendback)
        {
            $showingPrice = $locked = $invalidProducts = 0;

            foreach ((array) $post_ids as $post_id) {
                if (!current_user_can($post_type_obj->cap->edit_posts, $post_id)) {
                    wp_die(sprintf(__('You are not allowed to edit %s. Therefore you can not make price visible for this product', QUOTEUP_TEXT_DOMAIN), get_the_title($post_id)));
                }

                if (wp_check_post_lock($post_id)) {
                    ++$locked;
                    continue;
                }
                update_post_meta($post_id, '_enable_price', 'yes');
                ++$showingPrice;
            }

            // build the redirect url
            return add_query_arg(array('showing-price' => $showingPrice, 'invalid-products' => $invalidProducts, 'locked' => $locked, 'ids' => implode(',', $post_ids)), $sendback);
        }

        /**
         * This function is used to perform Hide Price action.
        * @param array $post_ids product post ids
        * @param object $post_type_obj Post object
        * @param string $sendback url to redirect to
                 * @return [array] [action to hide price]
         */
        public function actionHidePrice($post_ids, $post_type_obj, $sendback)
        {
            $hidePrice = $locked = $invalidProducts = 0;
            foreach ((array) $post_ids as $post_id) {
                if (!current_user_can($post_type_obj->cap->edit_posts, $post_id)) {
                    wp_die(sprintf(__('You are not allowed to edit %s. Therefore you can not hide price for this product', QUOTEUP_TEXT_DOMAIN), get_the_title($post_id)));
                }

                if (wp_check_post_lock($post_id)) {
                    ++$locked;
                    continue;
                }
                update_post_meta($post_id, '_enable_price', '');
                //Also hide Add to Cart for product when Price is hidden
                update_post_meta($post_id, '_enable_add_to_cart', '');
                ++$hidePrice;
            }

            // build the redirect url
            return add_query_arg(array('hiding-price' => $hidePrice, 'invalid-products' => $invalidProducts, 'locked' => $locked, 'ids' => implode(',', $post_ids)), $sendback);
        }

        /**
         * This function is used to perform Enable add to cart action.
         * @param array $post_ids product post ids
         * @param object $post_type_obj Post object
        * @param string $sendback url to redirect to
                 * @return [array] [action to enable add to cart]
         */
        public function actionEnableAddToCart($post_ids, $post_type_obj, $sendback)
        {
            $enabledAddToCart = $locked = $skippedEnablingAddToCart = $outOfStockProducts = $invalidProducts = 0;

            foreach ((array) $post_ids as $post_id) {

                if (!current_user_can($post_type_obj->cap->edit_posts, $post_id)) {
                    wp_die(sprintf(__('You are not allowed to edit %s. Therefore you can not enable Add to Cart for this product', QUOTEUP_TEXT_DOMAIN), get_the_title($post_id)));
                }

                if (wp_check_post_lock($post_id)) {
                    ++$locked;
                    continue;
                }
                //Enable add to cart only if price is visible on the Frontend
                if ($this->is_price_showing($post_id)) {
                    update_post_meta($post_id, '_enable_add_to_cart', 'yes');
                    if (\quoteupIsProductInStock($post_id)) {
                        ++$enabledAddToCart;
                    } else {
                        ++$outOfStockProducts;
                    }
                } else {
                    ++$skippedEnablingAddToCart;
                }
            }

            // build the redirect url
            return add_query_arg(array('enabled-add-to-cart' => $enabledAddToCart, 'invalid-products' => $invalidProducts, 'skipped-enabling-add-to-cart' => $skippedEnablingAddToCart, 'skipped-out-of-stock-products' => $outOfStockProducts, 'locked' => $locked, 'ids' => implode(',', $post_ids)), $sendback);
        }

        /**
         * This function is used to perform Disable add to cart action.
         * @param array $post_ids product post ids
         * @param object $post_type_obj Post object
        * @param string $sendback url to redirect to
           * @return [array] [action to disable add to cart]
         */
        public function actionDisableAddToCart($post_ids, $post_type_obj, $sendback)
        {
            $disabledAddToCart = $locked = $invalidProducts = 0;

            foreach ((array) $post_ids as $post_id) {

                if (!current_user_can($post_type_obj->cap->edit_posts, $post_id)) {
                    wp_die(sprintf(__('You are not allowed to edit %s. Therefore you can not disable Add to Cart for this product', QUOTEUP_TEXT_DOMAIN), get_the_title($post_id)));
                }

                if (wp_check_post_lock($post_id)) {
                    ++$locked;
                    continue;
                }
                update_post_meta($post_id, '_enable_add_to_cart', '');
                ++$disabledAddToCart;
            }

            // build the redirect url
            return add_query_arg(array('disabled-add-to-cart' => $disabledAddToCart, 'invalid-products' => $invalidProducts, 'locked' => $locked, 'ids' => implode(',', $post_ids)), $sendback);
        }

        /**
         * Process Bulk actions.
         */
        public function process_bulk_action()
        {
            // 1. get the action
            $action = $this->current_action();

            $all_actions = array_keys($this->quoteupBulkActions);

            //If current action is not in the custom bulk actions, return
            if (!in_array($action, $all_actions)) {
                return;
            }

            //security check
            check_admin_referer('bulk-posts');

            // this is based on wp-admin/edit.php
            $sendback = remove_query_arg(array('enabled-enquiries', 'invalid-products',
                'enabled-enquiries-if-out-of-stock', 'disabled-enquiries', 'showing-price', 'hiding-price',
                'enabled-add-to-cart', 'disabled-add-to-cart', 'skipped-enabling-add-to-cart',
                'untrashed', 'deleted', 'ids', ), wp_get_referer());

            if (!$sendback) {
                $sendback = admin_url('edit.php?post_type=product');
            }

            $pagenum = $this->get_pagenum();
            $sendback = add_query_arg('paged', $pagenum, $sendback);

            //list of posts selected by admin
            if (isset($_REQUEST[ 'post' ])) {
                $post_ids = array_map('intval', $_REQUEST[ 'post' ]);
            }

            if (!isset($post_ids)) {
                wp_redirect($sendback);
                exit;
            }

            $post_type_obj = get_post_type_object($this->screen->post_type);

            switch ($action) {
                case 'enable-enquiry':
                    $sendback = $this->actionEnableEnquiry($post_ids, $post_type_obj, $sendback);
                    break;

                case 'disable-enquiry':
                    $sendback = $this->actionDisableEnquiry($post_ids, $post_type_obj, $sendback);
                    break;

                case 'show-price':
                    $sendback = $this->actionShowPrice($post_ids, $post_type_obj, $sendback);
                    break;

                case 'hide-price':
                    $sendback = $this->actionHidePrice($post_ids, $post_type_obj, $sendback);
                    break;

                case 'enable-add-to-cart':
                    $sendback = $this->actionEnableAddToCart($post_ids, $post_type_obj, $sendback);
                    break;

                case 'disable-add-to-cart':
                    $sendback = $this->actionDisableAddToCart($post_ids, $post_type_obj, $sendback);
                    break;

                default:
                    return;
            }

            $sendback = remove_query_arg(array('action', 'action2', 'tags_input', 'post_author',
                'comment_status', 'ping_status', '_status', 'post', 'bulk_edit', 'post_view', ), $sendback);

            //Redirect client
            wp_redirect($sendback);

            exit();
        }
        /**
        * Gets the no of values which are for corresponding request.
        * @param string variable &$value request
        * @return int count for request
        */
        public function getIsset(&$value)
        {
            return isset($value) ? absint($value) : 0;
        }

        /**
         * This function is used to return bulk counts.
         *
         * @return array the requests which are set.
         */
        public function getBulkCounts()
        {
            return array(
                'enabled-enquiries' => $this->getIsset($_REQUEST[ 'enabled-enquiries' ]),
                'enabled-enquiries-if-out-of-stock' => $this->getIsset($_REQUEST[ 'enabled-enquiries-if-out-of-stock' ]),
                'disabled-enquiries' => $this->getIsset($_REQUEST[ 'disabled-enquiries' ]),
                'showing-price' => $this->getIsset($_REQUEST[ 'showing-price' ]),
                'hiding-price' => $this->getIsset($_REQUEST[ 'hiding-price' ]),
                'enabled-add-to-cart' => $this->getIsset($_REQUEST[ 'enabled-add-to-cart' ]),
                'disabled-add-to-cart' => $this->getIsset($_REQUEST[ 'disabled-add-to-cart' ]),
                'skipped-enabling-add-to-cart' => $this->getIsset($_REQUEST[ 'skipped-enabling-add-to-cart' ]),
                'skipped-out-of-stock-products' => $this->getIsset($_REQUEST[ 'skipped-out-of-stock-products' ]),
                'invalid-products' => $this->getIsset($_REQUEST[ 'invalid-products' ]),
            );
        }

        /**
         * This function is used to return bulk messages.
         * @param string $enquiry_enabled_message Enquiry enabled message to be displayed.
         * @param string $enquiry_enabled_if_out_of_stock Message if out of stock
         * @param int $bulk_counts Enquiry which are set for bulk actions
         * @return array messages to display
         */
        public function getBulkMessages($enquiry_enabled_message, $enquiry_enabled_if_out_of_stock, $bulk_counts)
        {
            return array(
                'enabled-enquiries' => $enquiry_enabled_message,
                'enabled-enquiries-if-out-of-stock' => $enquiry_enabled_if_out_of_stock,
                'disabled-enquiries' => _n('Enquiry disabled for %s product.', 'Enquiries disabled for %s products.', $bulk_counts[ 'disabled-enquiries' ], QUOTEUP_TEXT_DOMAIN),
                'showing-price' => _n('Price visible for %s product.', 'Price visible for %s products.', $bulk_counts[ 'showing-price' ], QUOTEUP_TEXT_DOMAIN),
                'hiding-price' => _n('\'Add to Cart\' & Price hidden for %s products.', '\'Add to Cart\' & Price hidden for %s products.', $bulk_counts[ 'hiding-price' ], QUOTEUP_TEXT_DOMAIN),
                'enabled-add-to-cart' => _n('Add to Cart enabled for %s in stock product.', 'Add to Cart enabled for %s in stock products.', $bulk_counts[ 'enabled-add-to-cart' ], QUOTEUP_TEXT_DOMAIN),
                'disabled-add-to-cart' => _n('Add to Cart disabled for %s product.', 'Add to Cart disabled for %s products.', $bulk_counts[ 'disabled-add-to-cart' ], QUOTEUP_TEXT_DOMAIN),
                'skipped-enabling-add-to-cart' => _n('Enabling \'Add to Cart\' skipped for %s product because price is hidden for that product.', 'Enabling \'Add to Cart\' skipped for %s products because price is hidden for those products.', $bulk_counts[ 'skipped-enabling-add-to-cart' ], QUOTEUP_TEXT_DOMAIN),
                'skipped-out-of-stock-products' => _n('Though Add to Cart is enabled for %s out of product, Add to Cart button is shown only for in-stock products.', 'Though Add to Cart is enabled for %s out of products, Add to Cart button is shown only for in-stock products.', $bulk_counts[ 'skipped-out-of-stock-products' ], QUOTEUP_TEXT_DOMAIN),
                'invalid-products' => _n('%s product skipped because it\'s product type is not supported by Product Enquiry Pro (A.K.A QuoteUp). It works only with Simple and Variable Products.', '%s products skipped because their product types are not supported by Product Enquiry Pro (A.K.A QuoteUp). It works only with Simple and Variable Products.', $bulk_counts[ 'invalid-products' ], QUOTEUP_TEXT_DOMAIN),
            );
        }

        /**
         * This function is used to print all messages.
         *
         * @param [array] $messages [Messages to display]
         */
        public function printMessages($messages)
        {
            if ($messages) {
                echo '<div id="message" class="updated notice is-dismissible"><p>'.implode(' ', $messages).'</p></div>';
            }
        }

        /**
         * This function is used to print all error messages.
         *
         * @param [array] $messages [Error Messages]
         */
        public function printErrorMessages($error_messages)
        {
            if ($error_messages) {
                echo '<div id="message" class="error notice is-dismissible"><p>'.implode(' ', $error_messages).'</p></div>';
            }
        }

        /**
         * Display Admin notices after performing bulk action.
         */
        public function bulk_action_admin_notices()
        {
            $bulk_counts = $this->getBulkCounts();

            $enquiry_enabled_if_out_of_stock = _n('Enquiry enabled for %s in stock product. Enquiry button will be displayed when that product runs out of stock.', 'Enquiries enabled for %s in stock products. Enquiry button will be displayed on those products only when those products run out of stock.', $bulk_counts[ 'enabled-enquiries-if-out-of-stock' ], QUOTEUP_TEXT_DOMAIN);
            if (!$this->settings || (isset($this->settings[ 'only_if_out_of_stock' ]) && $this->settings[ 'only_if_out_of_stock' ] == 1)) {
                $enquiry_enabled_message = _n('Enquiry enabled for %s out of stock product', 'Enquiries enabled for %s out of stock products', $bulk_counts[ 'enabled-enquiries' ], QUOTEUP_TEXT_DOMAIN);
                if ($bulk_counts[ 'enabled-enquiries' ] > 0) {
                    $enquiry_enabled_if_out_of_stock = _n('and %s in stock product. Enquiry button will be displayed only when the product runs out of stock', 'and %s in stock products. Enquiry button will be displayed only when the products run out of stock', $bulk_counts[ 'enabled-enquiries-if-out-of-stock' ], QUOTEUP_TEXT_DOMAIN);
                }
            } else {
                $enquiry_enabled_message = _n('Enquiry enabled for %s product.', 'Enquiries enabled for %s products.', $bulk_counts[ 'enabled-enquiries' ], QUOTEUP_TEXT_DOMAIN);
            }

            $bulk_messages = $this->getBulkMessages($enquiry_enabled_message, $enquiry_enabled_if_out_of_stock, $bulk_counts);

            $messages = array();

            $error_messages = array();

            $bulk_counts = array_filter($bulk_counts);

            $messagesAndErrorMessages = $this->getMessagesAndErrorMessages($bulk_counts, $bulk_messages);
            $messages = isset($messagesAndErrorMessages['messages']) ? $messagesAndErrorMessages['messages'] : array();
            $error_messages = isset($messagesAndErrorMessages['error_messages']) ? $messagesAndErrorMessages['error_messages'] : array();

            $this->printMessages($messages);

            $this->printErrorMessages($error_messages);

            unset($messages);

            unset($error_messages);

            $_SERVER[ 'REQUEST_URI' ] = remove_query_arg(array('enabled-enquiries', 'enabled-enquiries-if-out-of-stock',
                'disabled-enquiries', 'showing-price', 'hiding-price', 'enabled-add-to-cart',
                'disabled-add-to-cart', 'skipped-enabling-add-to-cart', 'skipped-out-of-stock-products',
                'invalid-products', 'locked', 'skipped', 'updated', 'deleted', 'trashed', 'untrashed', ), $_SERVER[ 'REQUEST_URI' ]);
        }

        /**
        * Gets te message or error-message what to be included for the bulk action.
        * @param $bulk_counts array Requests which are set and unset.
        * @param $bulk_messages array skipped-enabling-add-to-cart or else.
        * @return array of messages 
        */
        public function getMessagesAndErrorMessages($bulk_counts, $bulk_messages)
        {
            $messages = array();
            $error_messages = array();
            foreach ($bulk_counts as $message => $count) {
                if (isset($bulk_messages[ $message ]) && $message != 'skipped-enabling-add-to-cart') {
                    $messages[] = sprintf($bulk_messages[ $message ], number_format_i18n($count));
                }

                if ($message == 'skipped-enabling-add-to-cart') {
                    $error_messages[] = sprintf($bulk_messages[ $message ], number_format_i18n($count));
                }
            }

            return array(
                'messages' => $messages,
                'error_messages' => $error_messages,
            );
        }

        /**
         * Checks whether Price is being shown for the product.
         *
         * @param int $product_id
         *
         * @return bool
         */
        protected function is_price_showing($product_id)
        {
            $price_enabled = get_post_meta($product_id, '_enable_price', true);
            if ($price_enabled == 'yes') {
                return true;
            }

            return false;
        }
        /**
        * Increases the registered visibilty columns by oe.
        * @param array $existing_columns existing columns on product page.
        */
        public function register_visibility_column($existing_columns)
        {
            if (empty($existing_columns) && !is_array($existing_columns)) {
                $existing_columns = array();
            }
            $column = array('quoteup_visibility' => quoteupHelpTip(__('Use Bulk Actions to change visibility', QUOTEUP_TEXT_DOMAIN), false, '', __('Visibility', QUOTEUP_TEXT_DOMAIN)));
            $keys = array_keys($existing_columns);
            $index = array_search('product_tag', $keys);
            $pos = false === $index ? count($existing_columns) : $index + 1;

            return array_merge(array_slice($existing_columns, 0, $pos), $column, array_slice($existing_columns, $pos));
        }

        /**
         * Gets the data for the Column to be eanabled.
         * @param string $column column name.
         * @param int $post_id product post id
         */
        public function render_visibility_column_data($column, $post_id)
        {
            if ('quoteup_visibility' == $column) {
                global $the_product;
                
                //Show Enquiry Icon
                $enable_pep_meta = get_post_meta($post_id, '_enable_pep', true);
                if ($enable_pep_meta == 'yes') {
                    //Check if 'Display Enquiry/Quote button only when Out of Stock' is checked.
                    if (!$this->settings || (isset($this->settings[ 'only_if_out_of_stock' ]) && $this->settings[ 'only_if_out_of_stock' ] == 1)) {
                        $is_product_in_stock = \quoteupIsProductInStock($the_product);
                        //Check if product is in stock or not
                        if ($is_product_in_stock) {
                            $this->visibility_icon_url('enquiry-enabled-if-out-of-stock');
                        } else {
                            $this->visibility_icon_url('enquiry-enabled');
                        }
                    } else {
                        //'Display Enquiry/Quote button only when Out of Stock' is unchecked
                        $this->visibility_icon_url('enquiry-enabled');
                    }
                } else {
                    $this->visibility_icon_url('enquiry-disabled');
                }

                //Show Price icon
                $enable_price_meta = get_post_meta($post_id, '_enable_price', true);
                if ($enable_price_meta == 'yes') {
                    $this->visibility_icon_url('price-visible');
                } else {
                    $this->visibility_icon_url('price-hidden');
                }

                // Show Add to cart icon
                $enable_add_to_cart = get_post_meta($post_id, '_enable_add_to_cart', true);
                if ($enable_price_meta == 'yes' && $enable_add_to_cart == 'yes') {
                    $this->visibility_icon_url('add-to-cart-enabled');
                } else {
                    $this->visibility_icon_url('add-to-cart-disabled');
                }
            }
        }
        /**
        * Gets the icon url for the specified property.
        * @param string $icon specific property
        */
        protected function visibility_icon_url($icon)
        {
            $image_directory = QUOTEUP_PLUGIN_URL.'/images/';
            $tooltip = '';
            $imageUrl = '';
            switch ($icon) {
                case 'add-to-cart-enabled':
                    $tooltip = 'add-to-cart-enabled';
                    $imageUrl = 'cart.png';
                    break;
                case 'add-to-cart-disabled':
                    $tooltip = 'add-to-cart-disabled';
                    $imageUrl = 'cart-disabled.png';
                    break;
                case 'price-visible':
                    $tooltip = 'price-visible';
                    $imageUrl = 'price.png';
                    break;
                case 'price-hidden':
                    $tooltip = 'price-hidden';
                    $imageUrl = 'price-disabled.png';
                    break;
                case 'enquiry-enabled':
                    $tooltip = 'enquiry-enabled';
                    $imageUrl = 'enquiry.png';
                    break;
                case 'enquiry-enabled-if-out-of-stock':
                    $tooltip = 'enquiry-enabled-if-out-of-stock';
                    $imageUrl = 'enquiry-enabled-if-out-of-stock.png';
                    break;
                case 'enquiry-disabled':
                    $tooltip = 'enquiry-disabled';
                    $imageUrl = 'enquiry-disabled.png';
                    break;
                default:
                    break;
            }
            echo quoteupHelpTip($this->visibilityIconsTooltip[$tooltip], false, $image_directory.$imageUrl);
        }
    }

    // end class

    $productsTable = new QuoteupProductsTable();
    $productsTable->prepare_items();

    $productsTable->process_bulk_action();

    // Prepare our table, this method has already run with the global table object

    // Override the global post table object
    add_filter('views_edit-product', function ($views) use ($productsTable) {
        global $wp_list_table;
        // Let's clone it to the global object
        $wp_list_table = clone $productsTable;

        return $views;
    });
}

<?php
namespace Includes\Admin;

/**
 * PEP plugin links class.
 *
 * @since 6.4.0
 */

if (! class_exists('QuoteupAdminPluginLinks') ) {

    /**
     * This class contains the links to be added on the plugins list page.
     *
     * @since 6.4.0
     */
    class QuoteupAdminPluginLinks
    {
        /**
         * A link to the plugin documentation
         *
         * @var string - documentation URL
         */
        private static $docs = 'https://wisdmlabs.com/docs/product/wisdm-product-enquiry-pro/';

        /**
         * A link to the plugin support page
         *
         * @var string - support page URL
         */
        private static $support = 'https://wisdmlabs.com/contact-us/#support';

        public function __construct()
        {
            add_filter('plugin_action_links_' . PEP_PLUGIN_BASENAME, array('\Includes\Admin\QuoteupAdminPluginLinks', 'pluginActionLinks'));
            add_filter('plugin_row_meta', array('\Includes\Admin\QuoteupAdminPluginLinks', 'pluginRowMeta'), 10, 2);
        }

        /**
         * A method to add a 'Settings' link beside 'Deactivate' link.
         *
         * @param  array $links Array of lins.
         * @return array Array of links.
         */
        public static function pluginActionLinks($links)
        {
            $url = get_admin_url().'admin.php?page=quoteup-for-woocommerce';
    
            $links = array_merge(
                array(
                    '<a href="' . esc_url($url) . '">' . __('Settings', QUOTEUP_TEXT_DOMAIN) . '</a>',
                ),
                $links
            );

            return $links;
        }

        /**
         * This method will add extra links to the plugins list page. We are adding documentation page, support page.
         *
         * @param  array  $links Plugin links.
         * @param  object $file  Plugin file.
         * @return array - updated list of row meta links
         */
        public static function pluginRowMeta($links, $file)
        {
            if (PEP_PLUGIN_BASENAME === $file ) {
                $row_meta = array(
                    'docs'    => '<a href="' . esc_url(apply_filters('woocommerce_docs_url', self::$docs)) . '" aria-label="' . esc_attr__('View Product Enquiry Pro documentation', QUOTEUP_TEXT_DOMAIN) . '">' . esc_html__('Docs', QUOTEUP_TEXT_DOMAIN) . '</a>',
                    'support'   => '<a href="' . esc_url(self::$support) . '" aria-label="' . esc_attr__('Premium support', QUOTEUP_TEXT_DOMAIN) . '">' . esc_html__('Premium support', QUOTEUP_TEXT_DOMAIN) . '</a>',
                );
                return array_merge($links, $row_meta);
            }
            return (array) $links;
        }
    }
}

new \Includes\Admin\QuoteupAdminPluginLinks();
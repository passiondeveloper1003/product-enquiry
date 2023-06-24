<?php
namespace Includes\Admin\Privacy;

/**
 * QuoteUp abstract privacy class.
 *
 * @since 6.2.0
 */
defined('ABSPATH') || exit;

/**
 * Abstract class that is intended to be extended by
 * specific privacy class. It handles the display
 * of the privacy message of the privacy id to the admin,
 * privacy data to be exported and privacy data to be deleted.
 *
 * @version  6.2.0
 */
abstract class QuoteupAbstractPrivacy
{
    /**
     * This is the name of this object type.
     *
     * @var string
     */
    public $name;

    /**
     * This is a list of exporters.
     *
     * @var array
     */
    protected $exporters = array();

    /**
     * This is a list of erasers.
     *
     * @var array
     */
    protected $erasers = array();

    /**
     * Constructor.
     *
     * @param string $name Plugin identifier.
     */
    public function __construct($name = '')
    {
        $this->name = $name;
        $this->init();
    }

    /**
     * Hook in events.
     */
    protected function init()
    {
        add_action('admin_init', array($this, 'addPrivacyMessage'));
        add_filter('wp_privacy_personal_data_exporters', array($this, 'registerExporters'), 10);
        add_filter('wp_privacy_personal_data_erasers', array($this, 'registerErasers'));
    }

    /**
     * Adds the privacy message on WC privacy page.
     */
    public function addPrivacyMessage()
    {
        if (function_exists('wp_add_privacy_policy_content')) {
            $content = $this->getPrivacyMessage();

            if ($content) {
                wp_add_privacy_policy_content($this->name, $this->getPrivacyMessage());
            }
        }
    }

    /**
     * Gets the message of the privacy to display.
     * To be overloaded by the implementor.
     *
     * @return string
     */
    public function getPrivacyMessage()
    {
        return '';
    }

    /**
     * Integrate this exporter implementation within the WordPress core exporters.
     *
     * @param array $exporters List of exporter callbacks.
     *
     * @return array
     */
    public function registerExporters($exporters = array())
    {
        foreach ($this->exporters as $id => $exporter) {
            $exporters[ $id ] = $exporter;
        }

        return $exporters;
    }

    /**
     * Integrate this eraser implementation within the WordPress core erasers.
     *
     * @param array $erasers List of eraser callbacks.
     *
     * @return array
     */
    public function registerErasers($erasers = array())
    {
        foreach ($this->erasers as $id => $eraser) {
            $erasers[ $id ] = $eraser;
        }

        return $erasers;
    }

    /**
     * Add exporter to list of exporters.
     *
     * @param string $id       ID of the Exporter.
     * @param string $name     Exporter name.
     * @param string $callback Exporter callback.
     */
    public function addExporter($exporterId, $name, $callback)
    {
        $this->exporters[ $exporterId ] = array(
            'exporter_friendly_name' => $name,
            'callback' => $callback,
        );

        return $this->exporters;
    }

    /**
     * Add eraser to list of erasers.
     *
     * @param string $id       ID of the Eraser.
     * @param string $name     Exporter name.
     * @param string $callback Exporter callback.
     */
    public function addEraser($eraserId, $name, $callback)
    {
        $this->erasers[ $eraserId ] = array(
            'eraser_friendly_name' => $name,
            'callback' => $callback,
        );

        return $this->erasers;
    }
}

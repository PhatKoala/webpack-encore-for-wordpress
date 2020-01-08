<?php
/**
 * Plugin Name: Webpack Encore for WordPress
 * Plugin URI: https://phatkoala.uk/webpack-encore-for-wordpress
 * Description: Manage your theme's assets.
 * Version: 1.0
 * Author: Stewart Walter
 * Author URI: https://phatkoala.uk/
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class WebpackEncore
 */
class WebpackEncore
{
    /**
     * @var WebpackEncore|null
     */
    private static $instance;

    /**
     * @var array
     */
    private $assets = [ ];

    /**
     * @var array
     */
    private $entries = [ ];

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * WebpackEncore constructor.
     */
    protected function __construct()
    {
        add_action('init', array($this, 'init'));
    }

    public function init()
    {
        $this->load(get_template_directory(), get_template_directory_uri());

        if (is_child_theme()) {
            $this->load(get_stylesheet_directory(), get_stylesheet_directory_uri());
        }
    }

    /**
     * @param string $directory
     * @param string $uri
     */
    public function load($directory, $uri)
    {
        $this->loadManifest($directory, $uri);
        $this->loadEntryPoints($directory, $uri);
    }

    /**
     * @param string $directory
     * @param string $uri
     * @return bool
     */
    public function loadManifest($directory, $uri)
    {
        $file = sprintf('%s/build/manifest.json', $directory);
        if (!file_exists($file)) {
            return false;
        }

        foreach (json_decode(file_get_contents($file), true) as $original => $compiled) {
            $this->assets[sprintf('%s/assets/%s', $uri, $original)] = sprintf('%s/build/%s', $uri, $compiled);
        }

        return true;
    }

    /**
     * @param string $directory
     * @param string $uri
     * @return bool
     */
    public function loadEntryPoints($directory, $uri)
    {
        $file = sprintf('%s/build/entrypoints.json', $directory);
        if (!file_exists($file)) {
            return false;
        }

        foreach (json_decode(file_get_contents($file), true) as $entryPoints) {
            foreach ($entryPoints as $entryName => $entryPoint) {
                foreach ($entryPoint as $key => $assets) {
                    foreach ($assets as $asset) {
                        $this->entries[$entryName][$key][] = sprintf('%s/build/%s', $uri, $asset);
                    }
                }
            }
        }

        return true;
    }

    /**
     * @param string $asset
     * @param bool $stylesheet
     * @return mixed|null
     */
    public function asset($asset, $stylesheet = false)
    {
        $asset = sprintf('%s/assets/%s', (($stylesheet) ? get_stylesheet_directory_uri() : get_template_directory_uri()), $asset);

        if (array_key_exists($asset, $this->assets)) {
            return $this->assets[$asset];
        }

        return null;
    }

    /**
     * @param string $entry
     * @return array
     */
    public function entry_css($entry)
    {
        return $this->entry($entry, 'css');
    }

    /**
     * @param string $entry
     * @return array
     */
    public function entry_js($entry)
    {
        return $this->entry($entry, 'js');
    }

    /**
     * @param string $entry
     * @param string $key
     * @return array
     */
    public function entry($entry, $key)
    {
        $entries = [ ];

        if (isset($this->entries[$entry][$key])) {
            foreach ($this->entries[$entry][$key] as $asset) {
                $entries[] = $asset;
            }
        }

        return $entries;
    }

    /**
     * @param string $entry
     */
    public function enqueue_entry_css($entry)
    {
        foreach ($this->entry($entry, 'css') as $key => $asset) {
            wp_enqueue_style(sprintf('%s-%d', $entry, $key), $asset, [ ], null);
        }
    }

    /**
     * @param string
     */
    public function enqueue_entry_js($entry)
    {
        foreach ($this->entry($entry, 'js') as $key => $asset) {
            wp_enqueue_script(sprintf('%s-%d', $entry, $key), $asset, [ ], null);
        }
    }
}

WebpackEncore::getInstance();

if (!function_exists('asset')) {
    function asset($file, $stylesheet = false) {
        $webpackEncore = WebpackEncore::getInstance();
        return $webpackEncore->asset($file, $stylesheet);
    }
}
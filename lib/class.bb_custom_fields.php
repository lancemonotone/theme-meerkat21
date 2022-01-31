<?php

namespace m21;

/**
 * Class Custom_Fields
 * @package m21
 * @link https://docs.wpbeaverbuilder.com/beaver-builder/developer/custom-modules/cmdg-14-create-custom-fields/
 *
 * Add custom field definitions to BB forms.
 * Watch out for name collisions with existing BB fields.
 *
 * Field directories are structured like this:
 * fields/
 * - {field}/
 * - - {field}.php (required)
 * - - {field}-tpl.php
 * - - assets/
 * - - - css/
 * - - - style.css
 * - - - js/
 * - - - app.js
 */

class BB_Custom_Fields {
    static $fields;

    public function __construct() {
        if ( ! class_exists('\FLBuilder')) {
            return;
        }

        add_action('init' , [$this, 'get_fields']);

        // Register assets
        add_action('init', array(&$this, 'enqueue_field_assets' ));

        // Register custom fields definitions
        add_action('fl_page_data_add_properties', array(&$this, 'register_fields'));

        // Register custom field js templates
        add_filter('fl_builder_custom_fields', array(&$this, 'register_field_templates'));
    }

    function get_fields() {
        $dirs = glob(THEME_DIR . '/bb-custom-fields/*', GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            // handle
            $type = substr($dir, strripos($dir, '/') + 1);
            // filepath
            $path = $dir . "/";
            // put it all together now
            self::$fields[ $type ] = $path;
        }
    }

    function register_fields() {
        foreach (self::$fields as $type => $path) {
            $file = $path . $type . '.php';
            if (file_exists($file)) {
                require_once($file);
            }
        }
    }

    /**
     * Registers our Underscore-formatted custom field templates.
     * @link http://underscorejs.org/#template
     *
     * @param array $templates
     *
     * @return array|mixed
     */
    function register_field_templates($templates = array()) {
        foreach (self::$fields as $type => $path) {
            $tpl_path = $path . $type . '-tpl.php';
            if (file_exists($tpl_path)) {
                $templates[ $type ] = $tpl_path;
            }
        }

        return $templates;
    }

    /**
     * Enqueues our custom field assets only if the builder UI is active.
     */
    function enqueue_field_assets() {
        if ( ! \FLBuilderModel::is_builder_active()) {
            return;
        }
        foreach (self::$fields as $type => $path) {
            if (file_exists($style = $path . 'assets/css/style.css')) {
                wp_enqueue_style($type, $style, array(), '');
            }
            if (file_exists($app = $path . 'assets/js/field.js')) {
                wp_enqueue_script($type, $app, array(), '', true);
            }
        }
    }
}

new BB_Custom_Fields();
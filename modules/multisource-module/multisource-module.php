<?php

namespace m21;

/**
 * 1. Update name and description properties below
 * 2. Customize the array of fields for FLBuilder::register_module
 * 3. Run 'webpack' in module directory to build js and css.
 */
defined('ABSPATH') or exit;

class Multisource_Module {
    private static $instance;
    public static $used_js = array();
    public static $used_css = array();
    public static $slug;
    public static $path;
    public static $url;
    public static $layout = 'custom';
    public static $template = 'multisource';
    public static $type = array(
        'post-carousel' => 'slider',
        'post-grid'     => 'layout'
    );

    public static $css_file;

    function __construct() {
        self::$slug     = basename(dirname(__FILE__));
        self::$path     = THEME_DIR . '/modules/' . self::$slug;
        self::$url      = THEME_URL . '/modules/' . self::$slug;
        self::$css_file = self::$path . '/css/frontend.css';

        add_filter('fl_builder_posts_module_layout_path', array($this, 'get_layout_path'), null, 3);

        // Add this module's twig folder so submodules can reference it.
        array_push(\Timber\Timber::$locations, self::$path . '/views');

        self::register_modules();
        self::modify_settings_form();
        self::add_data_source();
        self::add_loop_settings();

        self::add_css($this);
    }

    /**
     * Returns a custom path for post module layouts.
     *
     * @param string $path
     * @param string $layout
     * @param object $settings
     *
     * @return string
     * @since 1.0
     */
    static public function get_layout_path($path, $layout, $settings) {
        if ('default' === self::$layout) {
            return $path;
        }
        if ('custom' === self::$layout) {
            return str_replace('custom', 'grid', $path);
        }
        else return $path;
    }

    /**
     * We add the extra data sources after the parent module has loaded ui-loop-settings.php,
     * which is where the data_source field is rendered. The 'fl_builder_render_settings_field'
     * hook is called by FLBuilder::render_settings_field().
     **/
    function add_data_source() {
        add_filter('fl_builder_render_settings_field', function($field, $name, $settings) {
            if (in_array($settings->type, array_keys(self::$type))) {
                if ('data_source' === $name) {
                    $field['options']['endpoint'] = 'Endpoint';
                    /*$field['toggle']['endpoint']  = array(
                        'sections' => array(
                            'filter'
                        ));*/
                }
            }

            return $field;
        }, 20, 3);
    }

    /**
     * We tack on extra field sections after ui-loop-settings.php does its thing.
     */
    function add_loop_settings() {
        add_action('fl_builder_loop_settings_after_form', function($settings) {
            if (in_array($settings->type, array_keys(self::$type))) {
                include(self::$path . '/lib/loop-settings.php');
            }

        }, 21, 1);
    }

    /**
     * Loads the controller for each submodule
     */
    function register_modules() {
        if (class_exists('\FLBuilderModule')) {
            $dirs = glob(self::$path . '/modules/*', GLOB_ONLYDIR);
            foreach ($dirs as $dir) {
                $file = substr($dir, strripos($dir, '/') + 1);
                if (file_exists($module = $dir . "/{$file}.php")) {
                    require_once($module);
                }
            }
        }
    }

    /**
     * Add the Template dropdown, a Custom Layout option to the layout dropdown, toggle the
     * template dropdown when Custom Layout is selected.
     *
     * @todo This is not going to work on any module except 'slider' which is hard-coded. Not good. Fix it!
     */
    function modify_settings_form() {
        add_filter('fl_builder_register_settings_form', function($form, $id) {
            if (in_array($id, array_keys(self::$type))) {
                self::insert_form_fields(
                    array(
                        array(
                            'group'    => &$form[ self::$type[ $id ] ]['sections']['general']['fields'],
                            'position' => 1, // 0-indexed
                            'key'      => 'template',
                            'field'    => array(
                                'default' => '',
                                'label'   => 'Template',
                                'options' => array(),
                                'toggle'  => array(),
                                'type'    => 'select'
                            )
                        ),
                        array(
                            'group'    => &$form[ self::$type[ $id ] ]['sections']['general']['fields']['layout']['options'],
                            'position' => 2, // 0-indexed
                            'key'      => 'custom',
                            'field'    => 'Custom Layout'
                        ),
                        array(
                            'group'    => &$form[ self::$type[ $id ] ]['sections']['general']['fields']['layout']['toggle'],
                            'position' => 1,
                            'key'      => 'custom',
                            'field'    => array(
                                'fields'   => array(
                                    'template',
                                    'hover_transition',
                                    'post_icon_color',
                                    'text_color',
                                    'link_color',
                                    'equal_height'
                                ),
                                'sections' => array(
                                    'icons',
                                    'content'
                                )
                            )
                        )
                    ));
            }

            return $form;
        }, 20, 2);
    }

    static function modify_submodule_settings_form($instance) {
        add_filter('fl_builder_register_settings_form',
            function($form, $id) use ($instance) {
                if (in_array($id, $instance::$module_slugs) && 'boilerplate' !== $instance::$template) {
                    Multisource_Module::insert_form_fields(
                        array(
                            array(
                                'group'    => &$form[ self::$type[ $id ] ]['sections']['general']['fields']['template']['options'],
                                'position' => 1,
                                'key'      => $instance::$template,
                                'field'    => $instance::$name,
                            )
                        )
                    );
                }

                return $form;
            }, 21, 2);
    }

    /**
     * @param $args
     *
     * @return void
     */
    static function add_frontend_file($instance) {
        add_filter('fl_builder_module_frontend_file',
            function($file, $module) use ($instance) {
                if (self::is_valid($module, $instance)) {
                    if ( ! empty($instance::$frontend) && file_exists($instance::$frontend)) {
                        return $instance::$frontend;
                    }
                }

                return $file;
            }, 21, 2);
    }

    /**
     * Add the template CSS class if needed
     *
     * @param $args
     *
     * @return void
     */
    static function add_template_class($instance) {
        add_filter('fl_builder_module_custom_class',
            function($class, $module) use ($instance) {
                if (self::is_valid($module, $instance)) {
                    if ( ! empty($instance::$class)) {
                        $class .= ' ' . $instance::$class;
                    }

                    return $class;
                }

                return $class;

            }, 21, 2);
    }

    /**
     * Would be nice to differentiate between modules, but the hook isn't fired for
     * individual mods so load js for all. This means it loads twice in many cases.
     * I haven't noticed a performance hit but it's sloppy. :(
     *
     * @param $instance
     * @param $js_file
     */
    static function add_js($instance, $js_file) {
        add_filter('fl_builder_render_js',
            function($js, $nodes, $global_settings, $include_global) use ($instance, $js_file) {
                $js .= self::add_inline_file($js_file);

                return $js;
            }, 21, 4);
    }

    static function add_css($instance) {
        add_filter('fl_builder_render_css',
            function($css, $module, $id) use ($instance) {
                if ( ! in_array($instance::$template, self::$used_css)) {
                    array_push(self::$used_css, $instance::$template);
                    $css .= self::add_inline_file($instance::$css_file);
                }

                return $css;
            }, 21, 3);
    }

    /**
     * Add inline css or js
     *
     * @param $file ['file', 'instance']
     *
     * @return string
     */
    static function add_inline_file($file) {
        if ( ! empty($file) && file_exists($file)) {
            ob_start();
            include($file);

            return ob_get_clean();
        }

        return '';
    }

    /**
     * @param array $fields
     *
     * @return void
     */
    static function insert_form_fields(array $fields): void {
        if ( ! empty($fields)) {
            // We'll rebuild the fields array here, placing the
            // new field at the appropriate position
            foreach ($fields as $field) {
                $position       = empty($field['position']) ? count($field['group']) - 1 : $field['position'];
                $field['group'] = array_merge(
                    array_slice($field['group'], 0, $position),
                    array($field['key'] => $field['field']),
                    array_slice($field['group'], $position)
                );
            }
        }
    }

    /**
     * @param $module
     * @param $instance
     *
     * @return bool
     */
    static function is_valid($module, $instance) {
        return 'boilerplate' !== $instance::$template && in_array($module->slug, $instance::$module_slugs)
            && $module->settings->layout === self::$layout
            && $module->settings->template === $instance::$template;
    }


    public static function init() {
        if ( ! isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}

Multisource_Module::init();
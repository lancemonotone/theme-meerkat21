<?php

namespace m21;

/**
 * Class BB
 * @package m21
 *
 * Configures BB environment to work with our theme.
 */
class BB {
    private $global_settings = array(
        'responsive_breakpoint'     => '910',
        'row_width'                 => '1200',
        'row_content_width_default' => 'fixed'
    );

    private $colors = array(
        '542f7c', // wms purple
        '3c2151', // amethyst
        '5c9396', // lichen
        '497476', // lichen dark + 10%
        '9da2a2', // iron
        'ddcf57', // wattle
        'cf432b', // orangered
        '939e49', // sycamore
        '3b3324', // darkbrown
        '000000', // black
        'ffffff', // white
    );

    public function __construct() {
        // Debugging: Disable BB caching. This will cause modules in page partials
        // to be rendered without frontend styles (header, alert, etc).
        //add_action('wp', array(&$this, 'refresh_bb_cache'));

        // Set up BB support
        add_theme_support('fl-theme-builder-headers');
        add_theme_support('fl-theme-builder-parts');
        add_filter('fl_theme_builder_part_hooks', array(&$this, 'register_part_hooks'));
        add_action('wp', array(&$this, 'setup_headers_and_footers'));

        // Uncomment this to import all layouts when theme is activated.
        add_action('after_switch_theme', array(&$this, 'import_themer_layouts'));

        // Uncomment this to delete all layouts when theme is deactivated.
        //add_action('switch_theme', array(&$this, 'delete_themer_layouts'));

        // add presets
        add_filter('fl_builder_color_presets', array(&$this, 'add_color_presets'));

        // set BB global settings
        add_action('after_setup_theme', array(&$this, 'save_global_settings'));

        // Change location of frontend.php to allow Timber to find views dir
        add_filter('fl_builder_module_frontend_file', array(&$this, 'set_frontend_path'), 10, 2);
        add_filter('fl_builder_render_module_html', array(&$this, 'render_frontend_file'), 10, 4);

        // Allow editors and admin to save iframes
        add_filter( 'fl_builder_ui_js_config', function( $config ) {
            $config['userCaps']['unfiltered_html'] = true;
            return $config;
            },10
        );
    }

    public function refresh_bb_cache() {
        if (\FLBuilderModel::is_builder_enabled()) {
            \FLBuilder::render_js();
            \FLBuilder::render_css();
        }
    }

    /**
     * Setup parts.
     *
     * @return array
     * @since 1.0
     */

    function register_part_hooks() {
        return array(
            array(
                'label' => 'Header',
                'hooks' => array(
                    'bb_alert' => 'Alert',
                    'bb_site_header'   => 'Header',
                ),
            ),
            array(
                'label' => 'Sidebar',
                'hooks' => array(
                    'bb_sidebar_site_nav' => 'SiteNav',
                    'bb_sidebar_aside'       => 'Sidebar'
                )
            ),
            array(
                'label' => 'Entry Footer',
                'hooks' => array(
                    'bb_entry_footer'  => 'Entry Footer'
                )
            )
        );
    }

    /**
     * Setup headers and footers.
     *
     * @return void
     * @since 1.0
     */
    function setup_headers_and_footers() {
        add_action('bb_site_header', array('\FLThemeBuilderLayoutRenderer', 'render_header'), 999);
    }

    /**
     * Modify path to module controller to allow us to more easily integrate twig view directory.
     *
     * @param $file
     * @param $module
     *
     * @return string
     */
    function set_frontend_path($file, $module) {

        if (file_exists($file)) return $file;

        // Probably a Williams module
        $file = $module->dir . 'frontend.php';

        return $file;
    }

    function render_frontend_file($file, $type, $settings, $module) {
        return $this->set_frontend_path($file, $module);
    }

    /**
     * Prevent cached assets from being deleted on load. Why are they
     * being deleted? Good question, but this seems to solve it.
     */
    function save_global_settings() {
        $old_settings = \FLBuilderModel::get_global_settings();
        $settings     = \FLBuilderModel::sanitize_global($this->global_settings);
        $new_settings = (object) array_merge((array) $old_settings, (array) $settings);

        update_option('_fl_builder_settings', $new_settings);
    }

    /**
     * Add color presets to Beaver Builder
     *
     * @return array
     * @since 1.0
     */
    function add_color_presets($colors) {
        return array_merge($colors, $this->colors);
    }

    /**
     * Deletes all layouts when theme is deactivated.
     * Not currently used.
     */
    function delete_themer_layouts() {
        if (class_exists('CPT_Delete')) CPT_Delete::delete_posts('fl-theme-layout');
    }

    /**
     * Auto-import standard themer layouts when theme is activated.
     */
    function import_themer_layouts() {
        if (class_exists('\WP_Import')) {
            define('WP_LOAD_IMPORTERS', true);
            //console_log('import_layout: ' . WP_LOAD_IMPORTERS);
            //console_log('doing import');
            $importer = new \WP_Import();
            $file     = THEME_DIR . '/import/import.xml';
            if (file_exists($file)) {
                $importer->import($file);
            }
        }
    }
}

new BB();

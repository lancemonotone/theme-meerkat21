<?php /** @noinspection JSNonStrictModeUsed */

namespace m21;

class Js {
    private static $instance;
    private static $js;

    protected function __construct() {
        add_action('wp_head', array(&$this, 'detect_javascript'), 0);
        add_action('wp_enqueue_scripts', array(&$this, 'load_frontend_js'), 10);

        // Make available to twigs for on-demand loading
        // e.g. {{ js.load_js_src('addthis') }}
        add_filter('timber/context', function($context) {
            $context['js'] = self::instance();

            return $context;
        });
    }

    /**
     * Use in twig templates to enable specific script on the fly
     * e.g. {{ js.load_js_src('addthis') }}
     *
     * @param $key
     */
    public static function load_js_src($key) {
        self::$js[ $key ]['load'] = true;
    }

    /**
     * Handles JavaScript detection.
     *
     * Adds a `js` class to the root `<html>` element when JavaScript is detected.
     */
    public function detect_javascript() {
        echo "<script>(function(html){html.className = html.className.replace(/\bno-js\b/,'js')})(document.documentElement);</script>\n";
    }

    /**
     * Wrapper to automate enqueuing of scripts and inline code.
     */
    public function load_frontend_js() {
        $this->set_js();

        foreach (self::$js as $handle => $data) {
            if ( ! isset($data['load']) || $data['load'] === true) {
                $this->do_load($handle, $data);
            }
        }
    }

    /**
     * Add inline scripts to page source.
     *
     * @param array $args Array containing these keys
     * @param string 'handle' WP or custom handle
     * @param string 'path' Relative to calling script: __DIR__ . '/path/to/script.js'
     * @param array  'find' (opt) Array of find/replace arrays: [['find_1', 'find_2'], ['replace_1', 'replace_2']]
     * @param array  'deps' (opt) Array of WP or custom handles on which this script depends
     */
    public static function do_inline($args): void {
        $args = wp_parse_args($args, [
            'handle'  => '',
            'path'    => '',
            'replace' => [],
            'deps'    => []
        ]);

        // Executable function
        $script = function() use ($args) {
            $out = file_get_contents($args['path']);
            if (count($args['replace'])) {
                [$find, $replace] = $args['replace'];
                $out = str_replace($find, $replace, $out);
            }

            return $out;
        };

        self::do_load($args['handle'], array(
            'inline' => $script,
            'deps'   => $args['deps']
        ));
    }

    /**
     * Add scripts to the load stack.
     *
     * @param string $handle
     * @param array  $data Containing any of the following keys
     * @param array|string $src URL to resource. If array, use this form: ['handle' => 'src'].
     * @param bool   'head' Load in head (default: false)
     * @param int    'v' Version (default: false)
     * @param array  'deps' Array of WP or custom handles which this script depends on
     * @param string 'inline' Executable PHP closure returning JS string, see self::do_inline() for example
     * @param array  'local' Array of arrays utilizing wp_localize_script()
     * @param array  'styles' Array of arrays of 'handle' and source URL
     */
    public static function do_load(string $handle, array $data): void {
        if ( ! empty($data['src'])) {
            $in_footer = isset($data['head']) && $data['head'] === true ? false : true;
            $version   = isset($data['v']) ? $data['v'] : false;

            // If array, make sure at least one of these elements has the same handle as the
            // parent $handle, or 'inline' and 'local' scripts will not be loaded. Use form ['handle1' = 'src1', 'handle2 = 'src2'].
            if (is_array($data['src'])) {
                foreach ($data['src'] as $hand => $src) {
                    wp_enqueue_script($hand, $src, isset($data['deps']) ? $data['deps'] : null, $version, $in_footer);
                }
            } else {
                wp_enqueue_script($handle, $data['src'], isset($data['deps']) ? $data['deps'] : null, $version, $in_footer);
            }
        }

        if (isset($data['inline'])) {
            wp_add_inline_script($handle, 'jQuery(document).ready(function($){' . $data['inline']() . '});');
        }

        if (isset($data['local'])) { // always an array
            foreach ($data['local'] as $obj => $arr) {
                wp_localize_script($handle, $obj, $arr);
            }
        }

        if (isset($data['styles'])) {
            foreach ($data['styles'] as $style) {
                wp_enqueue_style($style['handle'], $style['src']);
            }
        }
    }

    /**
     *  Order keys in the order the files should be loaded.
     */
    protected function set_js() {
        self::$js    = array(
            'printfriendly'       => array(
                'src' => 'https://pf-cdn.printfriendly.com/ssl/main.js',
                'v'   => '1.0',
            ),
            'tag_manager'         => array(
                'src' => M16_TEMP_JS_URL . '/lib/tagmanager.js',
                'v'   => '1.0.0'
            ),
            'skip-link-focus-fix' => array(
                'src' => M16_TEMP_JS_URL . '/vendor/skip-link-focus-fix.js',
                'v'   => '20151112'
            ),
            'expando_tables'      => array(
                'src'  => M16_TEMP_JS_URL . '/modules/expando_tables.js',
                'deps' => array('jquery'),
                'v'    => '1.0.0'
            ),
            'navigation'          => array(
                'src'  => M16_TEMP_JS_URL . '/modules/navigation.js',
                'deps' => array(
                    'jquery-ui-core',
                    'jquery-ui-tooltip',
                ),
                'v'    => '1.1.1'
            ),
            'theme_uisearch'      => array(
                'src'  => M16_TEMP_JS_URL . '/modules/uisearch.js',
                'deps' => array('jquery'),
                'v'    => '1.0.0'
            ),
            'directory'           => array(
                'src'  => M16_TEMP_JS_URL . '/lib/directory.js',
                'deps' => array(
                    'jquery',
                    //'common_lib'
                ),
                'v'    => '2.2',
            ),
            'bootstrap'           => array(
                'load' => false,
                'src'  => M16_TEMP_JS_URL . '/lib/bootstrap.min.js',
                'deps' => array('jquery'),
                'v'    => '1.0'
            ),
            'cycle'               => array(
                'src'  => WMS_LIB_URL . '/assets/js/vendor/jquery.cycle/jquery.cycle2.min.js',
                'deps' => array('jquery'),
                'v'    => '2.15'
            ),
            'cookie'              => array(
                'src'  => WMS_LIB_URL . '/assets/js/vendor/jquery.cookie.js',
                'deps' => array('jquery'),
                'v'    => '1.0'
            ),
            'purl'                => array(
                'src'  => WMS_LIB_URL . '/assets/js/vendor/purl.js',
                'deps' => array('jquery'),
                'v'    => '1.0'
            ),
            'detect_swipe'        => array(
                'src'  => WMS_LIB_URL . '/assets/js/vendor/jquery.detect_swipe.js',
                'deps' => array('jquery'),
                'v'    => '2.1.3'
            ),
            'featherlight'        => array(
                'src'  => WMS_LIB_URL . '/assets/js/vendor/featherlight/featherlight.min.js',
                'deps' => array('jquery', 'detect_swipe'),
                'v'    => '1.5.0'
            ),
            'featherlight-config' => array(
                'src'  => WMS_LIB_URL . '/assets/js/vendor/featherlight/featherlight-config.js',
                'deps' => array('featherlight'),
                'v'    => '1.5.3'
            ),
        );
    }

    /**
     * Returns the singleton instance of this class.
     *
     * @return Js The singleton instance.
     */
    public static function instance() {
        if ( ! static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}

Js::instance();

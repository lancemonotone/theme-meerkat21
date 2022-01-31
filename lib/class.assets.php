<?php

namespace m21;

/**
 * include app js and other styles/scripts
 */
class Assets {
    public function __construct() {
        add_action('wp_enqueue_scripts', [&$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        $js_path = THEME_JS_PATH . '/main.js';
        $js_url = THEME_JS_URL . '/main.js';
        $js_version = file_exists($js_path) ? filemtime($js_path) : time();
        wp_enqueue_script(__NAMESPACE__ . '-js', $js_url, array('jquery'), $js_version);

        $css_path = THEME_CSS_PATH . '/style.css';
        $css_url = THEME_CSS_URL . '/style.css';
        $styles_version = file_exists($css_path) ? filemtime($css_path) : time();
        wp_enqueue_style(__NAMESPACE__ . '-css', $css_url, null, $styles_version, false);

        wp_enqueue_style('blacktie-icons', WMS_LIB_URL . '/assets/fonts/blacktie/black-tie.css', null, '3.4.1');
        wp_enqueue_style('eph-slab', WMS_LIB_URL . '/assets/fonts/ephfamily/eph-family.css', null, '1.0.0');
    }
}

new Assets();
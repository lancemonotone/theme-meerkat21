<?php

namespace m21;

/**
 * Include app js and other styles/scripts
 */
class Assets {
    public function __construct() {
        add_action( 'wp_enqueue_scripts', [&$this, 'enqueue_assets'] );
    }

    public function enqueue_assets() {
        $deps = include THEME_ASSETS_PATH . '/index.asset.php';

        $js_url = THEME_ASSETS_URL . '/index.js';
        wp_enqueue_script( __NAMESPACE__ . '-js', $js_url, $deps['dependencies'], $deps['version'], true );

        $css_url = THEME_ASSETS_URL . '/index.css';
        wp_enqueue_style( __NAMESPACE__ . '-css', $css_url, null, $deps['version'] );
    }
}

new Assets();

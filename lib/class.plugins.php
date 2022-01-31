<?php

namespace m21;

class Plugins {
    protected $auto_activate = array(
        'bb-plugin/fl-builder.php',
        'bb-theme-builder/bb-theme-builder.php',
        'wms-directory/wms-directory.php',
        'wms-navbox/index.php',
        'wms-shortcode/shortcode-builder.php',
    );

    protected $auto_deactivate = array(
        'page-links-to/page-links-to.php',
    );

    public function __construct() {
        add_action('init', [$this, 'activate_plugins'], 10);
        add_action('switch_theme', [$this, 'deactivate_plugins']);
    }

    /**
     * Activate plugins
     */
    function activate_plugins(): void {
        activate_plugins($this->auto_activate);
        deactivate_plugins($this->auto_deactivate);
        require_once(THEME_DIR . '/plugins/index.php');
    }

    /**
     * Deactivate plugins
     */
    function deactivate_plugins(): void {
        deactivate_plugins($this->auto_activate);
    }
}

new Plugins();
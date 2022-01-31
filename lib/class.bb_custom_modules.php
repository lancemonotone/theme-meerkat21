<?php

namespace m21;

/**
 * Load custom modules.
 */
class BB_Custom_Modules {
    public function __construct() {
        add_action('init', [$this, 'load_modules'], 15);
    }

    public static function load_modules() {
        $dirs = glob(THEME_DIR . '/modules/*', GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            $file = substr($dir, strripos($dir, '/') + 1);
            if (file_exists($module = $dir . "/{$file}.php")) {
                require_once($module);
            }
        }
    }
}

new BB_Custom_Modules();
<?php

namespace m21;

require('constants.php');

class Functions {
    public function __construct() {
        add_action('after_setup_theme', array(&$this, 'load_libs'));
    }

    /**
     * Load class libs by file or directory (one level deep).
     */
    public function load_libs() {
        $libs = glob(THEME_DIR . '/lib/*');
        foreach ($libs as $lib) {
            if (is_file($lib)) {
            // If it's a file, load it
                require_once($lib);
            } elseif (is_dir($lib)) {
                // If it's a directory, look inside for class files
                $modules = glob($lib.'/class*');
                foreach ($modules as $module) {
                    require_once($module);
                }
            }
        }
    }
}

new Functions();
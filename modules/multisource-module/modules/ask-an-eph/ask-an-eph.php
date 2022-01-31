<?php

namespace m21;

class MS_Ask_An_Eph {
    // Template name
    public static $name = 'Ask an Eph';
    // Template slug
    public static $template = 'ask-an-eph';
    // Container div class for styling or js
    public static $class = 'ask-an-eph-grid quad-container';
    // Which BB modules should this submodule extend
    public static $module_slugs = array(
        'post-carousel'
    );

    public static $dir, $frontend, $js_settings_file, $css_file;

    public function __construct() {
        self::$dir              = dirname(__FILE__);
        self::$frontend         = self::$dir . '/frontend.php';
        self::$js_settings_file = self::$dir . '/js/settings.js';
        self::$css_file         = self::$dir . '/css/frontend.css';

        Multisource_Module::modify_submodule_settings_form($this);
        Multisource_Module::add_frontend_file($this);
        Multisource_Module::add_js($this, self::$js_settings_file);
        Multisource_Module::add_css($this);
        Multisource_Module::add_template_class($this);
    }

    /**
     * Returns the singleton instance of this class.
     *
     * @return MS_Ask_An_Eph The singleton instance.
     */
    public static function instance() {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * singleton instance.
     *
     * @return void
     */
    private function __clone() {
    }

    /**
     * Private unserialize method to prevent unserializing of the singleton
     * instance.
     *
     * @return void
     */
    private function __wakeup() {
    }

    private static $instance;
}

MS_Ask_An_Eph::instance();
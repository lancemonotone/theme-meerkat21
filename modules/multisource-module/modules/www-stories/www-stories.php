<?php

namespace m21;

class MS_Www_Stories {
    // Start config
    // This is the name that shows up in the Template dropdown
    public static $name = 'Www_Stories';
    // This is the slug, used for ids and such
    public static $template = 'www_stories';
    // Add template class for style or js hooks (optional)
    public static $class = '';
    // What kind of BB module(s) should this override?
    public static $module_slugs = array(
        'post-grid',
    );
    // End config

    public static $dir, $frontend, $js_frontend_file, $js_settings_file, $css_file;

    public function __construct() {
        self::$dir              = dirname(__FILE__);
        self::$frontend         = self::$dir . '/frontend.php';
        // self::$js_frontend_file = self::$dir . '/js/frontend.js';
        // self::$js_settings_file = self::$dir . '/js/settings.js';
        self::$css_file         = self::$dir . '/css/frontend.css';

        // Required
        Multisource_Module::modify_submodule_settings_form($this);
        Multisource_Module::add_frontend_file($this);

        // All css should be imported into this file (use webkit to build)
        Multisource_Module::add_css($this);
        //Multisource_Module::add_template_class(self::$class);

        // Do this with as many js files as you need (you can use webkit to build)
        // Multisource_Module::add_js($this, self::$js_settings_file);
        //MultisourceSlider_Modules::add_js($this, self::$js_frontend_file);

    }

    /**
     * Returns the singleton instance of this class.
     *
     * @return MS_Student_Groups The singleton instance.
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

MS_Www_Stories::instance();

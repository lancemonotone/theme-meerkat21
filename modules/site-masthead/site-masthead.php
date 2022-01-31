<?php

namespace m21;

/**
 * This is an example module with only the basic
 * setup necessary to get it working.
 *
 * @class FLBasicExampleModule
 */

class Site_Masthead_Module extends \FLBuilderModule {
    /**
     * Constructor function for the module. You must pass the
     * name, description, dir and url in an array to the parent class.
     *
     * @method __construct
     */
    public function __construct() {
        parent::__construct(array(
            'name'          => __('Site Masthead', 'fl-builder'),
            'description'   => __('Site Title and Header Image', 'fl-builder'),
            'category'      => __('Williams Modules', 'fl-builder'),
            'dir'           => THEME_DIR . '/modules/site-masthead/',
            'url'           => THEME_URL . '/modules/site-masthead/',
            'editor_export' => true, // Defaults to true and can be omitted.
            'enabled'       => true, // Defaults to true and can be omitted.
            'group'         => 'Williams',
        ));
    }
}

/**
 * Register the module and its form settings.
 */
\FLBuilder::register_module('m21\Site_Masthead_Module', array(

));

<?php

namespace m21;

require_once('lib/class.breadcrumbs.php');

class Breadcrumb_Bar_Module extends \FLBuilderModule {
    /**
     * Constructor function for the module. You must pass the
     * name, description, dir and url in an array to the parent class.
     *
     * @method __construct
     */
    public function __construct() {
        parent::__construct(array(
            'name'          => __('Breadcrumb Bar', 'fl-builder'),
            'description'   => __('Breadcrumb Bar.', 'fl-builder'),
            'category'      => __('Williams Modules', 'fl-builder'),
            'dir'           => THEME_DIR . '/modules/breadcrumb-bar/',
            'url'           => THEME_URL . '/modules/breadcrumb-bar/',
            'editor_export' => true, // Defaults to true and can be omitted.
            'enabled'       => true, // Defaults to true and can be omitted.
            'group'         => 'Williams',
        ));
    }
}

/**
 * Register the module and its form settings.
 */
\FLBuilder::register_module('m21\Breadcrumb_Bar_Module', array(

));
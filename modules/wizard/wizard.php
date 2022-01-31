<?php

namespace m21;

class Wizard_Module extends \FLBuilderModule {
    public $name = 'Wizard Module';
    public $description = 'This is a sample module.';
    public $enabled = true;
    public $slug = __DIR__;

    public function __construct() {
        parent::__construct(array(
            // Shouldn't need to change these...
            'name'            => __($this->name, 'fl-builder'),
            'description'     => __($this->description, 'fl-builder'),
            'group'           => 'Williams',
            'category'        => __('Williams Modules', 'fl-builder'),
            'dir'             => THEME_DIR . '/modules/' . $this->slug . '/',
            'url'             => THEME_URL . '/modules/' . $this->slug . '/',
            'icon'            => 'button.svg',
            'editor_export'   => true, // Defaults to true and can be omitted.
            'enabled'         => $this->enabled, // Defaults to true and can be omitted.
            'partial_refresh' => false, // Defaults to false and can be omitted.
        ));

    }

    public static function get_menu_array() {
        $menus = wp_get_nav_menus();
        $options = array( 0 => '' );
        foreach ( $menus as $menu ) { $options[ $menu->slug ] = $menu->name; }
        return $options;
    }
}

\FLBuilder::register_module('m21\Wizard_Module', array(
    'general' => array( // Tab
        'title'    => __('General', 'fl-builder'), // Tab title
        'sections' => array( // Tab Sections
            'general' => array( // Section
                'title'  => __('Wizard Builder', 'fl-builder'), // Section Title
                'fields' => array(
                    'wizard_title' => array(
                        'type'  => 'text',
                        'label' => __('Wizard Title', 'fl-builder'),
                        'description' => __('Required: Describe what this wizard does. The description is made available to those using assistive technology.', 'fl-builder'),
                    ),
                    'wizard_menu' => array(
                        'type'  => 'select',
                        'label' => __('Source of Wizard Data', 'fl-builder'),
                        'options' => Wizard_Module::get_menu_array()
                    )
                )
            )
        )
    )
));


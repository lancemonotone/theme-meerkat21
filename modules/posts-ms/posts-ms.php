<?php

namespace m21;

class Posts_Ms_Module extends \FLBuilderModule {
    public $name = 'Posts_Ms';
    public $description = 'Post carousel that queries another site';
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
            //
            'icon'            => 'wordpress-alt.svg',
            'editor_export'   => true, // Defaults to true and can be omitted.
            'enabled'         => $this->enabled, // Defaults to true and can be omitted.
            'partial_refresh' => false, // Defaults to false and can be omitted.
        ));

    }

    public static function get_form() {
        $fields = array(
             'general' => array( // Tab
                'title'    => __('General', 'fl-builder'), // Tab title
                'sections' => array( // Tab Sections
                    'general' => array( // Section
                        'title'  => __('Section Details', 'fl-builder'), // Section Title
                                'fields' => array( // Section Fields
                                    'ms_post_header' => array(
                                        'type'          => 'text',
                                        'label'         => __( 'Section Header', 'fl-builder' ),
                                        'default'       => '',
                                        'maxlength'     => '250',
                                        'size'          => '50',
                                        'placeholder'   => __( 'Type a section header here...', 'fl-builder' ),
                                        'class'         => 'my-css-class',
                                        'description'   => __( '<br/>Displayed as header at top of section.', 'fl-builder' ),
                                        'help'          => __( 'Type in some header text here!', 'fl-builder' )
                                    ),
                                      )
            )
        )
    ),
            'content'   => array(
            'title'         => __( 'Content', 'fl-builder' ),
            'file'          => THEME_DIR . '/includes/loop-settings.php',
        ),
       
        
    );
        return $fields;
    }
}

\FLBuilder::register_module('m21\Posts_Ms_Module', Posts_Ms_Module::get_form());

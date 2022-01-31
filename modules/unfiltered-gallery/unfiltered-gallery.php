<?php

namespace m21;

/**
 * This is an example module with only the basic
 * setup necessary to get it working.
 *
 * @class BB_Unfiltered_Gallery
 */
class BB_Unfiltered_Gallery extends \FLBuilderModule {
    /**
     * Constructor function for the module. You must pass the
     * name, description, dir and url in an array to the parent class.
     *
     * @method __construct
     */
    public function __construct() {
        parent::__construct(array(
            'name'          => __('Unfiltered Gallery', 'fl-builder'),
            'description'   => __('Unfiltered overlay gallery.', 'fl-builder'),
            'category'      => __('Williams Modules', 'fl-builder'),
            'dir'           => THEME_DIR . '/modules/unfiltered-gallery/',
            'url'           => THEME_URL . '/modules/unfiltered-gallery/',
            'editor_export' => true, // Defaults to true and can be omitted.
            'enabled'       => true, // Defaults to true and can be omitted.
            'group'         => 'Williams',
            'icon'            => 'format-gallery.svg',
        ));
    }
}

/**
 * Register the module and its form settings.
 */
\FLBuilder::register_module('m21\BB_Unfiltered_Gallery', array(
    'general' => array( // Tab
        'title'    => __('General', 'fl-builder'), // Tab title
        'sections' => array( // Tab Sections
            'general' => array( // Section
                'title'  => __('Unfiltered Gallery Fields', 'fl-builder'), // Section Title
                'fields' => array( // Section Fields
                    'ug_photos_field' => array(
                        'type'          => 'multiple-photos',
                        'label'         => __( 'Unfiltered Images', 'fl-builder' )
                    ),
                )
            )
        )
    )
));
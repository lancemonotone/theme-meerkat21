<?php

namespace m21;

/**
 * This is an example module with only the basic
 * setup necessary to get it working.
 *
 * @class FLBasicExampleModule
 */
class Network_Header_Module extends \FLBuilderModule {
    /**
     * Constructor function for the module. You must pass the
     * name, description, dir and url in an array to the parent class.
     *
     * @method __construct
     */
    public function __construct() {
        parent::__construct(array(
            'name'          => __('Network Header', 'fl-builder'),
            'description'   => __('Wordmark, Search and Mega Menu.', 'fl-builder'),
            'category'      => __('Williams Modules', 'fl-builder'),
            'dir'           => THEME_DIR . '/modules/network-header/',
            'url'           => THEME_URL . '/modules/network-header/',
            'editor_export' => true, // Defaults to true and can be omitted.
            'enabled'       => true, // Defaults to true and can be omitted.
            'group'         => 'Williams',
        ));
    }
}

/**
 * Register the module and its form settings.
 */
\FLBuilder::register_module('m21\Network_Header_Module', array(
    'general' => array( // Tab
        'title'    => __('General', 'fl-builder'), // Tab title
        'sections' => array( // Tab Sections
            'general'  => array( // Section
                'title'  => __('Info', 'fl-builder'), // Section Title
                'fields' => array( // Section Fields

                    'network_header_instructions' => array(
                        'type'    => 'raw',
                        'label'   => 'This module displays the network header with the  wordmark, search field and mega menu.',
                        'content' => '',
                    ),

                ),

            ), //end general
            'general2' => array( // Section

                'title'  => __('Settings', 'fl-builder'), // Section Title
                'fields' => array( // Section Fields

                    'site_bug' => array(
                        'type'        => 'textarea',
                        'label'       => __('Site bug (not active, use customizer)', 'fl-builder'),
                        'default'     => '',
                        'placeholder' => __('', 'fl-builder'),
                        'rows'        => '1',
                        'preview'     => array(
                            'type'     => 'text',
                            'selector' => '.bb-site-bug'
                        )
                    ),

                )
            ) //end general
        )
    ),

));
<?php

namespace m21;

/**
 * This is an example module with only the basic
 * setup necessary to get it working.
 *
 * @class FLBasicExampleModule
 */
class BB_Stats extends \FLBuilderModule {
    /**
     * Constructor function for the module. You must pass the
     * name, description, dir and url in an array to the parent class.
     *
     * @method __construct
     */
    public function __construct() {
        parent::__construct(array(
            'name'          => __('WMS Stats', 'fl-builder'),
            'description'   => __('Responsive Stats.', 'fl-builder'),
            'category'      => __('Williams Modules', 'fl-builder'),
            'dir'           => THEME_DIR . '/modules/stats/',
            'url'           => THEME_URL . '/modules/stats',
            'editor_export' => true, // Defaults to true and can be omitted.
            'enabled'       => true, // Defaults to true and can be omitted.
            'group'         => 'Williams',
        ));
    }
}

/**
 * Register the module and its form settings.
 */
\FLBuilder::register_module('m21\BB_Stats', array(
    'general' => array( // Tab
        'title'    => __('General', 'fl-builder'), // Tab title
        'sections' => array( // Tab Sections
            'general' => array( // Section
                'title'  => __('Statistic Text', 'fl-builder'), // Section Title
                'fields' => array( // Section Fields
                    'stats_instructions' => array(
                        'type'    => 'raw',
                        'label'   => 'This module only supports 4 or more stats.  If you wish to do 3 or fewer, please use individul text modules in columns',
                        'content' => '',
                    ),
                    'wms_stats' => array(
                        'type'          => 'text',
                        'label'         => __( 'Stat', 'fl-builder' ),
                        'multiple'      => true,
                    ),
                )
            )
        )
    )
));
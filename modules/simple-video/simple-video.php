<?php

namespace m21;

class Simple_Video_Module extends \FLBuilderModule {
    public $name = 'Simple Video';
    public $description = 'A simple carousel for 3 videos';
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
            'icon'            => 'format-video.svg',
            'editor_export'   => true, // Defaults to true and can be omitted.
            'enabled'         => $this->enabled, // Defaults to true and can be omitted.
            'partial_refresh' => false, // Defaults to false and can be omitted.
        ));

    }

    public static function get_form() {
        $fields = array(
            'content' => array( // Tab
                'title'    => __('Content', 'fl-builder'), // Tab title
                'sections' => array( // Tab Sections
                    'Videos' => array( // Section
                        'title'  => __('Videos', 'fl-builder'), // Section Title
                                'fields' => array( // Section Fields
                                    'min_videos' => array(
                                        'type'    => 'raw',
                                        'label'   => 'Warning!',
                                        'content' => '<p>Please add, at a minimum, <strong>four videos</strong> to this module and use the youtube embed url like this: <strong>https://www.youtube.com/embed/w4eI8rHH4QE</strong></p>',
                                    ),
                                    'simple_video_form' => array(
                                        'type'          => 'form',
                                        'label'         => __('Video', 'fl-builder'),
                                        'form'          => 'simple_video_form', // ID of a registered form.
                                        'preview_text'  => 'label', // ID of a field to use for the preview text.,
                                        'multiple'      => true,
                                    )
                                )
                    )
                )
            ),
    
       
        
    );
        return $fields;
    }
}

\FLBuilder::register_module('m21\Simple_Video_Module', Simple_Video_Module::get_form());

\FLBuilder::register_settings_form('simple_video_form', array(
    'title' => __('Simple Video Form', 'fl-builder'),
    'tabs'  => array(
        'general'      => array(
            'title'         => __('General', 'fl-builder'),
            'sections'      => array(
                'general'       => array(
                    'title'         => '',
                    'fields'        => array(
                        'label'         => array(
                            'type'          => 'text',
                            'label'         => __('Label', 'fl-builder')
                        ),
                        'video_image' => array(
                            'type'          => 'photo',
                            'label'         => __('Photo Field', 'fl-builder'),
                            'show_remove'   => true,
                        ),
                        'video_src' => array(
                            'type'          => 'text',
                            'label'         => __( 'Video Source', 'fl-builder' ),
                            'default'       => '',
                            'maxlength'     => '2000',
                            'size'          => '50',
                            'placeholder'   => __( 'Paste the YouTube embed URL here', 'fl-builder' ),
                            'class'         => 'simple-video-url',
                            'description'   => __( 'Get the embed url, paste it here.', 'fl-builder' ),
                            'help'          => __( 'Get the embed url, paste it here.', 'fl-builder' )
                        ),
                        'video_caption' => array(
                            'type'          => 'textarea',
                            'label'         => __( 'Video Caption', 'fl-builder' ),
                            'default'       => '',
                            'Rows'     => '10',
                            'placeholder'   => __( 'The caption goes here.', 'fl-builder' ),
                            'class'         => 'simple-video-url',
                            'description'   => __( "Paste or type this video's caption above. It will appear in the overlay.", 'fl-builder' ),
                            'help'          => __( "Paste or type this video's caption above. It will appear in the overlay.", 'fl-builder' )
                        ),
                    )
                ),
            )
        )
    )
));
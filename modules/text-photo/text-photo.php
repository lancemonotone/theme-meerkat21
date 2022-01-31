<?php

namespace m21;

/**
 * 1. Update name and description properties below
 * 2. Customize the array of fields for FLBuilder::register_module
 * 3. Run 'webpack' in module directory to build js and css.
 */

// include('lib/class.example.php');

class Text_Photo_Module extends \FLBuilderModule {
    public $name = 'Text on Photo Module';
    public $description = 'This module always text on divs with photo backgrounds, without using BB rows or columns';
    public $enabled = true;

    // You shouldn't need to change anything past this point.
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

        // Use this if needed.
        //$this->add_styles_scripts();
    }

    /**
     * Do not enqueue frontend.js, frontend.css or frontend.responsive.css as that will be enqueued automatically.
     * @method add_styles_scripts()
     */
    public function add_styles_scripts() {
        // Use WP handle for registered scripts.
        // $this->add_js('jquery-wookmark');
        // // Or full url for external scripts.
        // $this->add_js('jquery-somelib', $this->url . 'js/lib/jquery.somelib.js', array('jquery'));

        // $override_lightbox = apply_filters('fl_builder_override_lightbox', false);
        // if ( ! $override_lightbox) {
        //     $this->add_js('jquery-magnificpopup');
        //     $this->add_css('jquery-magnificpopup');
        // } else {
        //     wp_dequeue_script('jquery-magnificpopup');
        //     wp_dequeue_style('jquery-magnificpopup');
        // }
    }

    public static function get_form(){
        $fields = array(
            'my-tab-1' => array(
                'title'    => 'Settings',
                'sections' => array(
                    'my-section-1' => array(
                        'title'  => 'Section 1',
                        'fields' => array(
                            'tp_link' => array(
                                'type'          => 'link',
                                'label'         => __('Link!', 'fl-builder')
                            ),
                            'tp_padding' => array(
                                'type'        => 'dimension',
                                'label'       => 'Padding',
                                'description' => 'px',
                                'responsive'  => true,
                                'default'     => 0,   
                            ),
                            'background_image' => array(
                                'type'          => 'photo',
                                'label'         => __('Background Image', 'fl-builder'),
                                'show_remove'   => true,
                            ),
                            'background_pos_y' => array(
                                'type'   => 'unit',
                                'default' => 50,
                                'label'  => 'Background Position Y',
                                'units'  => array( '%' ),
                                'default_unit' => '%', // Optional
                                'selector'      => '.photo-w-text-module',
                                'slider' => array(
                                    'px'	=> array(
                                        'min'	=> 0,
                                        'max'	=> 100,
                                        'step'	=> 2,
                                    ),
                                ),
                            ),
                            'background_pos_x' => array(
                                'type'   => 'unit',
                                'default' => 50,
                                'label'  => 'Background Position X',
                                'units'  => array(  '%' ),
                                'default_unit' => '%', // Optional
                                'selector'      => '.photo-w-text-module',
                                'slider' => array(
                                    'px'	=> array(
                                        'min'	=> 0,
                                        'max'	=> 100,
                                        'step'	=> 2,
                                    ),
                                ),
                            ),
                            'gradient_overlay' => array(
                                'type'    => 'gradient',
                                'label'   => 'Gradient Overlay',
                                'preview' => array(
                                    'type'     => 'css',
                                    'selector' => '.tp-gradient-overlay',
                                    'property' => 'background-image',
                                ),
                            ),
                            'header_color' => array(
                                'type'          => 'color',
                                'label'         => __( 'Header Color', 'fl-builder' ),
                                'default'       => 'ffffff',
                                'show_reset'    => true,
                                'show_alpha'    => true
                            ),
                            'header_type' => array(
                                'type'       => 'typography',
                                'label'      => 'Header Typography',
                                'responsive' => true,
                                'preview'    => array(
                                    'type'	    => 'css',
                                    'selector'  => '.tp-header',
                                ),
                            ),
                            'tp_header_class' => array(
                                'type'          => 'text',
                                'label'         => __( 'Header Class', 'fl-builder' ),
                                'default'       => '',
                                'maxlength'     => '200',
                                'size'          => '3',
                                'placeholder'   => __( 'Type classes here without a period', 'fl-builder' ),
                                'class'         => 'tp-header-class',
                                'description'   => __( 'This adds a .class to the header, it is not used very often', 'fl-builder' ),
                                'help'          => __( 'This adds a .class to the header, it is not used very often', 'fl-builder' )
                            ),
                            'photo_header_text' => array(
                                'type'          => 'text',
                                'label'         => __( 'Photo Header', 'fl-builder' ),
                                'default'       => '',
                                'placeholder'   => __( 'Put the photo header here', 'fl-builder' ),
                                'maxlength'		=> '255',
                                'rows'          => '6'
                            ),
                            'text_color' => array(
                                'type'          => 'color',
                                'label'         => __( 'Copy color', 'fl-builder' ),
                                'default'       => 'ffffff',
                                'show_reset'    => true,
                                'show_alpha'    => true
                            ),
                            // 'copy_type' => array(
                            //     'type'       => 'typography',
                            //     'label'      => 'Copy Typography',
                            //     'responsive' => true,
                            //     'preview'    => array(
                            //         'type'	    => 'css',
                            //         'selector'  => '.tp-text',
                            //     ),
                            // ),
                            'photo_body_text' => array(
                                'type'          => 'textarea',
                                'label'         => __( 'Copy', 'fl-builder' ),
                                'default'       => '',
                                'placeholder'   => __( 'Put your photo body copy here', 'fl-builder' ),
                                'maxlength'		=> '255',
                                'rows'          => '6'
                            ),
                        )
                    )
                )
            )
        );
        return $fields;
    }

}

\FLBuilder::register_module('m21\Text_Photo_Module', Text_Photo_Module::get_form());

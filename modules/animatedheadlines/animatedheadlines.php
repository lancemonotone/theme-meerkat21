<?php

namespace m21;

/**
 * 1. Update name and description properties below
 * 2. Customize the array of fields for FLBuilder::register_module
 * 3. Run 'webpack' in module directory to build js and css.
 */

class Animatedheadlines_Module extends \FLBuilderModule {
    public $name = 'Animatedheadlines Module';
    public $description = 'This is a sample module.';
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
            //
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
        $this->add_js('jquery-wookmark');
        // Or full url for external scripts.
        $this->add_js('jquery-somelib', $this->url . 'js/lib/jquery.somelib.js', array('jquery'));

        $override_lightbox = apply_filters('fl_builder_override_lightbox', false);
        if ( ! $override_lightbox) {
            $this->add_js('jquery-magnificpopup');
            $this->add_css('jquery-magnificpopup');
        } else {
            wp_dequeue_script('jquery-magnificpopup');
            wp_dequeue_style('jquery-magnificpopup');
        }
    }

    public static function get_form(){
        $fields = array(
            'my-tab-1' => array(
                'title'    => 'Settings',
                'sections' => array(
                    'my-section-1' => array(
                        'title'  => 'Data and Design',
                        'fields' => array(
                            'header_text' => array(
                                'type'    => 'text',
	                            'label' => __( 'Header Text', 'fl-builder' ),
                            ),

                            'url_endpoint' => array(
                                'type'    => 'text',
	                            'label' => __( 'URL', 'fl-builder' ),
                            ),

                            'animation_type' => array(
                                'type'    => 'button-group',
                                'label'   => 'My Setting',
                                'default' => 'rotate-1',
                                'options' => array(
                                    'rotate-1'    => 'Rotate',
                                    'letters type'    => 'Type',
                                    'letters rotate-2'  => 'Rotate 2',
                                    'loading-bar'  => 'Loading Bar',
                                    'slide'  => 'Slide',
                                    'clip is-full-width'  => 'Clip',
                                    'letters rotate-3'  => 'Rotate 3',
                                    'letters scale'  => 'Scale',
                                    'push'  => 'Push',
                                ),
                            ),

                            'max_characters' => array(
                                'type'    => 'text',
	                            'label' => __( 'Character limit?', 'fl-builder' ),
                            ),

                            'data_restriction' => array(
                                'type'    => 'button-group',
                                'label'   => 'Restrict to',
                                'default' => 'all',
                                'options' => array(
                                    'all'    => 'All',
                                    'first_half'    => 'First Half',
                                    'second_half'  => 'Second Half',
                                    'first_third'  => 'First Third',
                                    'second_third'  => 'Second Third',
                                    'third_third'  => 'Third Third',
                                ),
                            ),
                            'header_color_field' => array(
                                'type'          => 'color',
                                'label'         => __( 'Header text color', 'fl-builder' ),
                                'default'       => '000000',
                                'show_reset'    => true,
                                'show_alpha'    => true
                            ),
                            'item_color_field' => array(
                                'type'          => 'color',
                                'label'         => __( "Items' color", 'fl-builder' ),
                                'default'       => '000000',
                                'show_reset'    => true,
                                'show_alpha'    => true
                            ),
                              'link_color' => array(
                                'type'          => 'color',
                                'label'         => __( 'Link Color', 'fl-builder' ),
                                'default'       => '497476',
                                'show_reset'    => true,
                            ),
                            'CTA_text' => array(
                                'type'          => 'text',
                                'label'         => __( 'Link Text', 'fl-builder' ),
                                'default'       => '',
                                'maxlength'     => '250',
                                'size'          => '50',
                                'placeholder'   => __( 'This is the link text.', 'fl-builder' ),
                                'class'         => 'my-css-class',
                                'description'   => __( '', 'fl-builder' ),
                                'help'          => __( 'Add text for this CTA link', 'fl-builder' )
                            ),
                            'CTA_link' => array(
                                'type'          => 'link',
                                'label'         => 'Link',
                            ),
                            'CTA_link_class' => array(
                                'type'          => 'text',
                                'label'         => __( 'Link Class', 'fl-builder' ),
                                'default'       => '',
                                'maxlength'     => '250',
                                'size'          => '50',
                                'placeholder'   => __( 'Enter classes here. This is not used very often.', 'fl-builder' ),
                                'class'         => 'my-css-class',
                                'description'   => __( '', 'fl-builder' ),
                                'help'          => __( 'Add any special classes for this link here.', 'fl-builder' )
                            ),

                        )
                    )
                )
            )
        );
        return $fields;
    }

}

\FLBuilder::register_module('m21\Animatedheadlines_Module', Animatedheadlines_Module::get_form());

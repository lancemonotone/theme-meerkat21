<?php

namespace m21;

/**
 * 1. Update name and description properties below
 * 2. Customize the array of fields for FLBuilder::register_module
 * 3. Run 'webpack' in module directory to build js and css.
 */

class Splash_Page_Module extends \FLBuilderModule {
    public $name = 'Splash_Page Module';
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
                        'title'  => 'Section 1',
                        'fields' => array(

                            'my_select_field' => array(
                                'type'          => 'select',
                                'label'         => __( 'Select Field', 'fl-builder' ),
                                'default'       => 'MP4',
                                'options'       => array(
                                    'mp4'      => __( 'MP4', 'fl-builder' ),
                                    'youtube'      => __( 'YouTube', 'fl-builder' )
                                ),
                                'toggle'        => array(
                                    'mp4'      => array(
                                        'fields'        => array( 'poster_img', 'mp4_url', 'link_text' ),
                                      
                                    ),
                                    'youtube'      => array(
                                        'fields'        => array( 'link_text', 'youtube_code', 'embed_url' ),
                                    )
                                )
                            ),
                            'poster_img' => array(
                                'type'          => 'photo',
                                'label'         => __('Poster Image', 'fl-builder'),
                                'show_remove'   => false,
                            ),
                            'mp4_url' => array(
                                'type'  => 'text',
                                'label' => 'MP4 URL',
                            ),
                             'link_text' => array(
                                'type'  => 'text',
                                'label' => 'Link Text',
                            ),
                            'youtube_code' => array(
                                'type'          => 'textarea',
                                'label'         => __( 'YouTube Embed Code', 'fl-builder' ),
                                'default'       => '',
                                'placeholder'   => __( 'Paste code here', 'fl-builder' ),
                                'maxlength'		=> '255',
                                'rows'          => '6'
                            ),
                              'embed_url' => array(
                                'type'  => 'text',
                                'label' => 'Embed URL',
                                'placeholder' => '?enablejsapi=1 must be included'
                            ),

                            
                        

                          
                        )
                    )
                )
            )
        );
        return $fields;
    }

}

\FLBuilder::register_module('m21\Splash_Page_Module', Splash_Page_Module::get_form());

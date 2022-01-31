<?php

namespace m21;

/**
 * 1. Update name and description properties below
 * 2. Customize the array of fields for FLBuilder::register_module
 * 3. Run 'webpack' in module directory to build js and css.
 */

class Qa_Module extends \FLBuilderModule {
    public $name = 'Question and Answer Module';
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
        // // Use WP handle for registered scripts.
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

    public static function get_form() {
        $fields = array(
            'my-tab-1' => array(
                'title'    => 'Settings',
                'sections' => array(
                    'my-section-1' => array(
                        'title'  => 'Questions and Answers',
                        'fields' => array(
                            'qa_form' => array(
                                'type'         => 'form',
                                'label'        => __('Question', 'fl-builder'),
                                'form'         => 'qa_form', // ID of a registered form.
                                'preview_text' => 'qa_question', // ID of a field to use for the preview text.,
                                'multiple'     => true,
                            )
                        )
                    )
                )
            )
        );

        return $fields;
    }

}

\FLBuilder::register_module('m21\Qa_Module', Qa_Module::get_form());

\FLBuilder::register_settings_form('qa_form', array(
    'title' => __('Single Question Form', 'fl-builder'),
    'tabs'  => array(
        'general' => array(
            'title'    => __('General', 'fl-builder'),
            'sections' => array(
                'general' => array(
                    'title'  => '',
                    'fields' => array(
                        'qa_question' => array(
                            'type'        => 'text',
                            'label'       => __('The Question', 'fl-builder'),
                            'default'     => '',
                            'maxlength'   => '250',
                            'size'        => '50',
                            'placeholder' => __('This is the Question. It can be blank.', 'fl-builder'),
                            'class'       => 'question-class',
                            'description' => __('', 'fl-builder'),
                            'help'        => __('Add text for this CTA link', 'fl-builder')
                        ),
                        'a_form'      => array(
                            'type'         => 'form',
                            'label'        => __('Answers', 'fl-builder'),
                            'form'         => 'a_form', // ID of a registered form.
                            'preview_text' => 'aname', // ID of a field to use for the preview text.,
                            'multiple'     => true,
                        ),
                    )
                ),
            )
        )
    )
));
\FLBuilder::register_settings_form('a_form', array(
    'title' => __('Answer Form', 'fl-builder'),
    'tabs'  => array(
        'general' => array(
            'title'    => __('General', 'fl-builder'),
            'sections' => array(
                'general' => array(
                    'title'  => '',
                    'fields' => array(

                        'aname'  => array(
                            'type'  => 'text',
                            'label' => 'Name',
                        ),
                        'a_text' => array(
                            'type'        => 'textarea',
                            'label'       => __('Answer', 'fl-builder'),
                            'default'     => '',
                            'maxlength'   => '50000',
                            'size'        => '500',
                            'placeholder' => __('Put answer here.', 'fl-builder'),
                            'class'       => 'my-css-class',
                            'description' => __('', 'fl-builder'),
                            'help'        => __('Put answers here.', 'fl-builder')
                        ),
                           'pulltext' => array(
                            'type'        => 'textarea',
                            'label'       => __('Aside', 'fl-builder'),
                            'default'     => '',
                            'maxlength'   => '50000',
                            'size'        => '500',
                            'placeholder' => __('Put inset quote here.', 'fl-builder'),
                            'class'       => 'my-css-class',
                            'description' => __('', 'fl-builder'),
                            'help'        => __('Put inset quotes here.', 'fl-builder')
                        ),
                    )
                ),
            )
        )
    )
));

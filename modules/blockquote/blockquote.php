<?php

namespace m21;

class Blockquote_Module extends \FLBuilderModule {
    public $name = 'Blockquote';
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

    }
}

\FLBuilder::register_module('m21\Blockquote_Module',
    array(
        'my-tab-1' => array(
            'title'    => 'Tab 1',
            'sections' => array(
                'my-section-1' => array(
                    'title'  => 'Section 1',
                    'fields' => array(
                        'bb_quote' => array(
                            'type'          => 'textarea',
                            'label'         => __( 'The Quote', 'fl-builder' ),
                            'default'       => '',
                            'placeholder'   => __( 'Type a quote here', 'fl-builder' ),
                            'rows'          => '6'
                        ),
                        'bb_quote_source' => array(
                            'type'  => 'text',
                            'label' => 'Source',
                        ),
                        'bb_quote_citation' => array(
                            'type'  => 'text',
                            'label' => 'Citation',
                        ),
                        'bb_quote_link' => array(
                            'type'          => 'link',
                            'label'         => 'Link',
                            'show_target'	=> true,
                            'show_nofollow'	=> true,
                        ),
                    )
                )
            )
        )
    )
);

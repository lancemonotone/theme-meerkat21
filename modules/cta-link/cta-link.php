<?php

namespace m21;

class CTA_Links_Module extends \FLBuilderModule {
    public $name = 'CTA Links';
    public $description = 'This module build the CTA links menus with header and x number of CTAs';
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
            'icon'            => 'arrow-right.svg',
            'editor_export'   => true, // Defaults to true and can be omitted.
            'enabled'         => $this->enabled, // Defaults to true and can be omitted.
            'partial_refresh' => false, // Defaults to false and can be omitted.
        ));

    }
}

\FLBuilder::register_module('m21\CTA_Links_Module',
    array(
        'General' => array(
            'title'    => 'General',
            'sections' => array(
                'my-section-1' => array(
                    'title'  => 'Section 1',
                    'fields' => array(
                        'menu_header' => array(
                            'type'  => 'text',
                            'label' => 'Menu Header',
                        ),
                        'header_color' => array(
                            'type'          => 'color',
                            'label'         => __( 'Header Color', 'fl-builder' ),
                            'default'       => '000000',
                            'show_reset'    => true,
                        ),
                        'link_color' => array(
                            'type'          => 'color',
                            'label'         => __( 'Link Color', 'fl-builder' ),
                            'default'       => '497476',
                            'show_reset'    => true,
                        ),
                        'cta_links_form' => array(
                            'type'          => 'form',
                            'label'         => __('CTA Link', 'fl-builder'),
                            'form'          => 'cta_links_form', // ID of a registered form.
                            'preview_text'  => 'CTA_text', // ID of a field to use for the preview text.,
                            'multiple'      => true,
                        )
                    )
                )
            )
        )
    )
);


\FLBuilder::register_settings_form('cta_links_form', array(
'title' => __('CTA Links Form', 'fl-builder'),
'tabs'  => array(
    'general'      => array(
        'title'         => __('General', 'fl-builder'),
        'sections'      => array(
            'general'       => array(
                'title'         => '',
                'fields'        => array(
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
            ),
        )
    )
)
));





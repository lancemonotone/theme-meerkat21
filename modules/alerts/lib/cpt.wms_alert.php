<?php
register_post_type(
    'wms_alert', array(
        'label'               => 'Alert',
        'description'         => '',
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'capability_type'     => 'post',
        'hierarchical'        => true,
        'rewrite'             => array('slug' => ''),
        'query_var'           => true,
        'exclude_from_search' => false,
        'menu_position'       => 1,
        'supports'            => array(
            'title',
            'editor',
        ),
        'labels'              => array(
            'name'                     => 'Alert',
            'singular_name'            => 'Alert',
            'menu_name'                => 'Alerts',
            'add_new'                  => 'Add Alert',
            'add_new_item'             => 'Add New Alert',
            'edit'                     => 'Edit',
            'edit_item'                => 'Edit Alert',
            'new_item'                 => 'New Alert',
            'view'                     => 'View Alert',
            'view_item'                => 'View Alert',
            'search_items'             => 'Search Alert',
            'not_found'                => 'No Alert Found',
            'not_found_in_trash'       => 'No Alert Found in Trash',
            'parent'                   => 'Parent Alert',
            'item_published'           => 'Alert published',
            'item_published_privately' => 'Alert published privately',
            'item_reverted_to_draft'   => 'Alert reverted to draft',
            'item_scheduled'           => 'Alert scheduled',
            'item_updated'             => 'Alert updated',
        ),
    )
);

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
        'key'      => 'group_wms_alert',
        'title'    => 'Page Options',
        'fields'   => array(
            array(
                'key'           => 'field_4feccb8bbde05',
                'label'         => 'Hide sidebar',
                'name'          => 'hide_sidebar',
                'type'          => 'true_false',
                'instructions'  => '',
                'required'      => '0',
                'message'       => '',
                'order_no'      => '5',
                'default_value' => 'true',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'wms_alert',
                ),
            ),
        ),
    ));
}
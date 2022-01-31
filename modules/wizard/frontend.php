<?php

/**
 * This file should be used to render each module instance.
 * You have access to two variables in this file:
 *
 * $module An instance of your module class.
 * $settings The module's settings.
 *
 */

$menu = wp_get_nav_menu_items($settings->wizard_menu);

$topItems = array();
$childItems = array();
$terminalItems = array();
$titlesByID = array();
foreach ( $menu as $item ) {
	$item->rendered_title = $item->post_title ? $item->post_title : $item->title;
	$titlesByID[$item->ID] = $item->rendered_title;
    if ($item->type == 'post_type') {
        $terminalItems[] = $item;
	}
    if ($item->menu_item_parent == 0) {
		$firstItems[] = $item;
	} else {
	    $childItems[$item->menu_item_parent]['parent_title'] = $titlesByID[$item->menu_item_parent];
	    $childItems[$item->menu_item_parent]['items'][] = $item;
	}
}

$context = array_merge(
    \Timber\Timber::get_context(),
        array( 
		    'firstItems' => $firstItems, 
		    'childItems' => $childItems, 
		    'terminalItems' => $terminalItems,
		    'settings' => $settings,
		)
);

if ( ! isset($settings->wizard_title) || $settings->wizard_title == '' ) 
	// BB does not have a "required" option for fields. Poor man's validation?
	echo '<h2>Wizard Title is a required field</h2>';
else
    \Timber\Timber::render('wizard.twig', $context);


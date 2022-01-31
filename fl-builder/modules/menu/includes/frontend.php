<?php

$menu_classes = 'fl-menu';
$wms_sticky   = $settings->wms_sticky;

if ($settings->collapse) {
    $menu_classes .= ' fl-menu-accordion-collapse';
}
if ($settings->mobile_breakpoint && 'expanded' != $settings->mobile_toggle) {
    $menu_classes .= ' fl-menu-responsive-toggle-' . $settings->mobile_breakpoint;
}
if ( ! empty($settings->menu)) {

if (isset($settings->menu_layout)) {
    if (in_array($settings->menu_layout, array('vertical', 'horizontal')) && isset($settings->submenu_hover_toggle)) {
        $toggle = ' fl-toggle-' . $settings->submenu_hover_toggle;
    } elseif ('accordion' == $settings->menu_layout && isset($settings->submenu_click_toggle)) {
        $toggle = ' fl-toggle-' . $settings->submenu_click_toggle;
    } else {
        $toggle = ' fl-toggle-arrows';
    }
} else {
    $toggle = ' fl-toggle-arrows';
}

$layout = isset($settings->menu_layout) ? 'fl-menu-' . $settings->menu_layout : 'fl-menu-horizontal';

$defaults = array(
    'menu'       => $settings->menu,
    'container'  => false,
    'menu_class' => 'menu ' . $layout . $toggle,
    'walker'     => new FL_Menu_Module_Walker(),
);
?>
<div <?php if ($wms_sticky) echo 'id= "heads-up"'; ?> class="<?php echo $menu_classes; ?>">
    <?php
    add_filter('wp_nav_menu_objects', 'FLMenuModule::sort_nav_objects', 10, 2);

    $menu = new \Timber\Menu($settings->menu);
    \Timber\Timber::render('modules/navigation.twig', array(
        'nav_obj'        => $menu,
        'menu_name'      => $menu->name,
        'menu_id'        => $menu->slug,
        'menu_class'     => 'menu ' . $layout . $toggle,
        'toggle'         => true,
        'title'          => __('Menu'),
        'load_collapsed' => true
    ));
    ?>

    <div class="fl-clear"></div>
    <?php
    remove_filter('wp_nav_menu_objects', 'FLMenuModule::sort_nav_objects');
    }
    ?>
</div>

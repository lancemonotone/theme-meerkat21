
.photo-w-text-module-<?php echo $id; ?>{
    background-position-x: <?php echo  $module->settings->background_pos_x; ?>%;
    background-position-y: <?php echo  $module->settings->background_pos_y; ?>%;

}
.tp-gradient-overlay-<?php echo $id; ?>{
    background-image: <?php echo \FLBuilderColor::gradient( $module->settings->gradient_overlay );?>;
    <?php if ($module->settings->tp_padding_top): ?> 
        padding: <?php echo $module->settings->tp_padding_top . "px " . $module->settings->tp_padding_right . "px " . $module->settings->tp_padding_bottom . "px " . $module->settings->tp_padding_left . "px "; ?>;
    <?php endif; ?>    
}
h2.tp-header-<?php echo $id; ?>{
    color: <?php echo  $module->settings->header_color; ?>;
    pointer-events: none;
}
p.tp-text-<?php echo $id; ?>{
    color: <?php echo  $module->settings->text_color; ?>;
    pointer-events: none;
}

<?php

\FLBuilderCSS::typography_field_rule( array(
	'settings'	=> $settings,
	'setting_name' 	=> 'header_type',
	'selector' 	=> ".fl-node-$id .tp-header-$id",
) );

//copy type
// FLBuilderCSS::typography_field_rule( array(
// 	'settings'	=> $settings,
// 	'setting_name' 	=> 'copy_type',
// 	'selector' 	=> ".fl-node-$id .tp-text-$id",
// ) );
<?php

namespace m21;

///add the loop for BB
the_post();

//left/top sidebar 
$hide_site_nav =   get_field('hide_site_nav') //page level
               ||  get_field('remove_site_nav', 'option'); //site wide

//right/bottom sidebar
$hide_sidebar =    get_field('hide_sidebar') //page level
               ||  get_field('remove_sidebar', 'option'); //site wide   

//$hide_site_nav = true; ///temp turn off left sidebar everywhere
               
$fullwidth = $hide_site_nav && $hide_sidebar;

Timberizer::render_template( array(
     'hide_site_nav'     => $hide_site_nav,
     'hide_sidebar'      => $hide_sidebar,
     'fullwidth'         => $fullwidth,
) );

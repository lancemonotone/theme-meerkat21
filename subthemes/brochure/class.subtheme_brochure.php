<?php

namespace m21;

class Subtheme_Brochure {
    public function __construct() {
        //dequeue searchui on admission home
        if (get_current_blog_id() === 26 && is_front_page()) {
            add_action('wp_print_scripts', function(){
                wp_dequeue_script('theme_uisearch');
            }, 100);
        }
    }
}

new Subtheme_Brochure();
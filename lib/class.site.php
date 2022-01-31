<?php

namespace m21;

class Site {
    public function __construct(){
        add_filter('acf/load_field/name=parent_site', ['m21\Acf_Options', 'get_sites_select_choices']);
    }

}

new Site();

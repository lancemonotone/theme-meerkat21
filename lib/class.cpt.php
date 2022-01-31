<?php

namespace m21;

class CPT {
    public function __construct() {
        add_action('init', array(&$this, 'register_cpt'));
    }

    function register_cpt() {
        $cpts = glob(THEME_DIR . '/cpt/*');
        foreach ($cpts as $cpt) {
            require_once($cpt);
        }
    }
}

new CPT();
<?php

namespace m21;

/**
 * Careers, Admission, Finaid
 * Script to enable vendor (https://technolutions.com/solutions/slate) to track pageviews
 */
class Slate {
    public function __construct() {
        add_action('wp_enqueue_scripts', [&$this, 'enqueue_scripts']);
    }

    public function enqueue_scripts() {
        global $blog_id;
        if (in_array($blog_id, array(2, 26, 129))) {
            Js::do_load('myadmission_tracking', array(
                'src'  => 'https://myadmission.williams.edu/ping'
            ));
        }
    }
}

new Slate();
<?php

namespace m21;

class Tablesorter {

    public function __construct() {
        add_action('wp_enqueue_scripts', [&$this, 'enqueue_scripts']);
    }

    public function enqueue_scripts() {
        Js::do_load('tablesorter', [
            'src'    => [
                'tablesorter' => WMS_LIB_URL . '/assets/js/vendor/jquery.tablesorter/jquery.tablesorter.min.js',
                'tablesorter.widgets' => WMS_LIB_URL . '/assets/js/vendor/jquery.tablesorter/addons/jquery.tablesorter.widgets.min.js',
                'tablesorter.filter'   => WMS_LIB_URL . '/assets/js/vendor/jquery.tablesorter/addons/filter/jquery.filter.min.js',
            ],
            'styles' => array(
                array(
                    'handle' => 'tablesorter-filter-style',
                    'src'    => WMS_LIB_URL . '/assets/js/vendor/jquery.tablesorter/addons/filter/jquery.filter.min.css'
                )
            ),
            'deps'   => array('jquery'),
            'inline' => function() {
                return file_get_contents(__DIR__ . '/assets/tablesorter.js');
            }
        ]);
    }
}

new Tablesorter();
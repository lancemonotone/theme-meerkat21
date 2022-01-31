<?php

namespace m21;

class Subtheme_Welcome {
    public function __construct() {
        add_action('wp_head', array(&$this, 'load_fonts'));
        add_filter('fl_builder_color_presets', array(&$this, 'add_color_presets'));
    }

    function load_fonts() {
        echo <<< EOD
        <!-- @todo make it so these only load on their respective sites -->
        <!-- //welcome site fonts -->
        <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Source+Code+Pro&display=swap" rel="stylesheet">
        <!-- //graduation 2020 fonts -- FF Providence loaded via typekit, Amatic dropped from Typekit, Knockout coming from typography.com -->
        <link rel="stylesheet" href="https://use.typekit.net/jtc0gev.css">
        <link rel="stylesheet" type="text/css" href="https://cloud.typography.com/7265312/7501612/css/fonts.css">

EOD;
    }

    function add_color_presets($colors) {
        $colors[] = '9933FF'; // bright pink
        $colors[] = '9966CC'; // muted pink
        $colors[] = '330033'; // eggplant
        $colors[] = '660099'; // another purple

        return $colors;
    }
}

new Subtheme_Welcome();
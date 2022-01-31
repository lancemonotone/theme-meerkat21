<?php

namespace m21;

class Subtheme_Magazine {
    public function __construct() {
        include('/lib/magazine.php');
    }
}

new Subtheme_Magazine();
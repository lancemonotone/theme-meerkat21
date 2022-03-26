<?php

namespace m21;

class Subtheme_Magazine {
    public function __construct() {
        include('./lib/class.magazine.php');
    }
}

new Subtheme_Magazine();

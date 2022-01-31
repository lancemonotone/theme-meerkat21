<?php

namespace m21;

/**
 * Class Random_Header
 * @package m21
 *
 * Configures random header image
 */
class Random_Header {
    public function __construct() {
       $this->random_header();
    }
    //load fields without assets    
    function random_header(){
        \FLPageData::add_post_property( 'rand_img', array(
            'label'   => 'Random Customizer Header Image',
            'group'   => 'advanced',
            'type'    => 'photo',
            'getter'  => array(&$this, 'rand_img_getter'),
        ) );
    }
    //return  header image as setup in the customizer
    function rand_img_getter() {
        return get_header_image();
    }
}
// RandomHeader::instance()->random_header(); 
new Random_Header();
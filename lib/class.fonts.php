<?php

namespace m21;

/**
 * edits/additions to the BB UI
 *
 * @since 1.0
 */
class Fonts {
    
    public function __construct() {
        //add Eph family to BB
        //Add to Beaver Builder Theme Customizer
        add_filter('fl_theme_system_fonts', __CLASS__ . '::add_custom_fonts');
        //Add to Page Builder modules
        add_filter('fl_builder_font_families_system', __CLASS__ . '::add_custom_fonts');
        //reduce available google fonts
        add_filter('fl_builder_font_families_google', __CLASS__ . '::add_google_fonts', 100);
    }

    /**
     *  add Eph family to BB and remove other system fonts
     *
     * @since 1.0
     * @return new system font array with only typekit
     */
    static public function add_custom_fonts($system_fonts) {
          $new_fonts['EphSerif']   = array(
            'fallback' => 'serif',
            'weights'  => array(
                '300', '400', '700'
            ),
        );
         $new_fonts['EphGothic']   = array(
            'fallback' => 'serif',
            'weights'  => array(
                '300', '400', '700'
            ),
        );
         $new_fonts['EphSlab']   = array(
            'fallback' => 'serif',
            'weights'  => array(
                '300', '400', '700'
            ),
        );

        return $new_fonts;
    }

    /**
     *  Reduce list of google fonts and add back valid ones--in plugin fonts.json
     *
     * @since 1.0
     * @return new google font array
     */
    static public function add_google_fonts($google_fonts) {
        $new_fonts = [];
        // add any Google fonts that you want like this:
        // $new_fonts['IBM Plex Sans'] = ["100", "100italic", "200", "200italic", "300", "300italic", "regular", "italic", "500", "500italic", "600", "600italic", "700", "700italic"];

        return $new_fonts;
    }
}

new Fonts();
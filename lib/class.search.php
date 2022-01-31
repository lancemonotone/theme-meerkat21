<?php

namespace m21;

/*
* Handles search queries, and builds directories (pulling from flexiform database)
*/

class Search {
    public function __construct() {
        // use native search for site search
        add_shortcode('wp_search', [$this, 'use_wp_search_shortcode']);
    }

    /**
     * Display native wp search with reasonable defaults
     */
    function use_wp_search_shortcode($atts) {
        $atts = shortcode_atts(array(
            'echo'       => false,
            'aria_label' => 'search ' . get_bloginfo('name'),
        ), $atts);

        return get_search_form(array(
            'echo'       => $atts['echo'],
            'aria_label' => $atts['aria_label']
        ));
    }

	public static function isWmsSearch() {
		if( isset($_GET[ 'q' ]) || isset($_GET[ 'dt' ]) || ( preg_match( '/\/(search|a-z|people|office-directory)(\/|\/\?.*)?$/', $_SERVER[ 'REQUEST_URI' ] ) && ( substr( $_SERVER[ 'SERVER_NAME' ], 0, 3 ) == 'www' ) ) ) {
			return true;
		} else {
			return false;
		}
	}

	public static function getSearchContext() {
		// tab order: Search, People, A-Z, Offices
		preg_match( '/\/(search|a-z|people|office-directory)(\/|\/\?.*)?$/', $_SERVER[ 'REQUEST_URI' ], $matches );
		switch ($matches[1]) {
			case 'search':
				$open = 1;
				break;
			case 'people':
				$open = 2;
				break;
			case 'a-z':
				$open = 3;
				break;
			case 'office-directory':
				$open = 4;
				break;
		}
		$people = do_shortcode( '[wmsdirindex]' );
		return array(
			'open' => $open,
			'searchstring' => $_GET['q'],
			'tab' => 'search',
			'people' => $people,
			'az' => wms_a_z_index(),
			'offices' => wms_dept_office_directory()
		);
	}
}
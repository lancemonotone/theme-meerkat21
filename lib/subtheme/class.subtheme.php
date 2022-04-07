<?php

namespace m21;

use WP_Customize_Manager;

class Subtheme {
    public $subtheme_option_name = 'subtheme_option_name';
    public $subtheme_dir = THEME_DIR . '/subthemes/';
    public $subtheme_url = THEME_URL . '/subthemes/';
    public $theme;
    public $all_subthemes;
    private $used_subthemes;

    public function __construct() {
        add_action( 'after_setup_theme', [$this, 'init'], 15 );
        add_action( 'customize_register', [$this, 'register_subthemes'], 100 );
        add_filter( 'body_class', [$this, 'add_body_class'] );

        // Subthemes can add their own css and js
        add_action( 'wp_enqueue_scripts', [$this, 'enqueue_assets'] );

        if( is_super_admin() ) {
            add_action( 'admin_menu', [$this, 'add_options_page'] );
        }
    }

    function init() {
        $this->theme = wp_get_theme();
        $this->all_subthemes = $this->get_all_subthemes();
        $this->load_subtheme();
    }

    function get_subtheme() {
        return get_theme_mod( $this->subtheme_option_name );
    }

    function get_path() {
        return $this->subtheme_dir . $this->get_subtheme();
    }

    function get_url() {
        return $this->subtheme_url . $this->get_subtheme();
    }

    /**
     * Don't load non-existent 'default' subtheme assets
     *
     * @return bool
     */
    function using_subtheme(){
        return $this->get_subtheme() !== 'default';
    }

    function add_body_class($classes) {
        if( $subtheme = get_theme_mod( $this->subtheme_option_name ) ) {
            $classes[] = 'subtheme-' . $subtheme;
        }

        return $classes;
    }

    /**
     * Registers subtheme options in WP theme customizer
     *
     * @param WP_Customize_Manager $customizer
     */
    function register_subthemes(WP_Customize_Manager $customizer) {
        // new panel for subtheme
        $customizer->add_section( 'subtheme',
            array(
                'title'    => 'Subtheme',
                'priority' => 1,
            )
        );

        // subtheme radio buttons
        $customizer->add_setting( $this->subtheme_option_name, array(
            'default'  => 'default',
            'type'     => 'theme_mod',
            'priority' => 1,
        ) );

        // radio button choices
        $customizer->add_control( $this->subtheme_option_name, array(
            'type'     => 'radio',
            'label'    => 'Select a subtheme:',
            'section'  => 'subtheme',
            'priority' => 20,
            'choices'  => $this->all_subthemes,
        ) );
    }

    /**
     * Load functions and assets for current subtheme.
     */
    function load_subtheme() {
        if( $this->using_subtheme() ) {
            $functions = $this->get_path() . '/class.subtheme_' . $this->subtheme_name . '.php';

            // Subthemes can have function files like regular themes
            if( file_exists( $functions ) ) {
                include($functions);
            }
        }
    }

    function enqueue_assets() {
        if( $this->using_subtheme() ) {
            $deps = include $this->get_path() . '/assets/build/frontend.asset.php';
            $handle = __NAMESPACE__ . '-subtheme-' . $this->get_subtheme();

            $js_url = $this->get_url() . '/assets/build/frontend.js';
            wp_enqueue_script( $handle . '-js', $js_url, $deps['dependencies'], $deps['version'], true );

            $css_url = $this->get_url() . '/assets/build/frontend.css';
            wp_enqueue_style( $handle . '-css', $css_url, null, $deps['version'] );
        }
    }

    /**
     * Get available subthemes by directory name.
     *
     * @return array
     */
    function get_all_subthemes() {
        $subthemes = ['default' => 'Default'];
        $subtheme_dirs = glob( $this->subtheme_dir . '*', GLOB_ONLYDIR );
        foreach( $subtheme_dirs as $subtheme ) {
            // handle
            $handle = substr( $subtheme, strripos( $subtheme, '/' ) + 1 );
            // put it all together now
            $subthemes[$handle] = ucwords( str_replace( '-', ' ', $handle ) );
        }

        return $subthemes;
    }

    /**
     * Add WP Admin subtheme options page
     *
     * @return void
     */
    function add_options_page(): void {
        //Create the admin menu page
        add_menu_page(
            __( 'Subtheme Usage Stats' ),
            __( 'Subthemes' ),
            'manage_network',
            __NAMESPACE__ . '_subthemes',
            [$this, 'generate_options_page'],
            'dashicons-open-folder',
            60
        );

        wp_enqueue_script( 'jquery' );
    }

    /**
     * Generate WP Admin options page for subtheme management.
     *
     * @return void
     */
    public function generate_options_page(): void {
        $this->used_subthemes = $this->get_used_subthemes();
        $unused_subthemes = $this->get_unused_subthemes();

        \Timber\Timber::render( 'views/subtheme-table.twig', array(
            'css'              => file_get_contents( __DIR__ . '/assets/css/admin.css' ),
            'subtheme'         => $this->get_subtheme(),
            'this_theme'       => $this->theme->get( 'Name' ),
            'used_subthemes'   => $this->used_subthemes,
            'unused_subthemes' => $unused_subthemes
        ) );
    }

    /**
     * Loop through all sites and return sites in current
     * theme with its subtheme, if applicable.
     *
     * @return array
     */
    function get_used_subthemes(): array {
        global $wpdb, $current_site;

        $blogs = $wpdb->get_results( "SELECT blog_id, domain, path FROM " . $wpdb->blogs . " WHERE site_id = {$current_site->id} ORDER BY domain ASC" );
        $subthemes = array();
        $processed = array();
        if( $blogs ) {
            foreach( $blogs as $blog ) {
                switch_to_blog( $blog->blog_id );

                // Only get subthemes of the current theme.
                if( __NAMESPACE__ !== wp_get_theme()->stylesheet ) continue;

                $subtheme = ucwords( str_replace( '_', ' ', get_theme_mod( $this->subtheme_option_name ) ) );

                if( $blog->path === '/' ) {
                    $blogurl = $blog->domain;
                } else {
                    $blogurl = trailingslashit( $blog->domain . $blog->path );
                }

                if( empty( $subtheme ) && ! $processed[$subtheme] ) {
                    // If $subtheme is empty, it's Default
                    $subthemes['Default'][0] = array(
                        'blogid'  => $blog->blog_id,
                        'name'    => get_bloginfo( 'name' ),
                        'blogurl' => $blogurl
                    );
                    $processed['Default'] = true;
                } else if( ! empty( $subtheme ) && ! $processed[$subtheme] ) {
                    $subthemes[$subtheme][0] = array(
                        'blogid'  => $blog->blog_id,
                        'name'    => get_bloginfo( 'name' ),
                        'blogurl' => $blogurl
                    );
                    $processed[$subtheme] = true;
                } else {
                    //get the size of the current array of blogs
                    $count = count( $subthemes[$subtheme] );
                    $subthemes[$subtheme][$count] = array(
                        'blogid'  => $blog->blog_id,
                        'name'    => get_bloginfo( 'name' ),
                        'blogurl' => $blogurl
                    );
                }
                restore_current_blog();
            }
        }

        ksort( $subthemes );

        return $subthemes;
    }

    /**
     * @param array $used_subthemes
     *
     * @return array
     */
    public function get_unused_subthemes(): array {
        $unused_subthemes = array();
        foreach( $this->all_subthemes as $subtheme ) {
            if( ! array_key_exists( $subtheme, $this->used_subthemes ) ) {
                array_push( $unused_subthemes, $subtheme );
            }
        }

        return $unused_subthemes;
    }
}

new Subtheme();

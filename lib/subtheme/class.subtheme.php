<?php

namespace m21;

use WP_Customize_Manager;

class Subtheme {
    public $subtheme_option_name = 'subtheme_option_name';
    public $subtheme_dir = THEME_DIR . '/subthemes/';
    public $theme;
    public $subtheme;
    public $all_subthemes;
    private $used_subthemes;

    public function __construct() {
        $this->theme         = wp_get_theme();
        $this->all_subthemes = $this->get_all_subthemes();
        add_action('after_setup_theme', [$this, 'init']);
        add_action('customize_register', [$this, 'register_subthemes'], 100);
        add_filter('body_class', [$this, 'add_body_class']);

        if (is_super_admin()) {
            add_action('admin_menu', [$this, 'add_options_page']);
        }
    }

    function init() {
        $this->subtheme = get_theme_mod($this->subtheme_option_name);
        $this->load_subtheme();
    }

    /**
     * Registers subtheme options in WP theme customizer
     *
     * @param WP_Customize_Manager $customizer
     */
    function register_subthemes(WP_Customize_Manager $customizer) {
        // new panel for subtheme
        $customizer->add_section('subtheme',
            array(
                'title'    => 'Subtheme',
                'priority' => 1,
            )
        );

        // subtheme radio buttons
        $customizer->add_setting($this->subtheme_option_name, array(
            'default'  => 'default',
            'type'     => 'theme_mod',
            'priority' => 1,
        ));

        // radio button choices
        $customizer->add_control($this->subtheme_option_name, array(
            'type'    => 'radio',
            'label'   => 'Select a subtheme:',
            'section' => 'subtheme',
            'choices' => $this->all_subthemes,
        ));
    }

    /**
     * Load functions and assets for current subtheme.
     */
    function load_subtheme() {
        $subtheme = $this->subtheme;
        $path     = $this->subtheme_dir . $this->subtheme;
        // @todo this is a dumb way to do this. Don't hardcode 'class.subtheme'. Make it smarter.
        $functions = $path . '/class.subtheme_' . $subtheme . '.php';

        // Subthemes can have function files like regular themes
        if (file_exists($functions)) {
            include($functions);
        }

        // Subthemes can add their own css and js
        add_action('wp_enqueue_scripts', function() use ($path) {
            if (file_exists($style = $path . '/assets/css/frontend.css')) {
                wp_enqueue_style($this->subtheme, $style, array(), '');
            }
            if (file_exists($app = $path . '/assets/js/frontend.js')) {
                wp_enqueue_script($this->subtheme, $app, array(), '', true);
            }
        });
    }

    /**
     * Get available subthemes by directory name.
     *
     * @return array
     */
    function get_all_subthemes() {
        $subthemes     = ['default' => 'Default'];
        $subtheme_dirs = glob($this->subtheme_dir . '*', GLOB_ONLYDIR);
        foreach ($subtheme_dirs as $subtheme) {
            // handle
            $handle = substr($subtheme, strripos($subtheme, '/') + 1);
            // put it all together now
            $subthemes[ $handle ] = ucwords(str_replace('-', ' ', $handle));
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
        add_submenu_page('themes.php',
            __('Subtheme Usage Stats'),
            __('Subthemes'),
            'manage_network',
            __NAMESPACE__ . '_subthemes',
            [$this, 'generate_options_page']
        );

        wp_enqueue_script('jquery');
    }

    /**
     * Generate WP Admin options page for subtheme management.
     *
     * @return void
     */
    public function generate_options_page(): void {
        $this->used_subthemes = $this->get_used_subthemes();
        $unused_subthemes     = $this->get_unused_subthemes();

        \Timber\Timber::render('views/subtheme-table.twig', array(
            'css'              => file_get_contents(__DIR__ . '/assets/css/admin.css'),
            'subtheme'         => $this->subtheme,
            'this_theme'       => $this->theme->get('Name'),
            'used_subthemes'   => $this->used_subthemes,
            'unused_subthemes' => $unused_subthemes
        ));
    }

    /**
     * Loop through all sites and return sites in current
     * theme with its subtheme, if applicable.
     *
     * @return array
     */
    function get_used_subthemes(): array {
        global $wpdb, $current_site;

        $blogs     = $wpdb->get_results("SELECT blog_id, domain, path FROM " . $wpdb->blogs . " WHERE site_id = {$current_site->id} ORDER BY domain ASC");
        $subthemes = array();
        $processed = array();
        if ($blogs) {
            foreach ($blogs as $blog) {
                switch_to_blog($blog->blog_id);

                // Only get subthemes of the current theme.
                if (__NAMESPACE__ !== wp_get_theme()->stylesheet) continue;

                $subtheme = ucwords(str_replace('_', ' ', get_theme_mod($this->subtheme_option_name)));

                if ($blog->path === '/') {
                    $blogurl = $blog->domain;
                } else {
                    $blogurl = trailingslashit($blog->domain . $blog->path);
                }

                if (empty($subtheme) && ! $processed[ $subtheme ]) {
                    // If $subtheme is empty, it's Default
                    $subthemes['Default'][0] = array('blogid'  => $blog->blog_id,
                                                     'name'    => get_bloginfo('name'),
                                                     'blogurl' => $blogurl);
                    $processed['Default']    = true;
                } else if ( ! empty($subtheme) && ! $processed[ $subtheme ]) {
                    $subthemes[ $subtheme ][0] = array('blogid'  => $blog->blog_id,
                                                       'name'    => get_bloginfo('name'),
                                                       'blogurl' => $blogurl);
                    $processed[ $subtheme ]    = true;
                } else {
                    //get the size of the current array of blogs
                    $count                            = count($subthemes[ $subtheme ]);
                    $subthemes[ $subtheme ][ $count ] = array('blogid'  => $blog->blog_id,/* 'path' => $path, 'domain' => $domain,*/
                                                              'name'    => get_bloginfo('name'),
                                                              'blogurl' => $blogurl);
                }
                restore_current_blog();
            }
        }

        ksort($subthemes);

        return $subthemes;
    }

    /**
     * @param array $used_subthemes
     *
     * @return array
     */
    public function get_unused_subthemes(): array {
        $unused_subthemes = array();
        foreach ($this->all_subthemes as $subtheme) {
            if ( ! array_key_exists($subtheme, $this->used_subthemes)) {
                array_push($unused_subthemes, $subtheme);
            }
        }

        return $unused_subthemes;
    }

    function add_body_class($classes) {
        if ($subtheme = get_theme_mod($this->subtheme_option_name)) {
            $classes[] = 'subtheme-' . $subtheme;
        }

        return $classes;
    }
}

new Subtheme();
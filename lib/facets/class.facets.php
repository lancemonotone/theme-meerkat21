<?php

namespace m21;

class Facets {
    private static $instance;

    protected function __construct() {
        add_action('init', [$this, 'load_lib']);
        add_filter('facetwp_facets', [$this, 'load_facets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

        // Add to twigs
        add_filter('timber/context', function($context) {
            $context['facets'] = self::instance();

            return $context;
        });

        add_filter( 'facetwp_indexer_query_args', function($args) {
            $args['post_type'] = $_POST['post_type'];

            return $args;
        } );

    }

    public function load_lib(){
        if (is_plugin_active('facetwp/index.php')) {
            require_once(WMS_LIB_PATH . '/facetwp_templater/class.facetwp.templater.php');
        }
    }

    public function enqueue_scripts() {
        Js::do_load('facets', [
            'handle' => 'facets',
            'path'   => __DIR__ . '/assets/featherlight.js',
            'deps'   => ['jquery']
        ]);
    }

    /**
     * Call back for FacetWP plugin. Adds custom search facet and makes lists of facets
     * available.
     *
     * @return array
     */
    public function load_facets($facets) {
        // Make available to archive templates
        if ( ! is_page()) {
            $facets[] = array(
                'label'         => 'Category Search',
                'name'          => 'category_search',
                'type'          => 'search',
                'search_engine' => '',
                'placeholder'   => 'Search Category',
            );
        }

        return $facets;
    }

    /**
     * Helper to determine whether a facet is active on the page.
     * @return bool
     */
    public static function has_facet() {
        if (Cat_Opts::do_display('facetwp_template')
            || Cat_Opts::do_display('facetwp_search')
            || Cat_Opts::do_display('facetwp_filters')
        ) {
            return true;
        }

        return false;
    }

    /**
     * Find and return the name of the sidebar auto-created to hold this category's FacetWP facets.
     *
     * @requires WmsWidgetizedArea
     * @return string
     */
    public static function get_facetwp_sidebar() {
        if ( ! class_exists('WmsWidgetizedArea')) return '';

        $queried_obj = get_queried_object();

        $name = $queried_obj instanceof \WP_Post_Type ? $queried_obj->label : $queried_obj->name;

        $sidebar_slug = WmsWidgetizedArea::sanitize_slug('Filters for' . $name);

        return $sidebar_slug;
    }

    /**
     * Twig helper to determine whether to load CPT or category twigs.
     *
     * @return bool
     */
    public static function facetwp_is_cpt() {
        $queried_obj = get_queried_object();
        if ($queried_obj instanceof \WP_Post_Type) {
            return $queried_obj->name;
        } else {
            return false;
        }
    }

    function options_from($which) {
        switch ($which) {
            case 'templates':
                $templates = function() {
                    return FWP()->helper->get_templates();
                };
                break;
            case 'facets':
                $templates = function() {
                    return FWP()->helper->get_facets();
                };
                break;
        }
        $options = array(
            0 => '',
        );
        foreach ($templates as $template) {
            $options[ $template['name'] ] = $template['label'];
        }

        return $options;
    }

    function options_from_templates() {
        return self::options_from('templates');

    }

    function options_from_facets() {
        return self::options_from('facets');
    }


    /**
     * Returns the singleton instance of this class.
     *
     * @return Facets The singleton instance.
     */
    public static function instance() {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * singleton instance.
     *
     * @return void
     */
    private function __clone() {
    }

    /**
     * Private unserialize method to prevent unserializing of the singleton
     * instance.
     *
     * @return void
     */
    private function __wakeup() {
    }
}

Facets::instance();

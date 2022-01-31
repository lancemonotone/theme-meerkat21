<?php

namespace m21;

/*
    the array used for determining the options for each category/tag
*/

class Cat_Opts {
    public static $options, $config;
    private static $instance;

    function __construct() {
        add_action('edit_term', array(&$this, 'edit_term_save'));
        add_action('admin_head', array(&$this, 'add_styles'));

        // drm2 - Person of the future, delete this 1 year after 2021-05-17
        //add_action('wp_loaded', array(&$this, 'load_cat_config'), 10);

        // Add to twigs
        add_filter('timber/context', function($context) {
            $context['catopts'] = self::instance();

            return $context;
        });
    }

    public static function get_options() {
        return self::$options;
    }

    /**
     * Initialize category and single post category options, which are
     * used in @Meerkat16::instance()->do_display().
     */
    public static function load_cat_config($term_id = null) {
        self::set_options();
        if ( ! is_page()) {
            self::load_saved_config($term_id);
        }

        return self::$config;
    }

    /**
     * Twig helper function.
     *
     * Decide whether to display $item based on config options.
     *
     * {% catopts.do_display('author') %}
     *
     * @param      $item
     * @param bool $get_value Whether to echo the value of the key
     *
     * @return bool
     */
    public static function do_display($item, $get_value = false) {
        $do_display = false;
        if ( ! is_page()) {
            $viewing_context = is_single() ? 'single' : 'multi';
            $value           = '';

            // construct key name for config option array - ie single_show_date
            $key = $viewing_context . '_show_' . $item;

            // Check to see if this context/option pairing is turned on or not
            if ( ! empty(self::$config) && self::$config[ $key ]) {
                $do_display = true;
                $value      = self::$config[ $key ];
            } else {
                // Nothing set - use defaults
                if (empty(self::$config) && self::$options[ $key ]['default']) {
                    $do_display = true;
                    $value      = self::$options[ $key ]['default'];
                }
            }
        }
        if ($get_value) {
            return $value;
        } else {
            return $do_display;
        }
    }

    /**
     * Grabs category configuration options for this category/tag from the db
     * and resolves any conflict if a single post has multiple tags/cats.
     */
    public static function load_saved_config($term_id = null) {
        global $cat, $wp_query;

        // we need the term id to get at the saved options for the term

        // echo '<pre>';print_r($wp_query);echo '</pre>';

        // we don't need to keep loading this if we're not mixing categories or tags...
        if ( ! empty(self::$config) && (is_category() || is_tag() || is_archive())) {
            return;
        }

        // term id lives in different places depending on context
        if (empty($term_id)) {
            if (is_category()) {
                $term_id = $cat;
            } else if (is_tag()) {
                $term_id = $wp_query->query_vars['tag_id'];
            } else if (is_tax()) {
                $term_id = $wp_query->queried_object->term_taxonomy_id;
            } else if (is_archive()) {
                $term_id = $wp_query->queried_object->query_var;
            } else {
                // this handles if we're in single mode, or dealing with something that's potentially a mix of cats (ie index, author)

                // a single post can belong to multiple categories/tags, so we need to prioritize
                /*   category with saved options >
                     tag with saved options >
                     category with default options >
                     tag with default options
                */

                // load up cat & tag options, save in array associated with its priority
                $cats = get_the_category();
                $tags = get_the_tags();

                if ( ! empty($cats) || ! empty($tags)) {
                    $terms = array();
                    foreach ($cats as $c) {
                        $option_key = 'wms_category_config_' . $c->cat_ID;
                        if ($opt = get_option($option_key)) {
                            $terms[ $c->cat_ID ] = 4;
                        } else {
                            $terms[ $c->cat_ID ] = 2;
                        }
                    }
                    if ($tags) {
                        foreach ($tags as $t) {
                            $option_key = 'wms_category_config_' . $t->term_id;
                            if (get_option($option_key)) {
                                $terms[ $t->term_id ] = 3;
                            } else {
                                $terms[ $t->term_id ] = 1;
                            }
                        }
                    }
                    // sort by priority, grab first one
                    asort($terms);
                    foreach ($terms as $id => $priority) {
                        $term_id = $id;
                        continue;
                    }
                }
            }
        }

        if ( ! empty($term_id)) {
            //  grab the configuration options for this term & dump it into a global array
            $option_key = 'wms_category_config_' . $term_id;
            //echo "option key is $option_key<br>";
            self::$config = get_option($option_key);
            //echo 'SAVED ******<pre>';print_r($wms_saved_config_options);echo '</pre>]';
        }
    }

    public function edit_term_save($term_id) {
        $cat             = get_term($term_id);
        $widgetized_area = new \WmsWidgetizedArea();
        $sidebar_name    = 'Filters for ' . $cat->name;

        if ($_POST['multi_show_facetwp_filters'] && is_plugin_active('wms-shortcode/shortcode-builder.php')) {
            $widgetized_area->create_new_sidebar($sidebar_name);
        } else {
            $widgetized_area->delete_sidebar($sidebar_name);
        }
    }

    public static function set_options() {
        self::$options = array(
            //  date
            'multi_show_date'                => array(
                'label'   => 'Show date of post',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'multi'
            ),
            'single_make_primary_breadcrumb' => array(
                'label'   => 'Make primary breadcrumb',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'single'
            ),
            'single_skip_breadcrumb'         => array(
                'label'   => 'Skip this breadcrumb',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'single'
            ),
            'single_show_date'               => array(
                'label'   => 'Show date of post',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'single'
            ),
            // thumbnail
            'multi_show_thumb'               => array(
                'label'   => 'Show featured image thumbnail',
                'default' => true,
                'type'    => 'checkbox',
                'view'    => 'multi'
            ),
            'single_show_thumb'              => array(
                'label'   => 'Show featured image thumbnail',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'single'
            ),
            // related taxonomy
            'multi_show_related_tax'         => array(
                'label'   => 'Show category & tag links',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'multi'
            ),
            'single_show_related_tax'        => array(
                'label'   => 'Show category & tag links',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'single'
            ),
            // comments
            'multi_show_comment_status'      => array(
                'label'   => 'Show comment count/link',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'multi'
            ),
            'single_show_comment_form'       => array(
                'label'   => 'Show comment form',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'single'
            ),
            // author
            'multi_show_author'              => array(
                'label'   => 'Show author byline',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'multi'
            ),
            'single_show_author'             => array(
                'label'   => 'Show author byline',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'single'
            ),
            'single_show_author_bio'         => array(
                'label'   => 'Show author bio',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'single'
            ),
            'single_show_sharing'            => array(
                'label'   => 'Show social media sharing links',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'single'
            ),
            // content-excerpt
            'multi_show_content'             => array(
                'label'   => 'Show full content (instead of excerpt)',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'multi'
            ),
            // prev-next post
            'single_show_pagination'         => array(
                'label'   => 'Show previous/next post links',
                'default' => true,
                'type'    => 'checkbox',
                'view'    => 'single'
            ),
            // order by criteria
            'multi_orderby'                  => array(
                'label'   => 'Order posts by',
                'default' => 'date',
                'type'    => 'select',
                'options' => array('date' => 'Date', 'title' => 'Title', 'ID' => 'ID'),
                'view'    => 'multi'
            ),
            // order by criteria
            'multi_order_dir'                => array(
                'label'   => 'Order direction',
                'default' => 'DESC',
                'type'    => 'select',
                'options' => array('ASC' => 'Ascending', 'DESC' => 'Descending'),
                'view'    => 'multi'
            )
        );
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        if (is_plugin_active('facetwp/index.php')) {
            self::$options['multi_show_facetwp_search'] = array(
                'label'   => 'Show FacetWP Search Box',
                'default' => false,
                'type'    => 'checkbox',
                'view'    => 'multi'
            );
            if (is_plugin_active('wms-shortcode/shortcode-builder.php')) {
                self::$options['multi_show_facetwp_filters'] = array(
                    'label'   => 'Create/show widget area for filters',
                    'default' => false,
                    'type'    => 'checkbox',
                    'view'    => 'multi'
                );
            }
            self::$options['multi_show_facetwp_template'] = array(
                'label'   => 'Use FacetWP template',
                'default' => '',
                'type'    => 'text',
                'view'    => 'multi'
            );
        }
    }

    /**
     * Style the category config options
     */
    function add_styles() {
        if ($GLOBALS['pagenow'] !== 'term.php') return;

        echo <<< EOD
<style>
.cat-config-options {
    margin: 10px;
    width: 90%;
}

.cat-config-multi {
    float: right;
    width: 50%;
}

.cat-config-multi select {
    float: right;
    width: 100px;
    margin-right: 100px;
    margin-top: -3px;
}

.cat-config-single {
    float: left;
    width: 50%;
}

.cat-config-blurb {
    margin: 10px 0 15px;
}

.cat-config-item {
    margin-bottom: 10px;
    width: 300px;
}
.cat-config-item input {
    margin-right: 5px;
    float: right;
}
</style>
EOD;

    }

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return Cat_Opts The *Singleton* instance.
     */
    public static function instance() {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * *Singleton* instance.
     *
     * @return void
     */
    private function __clone() {
    }

    /**
     * Private unserialize method to prevent unserializing of the *Singleton*
     * instance.
     *
     * @return void
     */
    private function __wakeup() {
    }
}

new Cat_Opts();
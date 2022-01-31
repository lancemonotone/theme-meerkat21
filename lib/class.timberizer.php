<?php

namespace m21;

use Timber\Timber;

class Timberizer {

    private static $instance;

    protected function __construct() {
        /**
         * @see TimberLoader::get_twig
         */
        if ( ! defined('TWIG_DEBUG')) {
            define('TWIG_DEBUG', true);
        }

        if ( ! class_exists('Timber')) {
            add_action('admin_notices', function() {
                echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin.</p></div>';
            });

            return;
        }

        Timber::$dirname = array_merge((array) Timber::$dirname, array('views'));

        add_filter('timber/context', array($this, 'add_to_context'), 1);
        add_filter('timber/twig', function(\Twig_Environment $twig) {
            // Make WP edit_post_link() available within twigs
            $twig->addFunction(new \Timber\Twig_Function('edit_post_link', 'edit_post_link'));
            $twig->addFilter(new \Twig\TwigFilter('json_decode', 'json_decode'));

            return $twig;
        });
    }

    /**
     * Returns the singleton instance of this class.
     *
     * @return Timberizer The singleton instance.
     */
    public static function instance() {
        if ( ! self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param $context
     *
     * @return mixed
     */
    public function add_to_context($context) {
        $context['theme_uri'] = get_stylesheet_directory_uri();
        if (is_active_sidebar('sidebar')) {
            $context['sidebar_widgets'] = array(
                'widgets' => Timber::get_widgets('sidebar'),
                'id'      => 'tertiary',
                'class'   => 'sidebar'
            );
        }
        $context['navbox_widget'] = \Timber\Helper::ob_function('the_widget', array('WMS_Navbox_Widget'));
        //show/hide site title and breadcrumbs- site-masthead in site-header
        $context['exclude_masthead'] = ! ((get_current_blog_id() != '93') or (get_current_blog_id() == '93' and ! is_front_page()));
        // Get network sidebar message from mu-plugin
        $context['network_sidebar_message'] = \WMS\Network_Sidebar_Message::display_message();

        return $context;
    }

    /**
     * @param $twig
     *
     * @return mixed
     */
    public function add_to_twig($twig) {
        /* this is where you can add your own functions to twig */
        $twig->addExtension(new \Twig_Extension_StringLoader());

        return $twig;
    }

    /**
     * Render Twig template based on queried object
     *
     * $template is used general all-purpose twig, but you can be more
     * specific by creating a twig to match $context['page'].
     *
     * @param $extra_args {Array} of extra data to pass to context - array('key' => mixed)
     */
    public static function render_template($extra_args = null) {
        global $post, $wp_query;
        $context            = Timber::get_context();
        $context['options'] = get_fields('options');

        if ( ! in_array($post->post_type, array('page', 'post'))) {
            $post_type = $post->post_type;
        }else{
            // Prevent undefined index warning
            $post_type = '';
        }

        if ($extra_args && is_array($extra_args)) {
            foreach ($extra_args as $k => $v) {
                $context[ $k ] = $v;
            }
        }

        // Prevent undefined index warning
        if(empty($extra_args['template'])){
            $extra_args['template'] = '';
        }

        if (is_singular()) { // post or page, check for custom 404 content
            $context['post'] = apply_filters('get_custom_404', new \Timber\Post($wp_query->queried_object ? $wp_query->queried_object->ID : null));
        } else { // anything else
            // Remove pagination for pages that are more than 2 away from current
            $pagination            = Timber::get_pagination(array('mid_size' => 1));
            $context['pagination'] = $pagination;
        }


        if (!empty($extra_args['template'])) {
            $context['page']     = $extra_args['template'];
            $context['template'] = $extra_args['template'];

        } else if (is_single()) { // single post
            $context['page']     = 'single';
            $context['template'] = 'single';

        } else if (is_page()) { // single page
            if (is_front_page()) { // home page
                $context['page'] = 'home';
                if (is_active_sidebar('home-widget-area')) {
                    $context['home_widgets'] = array(
                        'widgets' => Timber::get_widgets('home-widget-area'),
                        'id'      => 'home-widgets',
                        'class'   => 'home-widgets'
                    );
                }
                $context['template'] = 'page';

            } else { // generic page
                $context['page']     = 'page';
                $context['template'] = 'page';
            }

        } else if (is_home()) { // posts/blog homepage
            $context['page']     = 'home';
            $context['template'] = 'archive';

        } else if (is_category()) {
            $context['archive_title']       = get_cat_name(get_query_var('cat'));
            $context['archive_description'] = term_description();
            $context['page']                = 'category';
            $context['template']            = 'archive';

        } else if (is_tag()) {
            $tag_name                       = get_tag(get_query_var('tag_id'));
            $context['archive_title']       = $tag_name;
            $context['archive_description'] = term_description();
            $context['page']                = 'tag';
            $context['template']            = 'archive';

        } else if (is_archive()) {
            $queried_obj                    = get_queried_object();
            $context['archive_title']       = $queried_obj->label;
            $context['archive_description'] = $queried_obj->description;
            $context['page']                = $queried_obj->name;
            $context['template']            = 'archive';

        } else if (is_author()) {
            $context['archive_title'] = get_the_author();
            $context['page']          = 'author';
            $context['template']      = 'archive';

        } else if (is_404()) {
            if (function_exists('legacyRedirect')) {
                // Check to see if this request has a home.
                legacyRedirect();
            }
            $context['page']     = 'is-404';
            $context['template'] = '404';

        } else if (is_search() && isset($_GET['s'])) {
            // && $_GET['s'] != ''):
            $context['archive_title'] = 'Results for: ' . stripslashes($_GET['s']);
            $context['page']          = 'wp-search';
            $context['template']      = 'wp-search';

        } else if (Search::isWmsSearch()) {
            $context['page']         = 'search';
            $context['search']       = Search::getSearchContext();
            $context['hide_sidebar'] = true;
            $context['template']     = 'search';
        }

        // Allow modifying $context before render
        $context = apply_filters('timberizer_before_render', $context);

        // render using Twig template index.twig
        Timber::render(array(
            'page-' . $extra_args['template'] . '.twig',
            'page-' . $post->post_name . '.twig',
            'page-' . $post_type . '.twig',
            'loop-' . $context['page'] . '.twig', // allows for specific twigs
            'loop-' . $context['template'] . '.twig'
        ), $context);
    }
}

Timberizer::instance();

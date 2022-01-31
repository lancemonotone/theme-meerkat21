<?php

namespace m21;

class Breadcrumbs {
    public $crumbs = array();

    /**
     * @return array
     */
    function get_breadcrumbs(): array {
        $current_id   = get_current_blog_id();
        $current_site = get_site($current_id);
        $is_www       = WWW_BLOG_ID === $current_id;

        // college link
        $this->crumbs [] = $this->one_crumb(
            'Williams',
            \Wms_Server::instance()->www,
            'wms-home-crumb'
        );

        // If we're on a subdirectory site, append the parent subdomain crumb.
        if ($current_site->path !== '/') {
            $parent_url = \Wms_Server::instance()->name;
            $parent_id  = get_blog_id_from_url($parent_url);
            if ($parent_id !== WWW_BLOG_ID) {
                $parent          = get_blog_details($parent_id);
                $this->crumbs [] = $this->one_crumb(
                    $parent->blogname,
                    $parent->siteurl
                );
            }
        }

        // Has a parent site been configured on the Options page?
        if ($parent_site = get_field('parent_site', 'option')) {
            $site            = get_blog_details($parent_site['value']);
            $this->crumbs [] = $this->one_crumb(
                $site->blogname,
                'https://' . $site->domain,
                'dept-home-crumb'
            );
        }

        // dept homepage link
        if ( ! $is_www) {
            $this->crumbs [] = $this->one_crumb(
                get_bloginfo('name'),
                get_home_url(),
                'dept-home-crumb'
            );
        }

        if ( ! is_front_page()) {
            if ($custom_end_crumb = apply_filters('custom_end_crumb', null)) {
                $this->crumbs [] = $this->one_crumb($custom_end_crumb);
            } elseif ($custom_crumb_title = apply_filters('custom_crumb_title', null)) {
                $this->crumbs [] = $this->one_crumb($custom_crumb_title);
            } /*elseif (\Meerkat16_Profiles::instance()->is_wms_profile) {
                // faculty/staff profile gets directory page & name of person as crumb
                if ($staff_page = get_field('staff_url', 'options')) {
                    $this->crumbs       [] = $this->one_crumb(
                        $staff_page->post_title,
                        get_permalink($staff_page->ID)
                    );
                }
                $this->crumbs [] = $this->one_crumb(\Meerkat16_Profile_Single::instance()->get_the_profile()['full_name']);
            }*/ elseif (is_page()) {
                $this->add_page_crumbs();
            } elseif (is_single()) {
                // a single post
                global $post;
                $post_type = get_post_type();
                if ($post_type !== 'post' && function_exists('cptui_get_cptui_post_type_object')) {
                    $this->add_cpt_crumbs($post_type);
                } else {
                    $this->add_post_crumbs();
                }
            } elseif (is_tag()) {
                $this->add_tag_crumbs();
            } elseif (is_post_type_archive()) {
                $this->add_post_type_archive_crumbs();
            } elseif (is_archive() || is_category()) {
                if ($terms        [] = get_queried_object()) {
                    $primary_term = $this->get_primary_term($terms);
                    $this->add_term_crumbs($primary_term);
                }
            } elseif (is_author()) {
                $this->crumbs [] = $this->one_crumb('Author');
                $this->add_author_crumbs();
            } elseif (Search::isWmsSearch()) {
                $this->crumbs [] = $this->one_crumb('Search & Directories');
            } elseif (is_404() && ! (substr($_SERVER['REQUEST_URI'], 0, 12) == '/catalog.php')) {
                $this->crumbs [] = $this->one_crumb('Page Not Found');
            }
        }

        return $this->crumbs;
    }

    /**
     * Searches through all taxonomies for terms attached to the post
     * then sorts the terms hierarchically by primary term (selected in category options),
     * then by menu order, if set, and finally by id. It also sets the 'skip_breadcrumb'
     * flag
     *
     * @param array $terms
     *
     * @return \WP_Term|false
     */
    function get_primary_term($terms) {
        if (empty($terms)) return false;

        // Get each term's parent and their configs to check them for skip and/or primary breadcrumb flags
        foreach ($terms as &$term) {
            // Get saved config for term and check flags
            $config                    = Cat_Opts::load_cat_config($term->term_id);
            @$term->{'skip_breadcrumb'} = ! empty($config['single_skip_breadcrumb']) && $config['single_skip_breadcrumb'] === 'on';
            $term->{'primary_term'}    = false;
            $term->{'primary_terms'}   = array();

            // Is this term primary? This will be overwritten if there are primary parents.
            // Eventually this holds the top-level primary.
            if ( ! empty($config['single_make_primary_breadcrumb']) && $config['single_make_primary_breadcrumb'] === 'on') {
                $term->primary_term     = true;
                $term->primary_terms [] = $term;
            }

            // Init to push possible parents
            $term->{'parents'} = array();
            $parent            = get_term($term->parent, $term->taxonomy);

            // Loop parents until we get to the top
            while ( ! is_wp_error($parent)) {
                $parent_config               = Cat_Opts::load_cat_config($parent->term_id);
                @$parent->{'skip_breadcrumb'} = ! empty($parent_config['single_skip_breadcrumb']) && $parent_config['single_skip_breadcrumb'] === 'on';

                if ( ! empty($parent_config['single_make_primary_breadcrumb']) && $parent_config['single_make_primary_breadcrumb'] === 'on') {
                    // Flag parent (debug housekeeping - not necessary)
                    $parent->{'primary_term'} = true;
                    // Set child's primary to parent (may be overwritten if more parents in branch)
                    $term->primary_terms [] = $parent;
                    // Set parent term order on child for later sorting if post has more than one term with a primary parent
                    $term->{'primary_term_order'} = $parent->term_order;
                }

                $term->parents [] = $parent;
                $parent           = get_term($parent->parent, $term->taxonomy);
            }
        }

        // Check to see if any of the terms are primary
        $primary_terms = array_filter($terms, function($term) {
            return ! empty($term->primary_terms);
        });

        // Sort by primary term order or term id
        if ( ! empty($primary_terms)) {
            $primary_terms = wp_list_sort($primary_terms, 'primary_term_order');
            $primary_term  = reset($primary_terms);
        } else {
            $terms        = wp_list_sort($terms, 'term_id');
            $primary_term = reset($terms);
        }

        return $primary_term;
    }

    /**
     * @return array|false
     */
    static function get_post_terms() {
        global $post;
        // Get all site taxonomies because we can't get post terms without knowing their taxonomy
        $taxonomies = array_keys(get_taxonomies('', 'names'));
        // Get all post terms for all taxonomies
        $terms = wp_get_object_terms($post->ID, $taxonomies);

        return ! empty($terms) ? $terms : false;
    }

    /**
     * @return void
     */
    function add_post_type_archive_crumbs(): void {
        global $wp_query, $post_type;
        $title = $wp_query->queried_object->label;

        $this->crumbs [] = $this->one_crumb(
            $title,
            get_post_type_archive_link($post_type)
        );
    }

    /**
     * @return void
     */
    function add_page_crumbs(): void {
        global $post;

        $parents = array_map('get_post', array_reverse((array) get_post_ancestors($post)));
        foreach ($parents as $parent) {
            $this->crumbs [] = $this->one_crumb(
                $parent->post_title,
                get_permalink($parent)
            );
        }

        $custom_page_bc  = get_field('page_breadcrumb');
        $page_title      = $custom_page_bc ? $custom_page_bc : get_the_title($post);
        $this->crumbs [] = $this->one_crumb($page_title);
    }

    /**
     * @param      $term
     * @param bool $is_link
     */
    function add_term_crumbs($term, $is_link = false) {
        if ($term->slug === 'uncategorized') return;

        // builds breadcrumb trail for a category and its ancestors
        if ($ancestors = $term->parents) {
            // sort the other direction (we want oldest ancestor first)
            $ancestors = array_reverse($ancestors);
            foreach ($ancestors as $ancestor) {
                if ( ! $ancestor->skip_breadcrumb) {
                    $this->crumbs[] = $this->one_crumb(
                        $ancestor->name,
                        get_term_link($ancestor->term_id),
                        'ancestor-crumb'
                    );
                }
            }
        }

        if ( ! $term->skip_breadcrumb) {
            $this->crumbs [] = $this->one_crumb(
                $term->name,
                $is_link ? get_term_link($term->term_id) : null,
                'term-crumb'
            );
        }
    }


    /**
     * Builds a single breadcrumb
     *
     * @param        $name
     * @param string $url
     * @param string $class
     * @param bool   $wrap
     *
     * @return array
     */
    function one_crumb($name, $url = '', $class = '', $wrap = true): array {
        return array(
            'name'  => $name,
            'url'   => $url,
            'class' => $class,
            'wrap'  => $wrap
        );
    }

    /**
     * @return void
     */
    function add_post_crumbs(): void {
        if ($terms = $this->get_post_terms()) {
            $primary_term = $this->get_primary_term($terms);
            $this->add_term_crumbs($primary_term, true);
        }

        global $post;
        $title           = get_field('page_breadcrumb');
        $this->crumbs [] = $this->one_crumb($title ? $title : $post->post_title);
    }

    /**
     * @return void
     */
    function add_tag_crumbs(): void {
        // builds crumb for a tag page
        $tag_obj = get_tag(get_query_var('tag_id'));
        $term    = get_term_by('slug', $tag_obj->slug, 'post_tag', ARRAY_A);

        $this->crumbs [] = $this->one_crumb(
            ucwords($term['name']),
            '/tag/' . $term['slug'],
            $tag_obj->name . '-crumb'
        );

    }

    /**
     * Builds crumb for an author page
     * @return void
     */
    function add_author_crumbs(): void {
        global $author;
        $author_obj = get_user_by('id', $author);

        $this->crumbs [] = $this->one_crumb(
            $author_obj->user_nicename,
            '/author/' . $author_obj->user_login,
            $author_obj->user_nicename . '-crumb'
        );
    }

    /**
     * Check if a custom post type would like to override the default breadcrumb
     * by adding 'breadcrumb_priority' to CPT UI's 'custom supports' field.
     */
    function add_cpt_crumbs($post_type): void {
        global $post;
        // cptui_get_cptui_post_type_object() should denote array as return
        $cpt = (array) cptui_get_cptui_post_type_object($post_type);
        if ($cpt && preg_match("/breadcrumb[\s\-_]priority/i", $cpt['custom_supports'])) {
            $this->crumbs[] = $this->one_crumb($cpt['label'], '/' . $cpt['name']);
        } else {
            $terms = $this->get_post_terms();
            if ( ! empty($terms)) {
                $primary_term = $this->get_primary_term($terms);
                $this->add_term_crumbs($primary_term, true);
            }
        }

        $custom_page_bc = get_field('page_breadcrumb');
        $post_title     = $custom_page_bc ? $custom_page_bc : $post->post_title;

        $this->crumbs[] = $this->one_crumb($post_title);
    }

    public function __construct() {
    }
}

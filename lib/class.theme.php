<?php

namespace m21;

use Timber\Menu;

class Theme {
    public $megamenu_locations = ['mega_social'   => 'Megamenu Social',
                                  'mega_featured' => 'Megamenu Featured',
                                  'mega_global'   => 'Megamenu Global'];
    public $sitenav_locations = ['site'   => 'Site Navigation',
                                 'social' => 'Site Social Navigation',
                                 'footer' => 'Site Footer Links'];

    public function __construct() {
        add_filter('timber/context', [$this, 'add_nav_to_context']);
        add_filter('timber/context', [$this, 'add_contact_info_to_context']);
        add_filter('timber/context', [$this, 'add_is_off_campus_to_context']);
        add_action('init', [$this, 'register_navigation']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_filter('body_class', [$this, 'add_body_class']);
    }

    /**
     * Used in twigs: navigation.mega_global
     *
     * @param $context
     *
     * @return array
     */
    public function add_nav_to_context($context): array {
        // Get megamenu navs from WWW
        switch_to_blog(WWW_BLOG_ID);
        $locations = get_nav_menu_locations();
        foreach ($this->megamenu_locations as $slug => $name) {
            $context['navigation'][ $slug ] = new Menu($locations[ $slug ]);
        }
        restore_current_blog();

        // Get site navs
        foreach (get_nav_menu_locations() as $slug => $id) {
            if (has_nav_menu($slug) && empty($context[ $slug ])) {
                $context['navigation'][ $slug ] = new Menu($id);
            }
        }

        unset($context['wp_nav_menu']); // Don't need this because we're invoking them individually.

        return $context;
    }

    public function register_navigation() {
        global $blog_id;
        if (WWW_BLOG_ID === $blog_id) {
            $site_nav = array_merge($this->sitenav_locations, $this->megamenu_locations);
        } else {
            $site_nav = $this->sitenav_locations;
        }

        register_nav_menus($site_nav);
    }

    public function enqueue_scripts() {
        $ajax_url    = \Wms_Server::instance()->is_local() ? str_replace('https', 'http', admin_url('admin-ajax.php')) : admin_url('admin-ajax.php');
        $site_url    = \Wms_Server::instance()->site_url;
        $site_domain = \Wms_Server::instance()->domain;

        Js::do_load(
            'main',
            [
                'src'   => M16_TEMP_JS_URL . '/main.js',
                'deps'  => array(
                    'jquery',
                    'featherlight-config',
                    'jquery-color',
                    'jquery-ui-draggable',
                    'jquery-ui-droppable',
                    'jquery-ui-sortable',
                    'jquery-ui-core',
                    'jquery-ui-tooltip',
                    'jquery-ui-widget',
                    'jquery-ui-mouse',
                    'comment-reply',
                    'purl'
                ),
                'v'     => file_exists(M16_TEMP_JS_URL . '/main.js') ? filemtime(M16_TEMP_JS_URL . '/main.js') : time(),
                'local' => array(
                    'myAjax' => array(
                        'wwwurl'  => \Wms_Server::instance()->www,
                        'ajaxurl' => $ajax_url,
                        'siteurl' => $site_url,
                        'domain'  => $site_domain,
                    )
                )
            ]
        );
    }

    /**
     * @param $classes
     *
     * @return mixed
     */
    function add_body_class($classes) {
        $classes[] = __NAMESPACE__;

        return $classes;
    }

    /**
     * Used in twigs: is_off_campus
     * Checks to see if the campus only restriction (custom field) is set on a page or a post.
     *
     * @param $context
     *
     * @return mixed
     */
    function add_is_off_campus_to_context($context) {
        global $id;
        $context['is_off_campus'] = false;
        $campus_only              = get_field('campus_only', $id);
        if ($campus_only && is_off_campus() && ! is_user_logged_in()) {
            // Page is designated on-campus only, do not display
            $context['is_off_campus'] = true;
        }

        return $context;
    }

    /**
     * Used in twigs: contact_info.city
     * Return saved or default values for contact information
     *
     * @param $context
     *
     * @return mixed
     */
    function add_contact_info_to_context($context) {
        $contact_info_group_id = 'group_54fdbe1bb9791';
        $contact_group         = acf_get_field_group($contact_info_group_id);
        $contact_fields        = acf_get_fields($contact_group);

        $context['contact_info'] = array();
        foreach ($contact_fields as $field_obj) {
            $context['contact_info'][ $field_obj['name'] ] = get_field($field_obj['name'], 'options');
        }

        return $context;
    }
}

new Theme();

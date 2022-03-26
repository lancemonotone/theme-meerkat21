<?php

namespace m21;

class BB_Assets_Purge {
    public $action = 'bb-assets-purge';
    public $nonce;

    public function __construct() {
        add_action('admin_bar_menu', [&$this, 'add_toolbar_items'], 100);
        add_action('wp_enqueue_scripts', [&$this, 'enqueue_scripts']);
        add_action('admin_footer', [&$this, 'enqueue_scripts']);
        add_action('wp_ajax_' . $this->action, [&$this, 'purge_bb_callback']);

        $this->nonce = wp_create_nonce($this->action);
    }

    function add_toolbar_items(\WP_Admin_Bar $admin_bar) {
        // Is administrator
        if (current_user_can( 'edit_posts' )) {
            $admin_bar->add_menu(array(
                'id'    => $this->action,
                'title' => 'Purge BB Assets',
                'href'  => 'javascript:void(0)',
                'meta'  => array(
                    'title' => __('Purge Beaver Builder cached assets'),
                ),
            ));
        }
    }

    function enqueue_scripts() {
        Js::do_inline([
            'handle'  => 'admin-bar',
            'path'    => __DIR__ . "/assets/purge-button.js",
            'replace' => [
                ['{%action%}', '{%nonce%}'],
                [$this->action, $this->nonce]
            ],
            'deps'    => ['jquery']
        ]);
    }

    function purge_bb_callback() {
        check_ajax_referer($this->action, 'security');

        if ( ! \FLBuilderAdmin::current_user_can_access_settings()) {
            return;
        } else {
            // Clear builder cache.
            \FLBuilderModel::delete_asset_cache_for_all_posts();

            echo 'BB cache purged';
            wp_die();
        }
    }
}

new BB_Assets_Purge();

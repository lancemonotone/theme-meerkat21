<?php

namespace m21;

class Images {
    public function __construct() {
        add_action('after_setup_theme', [$this, 'add_image_sizes']);
        add_filter('manage_posts_columns', [$this, 'posts_columns'], 5);
        add_action('manage_posts_custom_column', [$this, 'posts_custom_columns'], 5, 2);
        add_filter('media_view_settings', [$this, 'set_gallery_default_link_type']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function enqueue_scripts() {
        // If this still needed, uncomment. If not, delete!
        /*if (is_singular() && wp_attachment_is_image()) {
            Js::do_inline([
                'handle' => 'keyboard-image-navigation',
                'path'   => __DIR__ . '/assets/keyboard-navigation.js',
                'deps'   => ['jquery']
            ]);
        }*/
    }

    function add_image_sizes() {
        // Thumbnails to Admin Post View
        add_image_size('admin-thumb', 100, 999999); // 100 pixels wide (and unlimited height)
        //meerkat16-home images added here so they can be scraped---all aspect ratio 4:3 cropped center, center (true)
        add_image_size('newsmix-featured', 832, 624, true); // 832w x 624h
        add_image_size('newsmix-uncropped-thumb', 250, 9999, false);//250 x 100%
        add_image_size('newsmix-featured2', 188, 134, true); //  188w x 134h
    }

    // retrieves the attachment ID from the file URL
    public static function get_image_id($image_url) {
        global $wpdb;
        $attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE guid LIKE %s;", '%' . $wpdb->esc_like($image_url)));

        return $attachment[0];
    }

    /**
     * Get all the registered image sizes along with their dimensions
     *
     * @return array $image_sizes The image sizes
     * @link http://core.trac.wordpress.org/ticket/18947 Reference ticket
     *
     * @global array $_wp_additional_image_sizes
     *
     */
    public static function get_all_image_sizes() {
        global $_wp_additional_image_sizes;

        $default_image_sizes = get_intermediate_image_sizes();

        foreach ($default_image_sizes as $size) {
            $image_sizes[ $size ]['width']  = intval(get_option("{$size}_size_w"));
            $image_sizes[ $size ]['height'] = intval(get_option("{$size}_size_h"));
            $image_sizes[ $size ]['crop']   = get_option("{$size}_crop") ? get_option("{$size}_crop") : false;
        }

        if (isset($_wp_additional_image_sizes) && count($_wp_additional_image_sizes)) {
            $image_sizes = array_merge($image_sizes, $_wp_additional_image_sizes);
        }

        return $image_sizes;
    }

    public function set_gallery_default_link_type($settings) {
        $settings['galleryDefaults']['link'] = 'file';
        $settings['galleryDefaults']['size'] = 'medium';

        return $settings;
    }

    function posts_columns($defaults) {
        $defaults['my_post_thumbs'] = __('Featured Image');

        return $defaults;
    }

    function posts_custom_columns($column_name, $id) {
        if ($column_name === 'my_post_thumbs') {
            the_post_thumbnail('admin-thumb');
        }
    }
}

new Images();
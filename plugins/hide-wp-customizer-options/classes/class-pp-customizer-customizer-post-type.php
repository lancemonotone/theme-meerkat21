<?php
if ( ! defined('ABSPATH')) exit; // Exit if accessed directly.

/**
 * Starter Plugin Post Type Class
 *
 * All functionality pertaining to post types in Starter Plugin.
 *
 * @package WordPress
 * @subpackage PP_Customizer_Customizer
 * @category Plugin
 * @author Matty
 * @since 1.0.0
 */
class PP_Customizer_Customizer_Post_Type {
    /**
     * The post type token.
     * @access public
     * @since  1.0.0
     * @var    string
     */
    public $post_type;

    /**
     * The post type singular label.
     * @access public
     * @since  1.0.0
     * @var    string
     */
    public $singular;

    /**
     * The post type plural label.
     * @access public
     * @since  1.0.0
     * @var    string
     */
    public $plural;

    /**
     * The post type args.
     * @access public
     * @since  1.0.0
     * @var    array
     */
    public $args;

    /**
     * The taxonomies for this post type.
     * @access public
     * @since  1.0.0
     * @var    array
     */
    public $taxonomies;

    /**
     * Constructor function.
     *
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function __construct($post_type = 'thing', $singular = '', $plural = '', $args = array(), $taxonomies = array()) {
        $this->post_type  = $post_type;
        $this->singular   = $singular;
        $this->plural     = $plural;
        $this->args       = $args;
        $this->taxonomies = $taxonomies;

        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'register_taxonomy'));

        if (is_admin()) {
            global $pagenow;

            add_action('admin_menu', array($this, 'meta_box_setup'), 20);
            add_action('save_post', array($this, 'meta_box_save'));
            add_filter('enter_title_here', array($this, 'enter_title_here'));
            add_filter('post_updated_messages', array($this, 'updated_messages'));

            if ($pagenow == 'edit.php' && isset($_GET['post_type']) && esc_attr($_GET['post_type']) == $this->post_type) {
                add_filter('manage_edit-' . $this->post_type . '_columns', array($this, 'register_custom_column_headings'), 10, 1);
                add_action('manage_posts_custom_column', array($this, 'register_custom_columns'), 10, 2);
            }
        }

        add_action('after_setup_theme', array($this, 'ensure_post_thumbnails_support'));
        add_action('after_theme_setup', array($this, 'register_image_sizes'));
    } // End __construct()

    /**
     * Register the post type.
     *
     * @access public
     * @return void
     */
    public function register_post_type() {
        $labels = array(
            'name'               => sprintf(_x('%s', 'post type general name', 'pp-customizer-customizer'), $this->plural),
            'singular_name'      => sprintf(_x('%s', 'post type singular name', 'pp-customizer-customizer'), $this->singular),
            'add_new'            => _x('Add New', $this->post_type, 'pp-customizer-customizer'),
            'add_new_item'       => sprintf(__('Add New %s', 'pp-customizer-customizer'), $this->singular),
            'edit_item'          => sprintf(__('Edit %s', 'pp-customizer-customizer'), $this->singular),
            'new_item'           => sprintf(__('New %s', 'pp-customizer-customizer'), $this->singular),
            'all_items'          => sprintf(__('All %s', 'pp-customizer-customizer'), $this->plural),
            'view_item'          => sprintf(__('View %s', 'pp-customizer-customizer'), $this->singular),
            'search_items'       => sprintf(__('Search %a', 'pp-customizer-customizer'), $this->plural),
            'not_found'          => sprintf(__('No %s Found', 'pp-customizer-customizer'), $this->plural),
            'not_found_in_trash' => sprintf(__('No %s Found In Trash', 'pp-customizer-customizer'), $this->plural),
            'parent_item_colon'  => '',
            'menu_name'          => $this->plural,
        );

        $single_slug  = apply_filters('pp-customizer-customizer_single_slug', _x(sanitize_title_with_dashes($this->singular), 'single post url slug', 'pp-customizer-customizer'));
        $archive_slug = apply_filters('pp-customizer-customizer_archive_slug', _x(sanitize_title_with_dashes($this->plural), 'post archive url slug', 'pp-customizer-customizer'));

        $defaults = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => $single_slug),
            'capability_type'    => 'post',
            'has_archive'        => $archive_slug,
            'hierarchical'       => false,
            'supports'           => array('title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'),
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-smiley',
        );

        $args = wp_parse_args($this->args, $defaults);

        register_post_type($this->post_type, $args);
    } // End register_post_type()

    /**
     * Register the "thing-category" taxonomy.
     * @access public
     * @return void
     * @since  1.3.0
     */
    public function register_taxonomy() {
        $this->taxonomies['thing-category'] = new PP_Customizer_Customizer_Taxonomy(); // Leave arguments empty, to use the default arguments.
        $this->taxonomies['thing-category']->register();
    } // End register_taxonomy()

    /**
     * Add custom columns for the "manage" screen of this post type.
     *
     * @access public
     *
     * @param string $column_name
     * @param int    $id
     *
     * @return void
     * @since  1.0.0
     */
    public function register_custom_columns($column_name, $id) {
        global $post;

        // Uncomment this line to use metadata in the switches below.
        // $meta = get_post_custom( $id );

        switch ($column_name) {
            case 'image':
                echo $this->get_image($id, 40);
                break;

            default:
                break;
        }
    } // End register_custom_columns()

    /**
     * Add custom column headings for the "manage" screen of this post type.
     *
     * @access public
     *
     * @param array $defaults
     *
     * @return void
     * @since  1.0.0
     */
    public function register_custom_column_headings($defaults) {
        $new_columns = array('image' => __('Image', 'pp-customizer-customizer'));

        $last_item = array();

        if (isset($defaults['date'])) {
            unset($defaults['date']);
        }

        if (count($defaults) > 2) {
            $last_item = array_slice($defaults, -1);

            array_pop($defaults);
        }
        $defaults = array_merge($defaults, $new_columns);

        if (is_array($last_item) && 0 < count($last_item)) {
            foreach ($last_item as $k => $v) {
                $defaults[ $k ] = $v;
                break;
            }
        }

        return $defaults;
    } // End register_custom_column_headings()

    /**
     * Update messages for the post type admin.
     *
     * @param array $messages Array of messages for all post types.
     *
     * @return array           Modified array.
     * @since  1.0.0
     */
    public function updated_messages($messages) {
        global $post, $post_ID;

        $messages[ $this->post_type ] = array(
            0  => '', // Unused. Messages start at index 1.
            1  => sprintf(__('%3$s updated. %sView %4$s%s', 'pp-customizer-customizer'), '<a href="' . esc_url(get_permalink($post_ID)) . '">', '</a>', $this->singular, strtolower($this->singular)),
            2  => __('Custom field updated.', 'pp-customizer-customizer'),
            3  => __('Custom field deleted.', 'pp-customizer-customizer'),
            4  => sprintf(__('%s updated.', 'pp-customizer-customizer'), $this->singular),
            /* translators: %s: date and time of the revision */
            5  => isset($_GET['revision']) ? sprintf(__('%s restored to revision from %s', 'pp-customizer-customizer'), $this->singular, wp_post_revision_title((int) $_GET['revision'], false)) : false,
            6  => sprintf(__('%1$s published. %3$sView %2$s%4$s', 'pp-customizer-customizer'), $this->singular, strtolower($this->singular), '<a href="' . esc_url(get_permalink($post_ID)) . '">', '</a>'),
            7  => sprintf(__('%s saved.', 'pp-customizer-customizer'), $this->singular),
            8  => sprintf(__('%s submitted. %sPreview %s%s', 'pp-customizer-customizer'), $this->singular, strtolower($this->singular), '<a target="_blank" href="' . esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))) . '">', '</a>'),
            9  => sprintf(__('%s scheduled for: %1$s. %2$sPreview %s%3$s', 'pp-customizer-customizer'), $this->singular, strtolower($this->singular),
                // translators: Publish box date format, see http://php.net/date
                '<strong>' . date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date)) . '</strong>', '<a target="_blank" href="' . esc_url(get_permalink($post_ID)) . '">', '</a>'),
            10 => sprintf(__('%s draft updated. %sPreview %s%s', 'pp-customizer-customizer'), $this->singular, strtolower($this->singular), '<a target="_blank" href="' . esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))) . '">', '</a>'),
        );

        return $messages;
    } // End updated_messages()

    /**
     * Setup the meta box.
     *
     * @access public
     * @return void
     * @since  1.0.0
     */
    public function meta_box_setup() {
        add_meta_box($this->post_type . '-data', __('Thing Details', 'pp-customizer-customizer'), array($this, 'meta_box_content'), $this->post_type, 'normal', 'high');
    } // End meta_box_setup()

    /**
     * The contents of our meta box.
     *
     * @access public
     * @return void
     * @since  1.0.0
     */
    public function meta_box_content() {
        global $post_id;
        $fields     = get_post_custom($post_id);
        $field_data = $this->get_custom_fields_settings();

        $html = '';

        $html .= '<input type="hidden" name="pp-customizer-customizer_' . $this->post_type . '_noonce" id="pp-customizer-customizer_' . $this->post_type . '_noonce" value="' . wp_create_nonce(plugin_basename(dirname(PP_Customizer_Customizer()->plugin_path))) . '" />';

        if (0 < count($field_data)) {
            $html .= '<table class="form-table">' . "\n";
            $html .= '<tbody>' . "\n";

            foreach ($field_data as $k => $v) {
                $data = $v['default'];
                if (isset($fields[ '_' . $k ]) && isset($fields[ '_' . $k ][0])) {
                    $data = $fields[ '_' . $k ][0];
                }

                $html .= '<tr valign="top"><th scope="row"><label for="' . esc_attr($k) . '">' . $v['name'] . '</label></th><td><input name="' . esc_attr($k) . '" type="text" id="' . esc_attr($k) . '" class="regular-text" value="' . esc_attr($data) . '" />' . "\n";
                $html .= '<p class="description">' . $v['description'] . '</p>' . "\n";
                $html .= '</td><tr/>' . "\n";
            }

            $html .= '</tbody>' . "\n";
            $html .= '</table>' . "\n";
        }

        echo $html;
    } // End meta_box_content()

    /**
     * Save meta box fields.
     *
     * @access public
     *
     * @param int $post_id
     *
     * @return void
     * @since  1.0.0
     */
    public function meta_box_save($post_id) {
        global $post, $messages;

        // Verify
        if ((get_post_type() != $this->post_type) || ! wp_verify_nonce($_POST[ 'pp-customizer-customizer_' . $this->post_type . '_noonce' ], plugin_basename(dirname(PP_Customizer_Customizer()->plugin_path)))) {
            return $post_id;
        }

        if (isset($_POST['post_type']) && 'page' == esc_attr($_POST['post_type'])) {
            if ( ! current_user_can('edit_page', $post_id)) {
                return $post_id;
            }
        } else {
            if ( ! current_user_can('edit_post', $post_id)) {
                return $post_id;
            }
        }

        $field_data = $this->get_custom_fields_settings();
        $fields     = array_keys($field_data);

        foreach ($fields as $f) {

            ${$f} = strip_tags(trim($_POST[ $f ]));

            // Escape the URLs.
            if ('url' == $field_data[ $f ]['type']) {
                ${$f} = esc_url(${$f});
            }

            if (get_post_meta($post_id, '_' . $f) == '') {
                add_post_meta($post_id, '_' . $f, ${$f}, true);
            } elseif (${$f} != get_post_meta($post_id, '_' . $f, true)) {
                update_post_meta($post_id, '_' . $f, ${$f});
            } elseif (${$f} == '') {
                delete_post_meta($post_id, '_' . $f, get_post_meta($post_id, '_' . $f, true));
            }
        }
    } // End meta_box_save()

    /**
     * Customise the "Enter title here" text.
     *
     * @access public
     *
     * @param string $title
     *
     * @return void
     * @since  1.0.0
     */
    public function enter_title_here($title) {
        if (get_post_type() == $this->post_type) {
            $title = __('Enter the thing title here', 'pp-customizer-customizer');
        }

        return $title;
    } // End enter_title_here()

    /**
     * Get the settings for the custom fields.
     * @return array
     * @since  1.0.0
     */
    public function get_custom_fields_settings() {
        $fields = array();

        $fields['url'] = array(
            'name'        => __('URL', 'pp-customizer-customizer'),
            'description' => __('Enter a URL that applies to this thing (for example: http://domain.com/).', 'pp-customizer-customizer'),
            'type'        => 'url',
            'default'     => '',
            'section'     => 'info'
        );

        return apply_filters('pp-customizer-customizer_custom_fields_settings', $fields);
    } // End get_custom_fields_settings()

    /**
     * Get the image for the given ID.
     *
     * @param int   $id Post ID.
     * @param mixed $size Image dimension. (default: "thing-thumbnail")
     *
     * @return string        <img> tag.
     * @since  1.0.0
     */
    protected function get_image($id, $size = 'thing-thumbnail') {
        $response = '';

        if (has_post_thumbnail($id)) {
            // If not a string or an array, and not an integer, default to 150x9999.
            if ((is_int($size) || (0 < intval($size))) && ! is_array($size)) {
                $size = array(intval($size), intval($size));
            } elseif ( ! is_string($size) && ! is_array($size)) {
                $size = array(150, 9999);
            }
            $response = get_the_post_thumbnail(intval($id), $size);
        }

        return $response;
    } // End get_image()

    /**
     * Register image sizes.
     * @return void
     * @since  1.0.0
     */
    public function register_image_sizes() {
        if (function_exists('add_image_size')) {
            add_image_size($this->post_type . '-thumbnail', 150, 9999); // 150 pixels wide (and unlimited height)
        }
    } // End register_image_sizes()

    /**
     * Run on activation.
     * @access public
     * @return void
     * @since 1.0.0
     */
    public function activation() {
        $this->flush_rewrite_rules();
    } // End activation()

    /**
     * Flush the rewrite rules
     * @access public
     * @return void
     * @since 1.0.0
     */
    private function flush_rewrite_rules() {
        $this->register_post_type();
        flush_rewrite_rules();
    } // End flush_rewrite_rules()

    /**
     * Ensure that "post-thumbnails" support is available for those themes that don't register it.
     * @return  void
     * @since  1.0.0
     */
    public function ensure_post_thumbnails_support() {
        if ( ! current_theme_supports('post-thumbnails')) {
            add_theme_support('post-thumbnails');
        }
    } // End ensure_post_thumbnails_support()
} // End Class
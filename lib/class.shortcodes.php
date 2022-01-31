<?php

namespace m21;

use Timber\Timber;

/*
* Handles misc shortcodes for the theme. See gallery.php for [gallery] shortcode.
*
* [raw_excerpt]           Output the raw excerpt direct from the DB with no filters applied.
* [wms_javascript] 		  used by: ??
* 				   		  legal attributes: script (must be a key in $js array declared in class.js.php)
*                         example: [wms_javascript script="tablesorter"]
* [code]		   		  used by: acad, and potentially all web documentation sites
* 				   		  legal attributes: none
*                         example: [code]<div>some <b>html</b> goes here</div>[/code]
* [faculty_experts] 	  used by: communications only (one-off)
* 						  legal attributes: any $arg understood by the wordpress tagcloud widget
*                         example: [faculty_experts]
*                         all attributes are optional, as reasonable defaults are set up in the shortcode handler
* [reuse_post]	          used by: wordpress documentation site
* 						  legal attributes: id (post id)
* 											blog_id (optional)
* [quad]				  used in the shortcode builder to make single unit for the 4-block layout.
*						  [quad image="http://.../img/blah.jpg" url="http://foo.com/" overlay="title thingy"]blah blah[/quad]
*					      all attributes (image, url, overlay) are required. text between the open/end shortcode tags, if provided,
*						  will be placed below the image.
*
* [image_html]            Display Wordpress's image tag for an image from the media library.
*                         HTML is retrieved by either ID or URL.
*
*/

class Shortcodes {
    public function __construct() {
        // don't replace quotes n stuff for these shortcodes
        add_filter('no_texturize_shortcodes', array(&$this, 'shortcodes_to_exempt_from_wptexturize'));
        add_filter('the_content', array(&$this, 'shortcode_empty_paragraph_fix'));

        // set up shortcode handlers
        add_shortcode('raw_excerpt', array($this, 'wms_raw_excerpt'));
        add_shortcode('wms_javascript', array(&$this, 'wms_javascript'));
        add_shortcode('code', array(&$this, 'code'));
        add_shortcode('faculty_experts', array(&$this, 'faculty_experts'));
        add_shortcode('reuse_post', array(&$this, 'reuse_post'));
        add_shortcode('quad', array(&$this, 'quad'));
        add_shortcode('quads', array(&$this, 'quad_group'));
        add_shortcode('home_btn', array(&$this, 'home_btn'));
        add_shortcode('image_html', array(&$this, 'image_html'));
    }

    function shortcodes_to_exempt_from_wptexturize($shortcodes) {
        $meerkat_shortcodes = array(
            'wms_javascript',
            'code',
            'faculty_experts',
            'reuse_post',
            'quad',
            'quads'
        );
        foreach ($meerkat_shortcodes as $sc) {
            array_push($shortcodes, $sc);
        }

        return $shortcodes;
    }

    function shortcode_empty_paragraph_fix($content) {
        // list the shortcodes to filter, '' filters all shortcodes
        $shortcodes = array('');

        foreach ($shortcodes as $shortcode) {
            $array = array(
                '<p>[' . $shortcode    => '[' . $shortcode,
                '<p>[/' . $shortcode   => '[/' . $shortcode,
                $shortcode . ']</p>'   => $shortcode . ']',
                $shortcode . ']<br />' => $shortcode . ']'
            );

            $content = strtr($content, $array);
        }

        return $content;
    }

    /**
     * Output the raw excerpt direct from the DB with no filters applied.
     *
     * @return string
     */
    function wms_raw_excerpt() {
        global $post;

        return $post->post_excerpt;
    }

    /**
     * Provides a container to group related expandos together
     *
     * @param      $atts
     * @param null $content
     */
    function quad_group($atts, $content = null) {
        return '<div class="quad-container">' . do_shortcode($content) . '</div>';
    }

    function quad($attr, $content) {
        // build a single pic/title/blurb unit for the quad layout
        $atts = shortcode_atts(array(
            'image-id'  => '',
            'image'     => '',  // absolute or relative URL of the img src
            'url'       => '',    // absolute or relative URL of the clickthrough destination
            'overlay'   => '', // text to place on top of the image - keep it short
            'ratio_4_3' => 'false'
        ), $attr);

        if ( ! $atts['image'] || ! $atts['overlay']) {
            return false;
        }

        if ( ! $atts['image-id']) {
            $id = $this->get_attachment_id($atts['image']);
        } else {
            $id = $atts['image-id'];
        }

        $orientation = 'landscape';
        if ($meta = wp_get_attachment_metadata($id, true)) {
            $ratio = $meta['width'] / $meta['height']; // width/height
            if ($ratio < 1) {
                $orientation = 'portrait';
            }
        }

        $ratio_4_3 = $atts['ratio_4_3'] === 'true' ? 'ratio_4_3' : '';

        if ( ! $content) {
            // caption shortcode won't work if there's no actual caption. let's fake it.
            $content = '&nbsp;';
        }

        return Timber::fetch('modules/image.twig', array(
            'id'          => $id,
            'content'     => $content,
            'class'       => 'quad',
            'width'       => $atts['width'],
            'height'      => $atts['height'],
            'src'         => $atts['image'],
            'url'         => $atts['url'],
            'overlay'     => $atts['overlay'],
            'ratio_4_3'   => $ratio_4_3,
            'orientation' => $orientation
        ));
    }

    /**
     * Get an attachment ID given a URL.
     *
     * @param string $url
     *
     * @return int Attachment ID on success, 0 on failure
     */
    function get_attachment_id($url) {
        $attachment_id = 0;
        $dir           = wp_upload_dir();
        if (false !== strpos($url, $dir['baseurl'] . '/')) { // Is URL in uploads directory?
            $file       = basename($url);
            $query_args = array(
                'post_type'   => 'attachment',
                'post_status' => 'inherit',
                'fields'      => 'ids',
                'meta_query'  => array(
                    'relation' => 'OR',
                    array(
                        'value'   => $file,
                        'compare' => 'LIKE',
                        'key'     => '_wp_attachment_metadata',
                    ),
                    array(
                        'value'   => $file,
                        'compare' => 'LIKE',
                        'key'     => '_wp_attachment_backup_sizes',
                    ),
                )
            );
            $query      = new \WP_Query($query_args);
            if ($query->have_posts()) {
                foreach ($query->posts as $post_id) {
                    $meta                = wp_get_attachment_metadata($post_id);
                    $original_file       = basename($meta['file']);
                    $cropped_image_files = wp_list_pluck($meta['sizes'], 'file');
                    if ($original_file === $file || in_array($file, $cropped_image_files)) {
                        $attachment_id = $post_id;
                        break;
                    }
                }
            }
        }

        return $attachment_id;
    }

    function wms_javascript($atts) {
        // allows a per page js lib load via shortcode: [wms_javascript script="tablesorter"]
        global $js;
        extract(shortcode_atts(array('script' => ''), $atts));
        if ($script && $js[ $script ]) {
            foreach ($js[ $script ]['deps'] as $dep) {
                $dep = str_replace(get_template() . '-', '', $dep);
                if ($js[ $dep ] && ! $js[ $dep ]['load']) {
                    $js[ $dep ]['load'] = true;
                }
            }
            $js[ $script ]['load'] = true;
        }
    }

    function build_edit_button($post_id, $label = 'Edit') {
        $html     = '';
        $edit_url = get_edit_post_link($post_id);
        if ($edit_url) {
            $html = '<a class="edit-me" href="' . $edit_url . '">' . $label . '</a>';
        }

        return $html;
    }

    function reuse_post($atts) {
        $defaults = array(
            'id'      => '',
            'blog_id' => '',
            'edit'    => 'yes'
        );

        // Embed the content of a page/post into this page/post
        $atts = shortcode_atts($defaults, $atts);

        $current_blog_id = get_current_blog_id();

        // Make sure we have some sort of url to work with
        if ( ! $atts['id'] || ! is_numeric($atts['id'])) {
            return '<code>Invalid post ID</code>';
        }

        if ($atts['blog_id']) {
            if (is_numeric($atts['blog_id'])) {
                switch_to_blog($atts['blog_id']);
            } else {
                return '<code>Invalid blog ID</code>';
            }
        }

        $post    = get_post($atts['id']);
        $content = $post->post_content;

        // Interpret shortcodes, do oembed, formatting, etc.
        $content = apply_filters('the_content', $content);
        $content = apply_filters('after_reuse_post', $content);

        $edit_button = $atts['edit'] !== 'no' ? $this->build_edit_button($atts['id'], 'Edit Snippet') : '';

        if ($atts['blog_id']) {
            switch_to_blog($current_blog_id);
        }

        return '<div class="reused-post cf">' . $content . $edit_button . '</div>';
    }

    function code($atts, $content) {
        // allows you to display html code inside a page: [code]<div>some <b>html</b> goes here</div>[/code]
        $content = str_replace('<p>', '', $content);
        $content = str_replace('</p>', '', $content);
        $content = str_replace('<br />', '', $content);
        $html    = '<code>';
        $bits    = explode("\n", $content);
        foreach ($bits as $line) {
            $encoded = htmlspecialchars($line);
            if ($encoded != '') {
                $html .= $encoded . '<br>';
            }
        }
        $html .= '</code>';

        return $html;
    }

    function faculty_experts($atts) {
        // create a tag cloud tuned for the faculty experts on communications sites
        // specify defaults for shortcode attributes
        $defaults       = array(
            'smallest'                  => 14,
            'largest'                   => 30,
            'unit'                      => 'px',
            'number'                    => 9999,
            'format'                    => 'flat',
            'separator'                 => "\n",
            'orderby'                   => 'name',
            'order'                     => 'ASC',
            'exclude'                   => null,
            'include'                   => null,
            'topic_count_text_callback' => 'default_topic_count_text',
            'link'                      => 'view',
            'taxonomy'                  => 'post_tag',
            'echo'                      => false,
            'category'                  => ''
        );
        $shortcode_atts = shortcode_atts($defaults, $atts);
        if ($shortcode_atts['category']) {
            // Category is specified, convert it to list of tags to include.
            if (is_numeric($shortcode_atts['category'])) {
                $cat_id = $shortcode_atts['category'];
            } else {
                $catObj = get_category_by_slug($shortcode_atts['category']);
                if ($catObj) {
                    $cat_id = $catObj ? $catObj->term_id : false;
                }
            }
            if ($cat_id) {
                $custom_query = new WP_Query("posts_per_page=-1&cat={$cat_id}");
                if ($custom_query->have_posts()) :
                    while ($custom_query->have_posts()) : $custom_query->the_post();
                        $posttags = get_the_tags();
                        if ($posttags) {
                            foreach ($posttags as $tag) {
                                $all_tags[] = $tag->term_id;
                            }
                        }
                    endwhile;
                endif;
                $tags_arr                  = array_unique($all_tags);
                $shortcode_atts['include'] = implode(",", $tags_arr);
            }
        }

        $tag_cloud = wp_tag_cloud($shortcode_atts);

        return '<div id="faculty-experts-tagcloud">' . $tag_cloud . '</div>';
    }

    //adds a button in the home page style
    function home_btn($atts) {

        $atts = shortcode_atts(array(
            'class'     => '',
            'color'     => '#3c2151',
            'text'      => 'no text provided',
            'link'      => '#',
            'max_width' => '100%'
        ), $atts, 'home_btn');

        return <<<EOD
            <a class="home-btn {$atts['class']}" href="{$atts['link']}" style="color:{$atts['color']}; max-width:{$atts['max_width']};">{$atts['text']}</a>
EOD;

    }

    /**
     * Turn an image URL into Wordpress-generated <img ...> tag
     * (It would be nicer to do this by image ID, but this was developed for
     * Beaver Builder Themer and the wpbb shortcode doesn't seem to return
     * an ACF image field by ID, only by URL.)
     *
     * Shortcode params
     *
     * @param int       $image_id    Valid ID of an image attachment
     * @param string    $image_url   Valid URL of an image attachment
     * @param string    $size        Valid image size; defaults to 'thumbnail'
     *
     */
    function image_html($atts) {
        // normalize attribute keys, lowercase
        $atts = array_change_key_case( (array) $atts, CASE_LOWER );
     
        // override default attributes with user attributes
        $image_atts = shortcode_atts(
            array( 'image_id'=>0, 'image_url'=>'', 'size'=>'thumbnail'), 
            $atts
        );

        // Prioritize ID because that seems more correct
        if (is_numeric($image_atts['image_id']) && $image_atts['image_id'] > 0) {
            // check that image exists
            if ($image_html = wp_get_attachment_image($image_atts['image_id'], $image_atts['size'])) {
                return $image_html;
            }
        } else if ($image_atts['image_url']) {
            if ($image_id = attachment_url_to_postid($image_atts['image_url'])) {
                return wp_get_attachment_image($image_id, $image_atts['size']);
            }
        }
        // do nothing if a valid attachment URL is not present
        return '';
    }

}

new Shortcodes();

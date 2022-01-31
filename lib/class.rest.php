<?php

namespace m21;

/**
 * Class Rest
 * @package m21
 *
 * @uses \WP_REST_Server
 * @uses \WP_REST_Request
 * @uses \Timber\PostQuery
 */

class Rest {
    // Namespace
    const NS = 'wms';

    public function __construct() {
        add_action('rest_api_init', function() {
            $args = array(
                'page'          => array( // which page of results
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric($param);
                    }
                ),
                'per_page'      => array( // max results to return per page
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric($param);
                    }
                ),
                'meta_key'      => array( // custom field key
                    'validate_callback' => function($param, $request, $key) {
                        return is_string($param);
                    }
                ),
                'meta_value'    => array( // custom field value
                    'validate_callback' => function($param, $request, $key) {
                        return is_string($param) || is_numeric($param);
                    }
                ),
                'ignore_sticky' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_bool($param);
                    }
                )
            );

            register_rest_route(self::NS, '/list/tax/(?P<tax_slug>[a-zA-Z0-9-_]+)/term/(?P<term_id>[a-zA-Z0-9-_]+)', array(
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => array($this, 'by_tax'),
                'args'                => $args,
                'permission_callback' => '__return_true'
            ));

        });
    }


    /**
     * Filter by specified taxonomy and term_id.
     * Uses \Timber\Image to add post featured image.
     *
     * @param \WP_REST_Request $request
     *
     * @return array|bool
     *
     * Example twig
        <div>
            {% for post in posts %}
                <article>
                    <h3>{{ post.title }}</h3>
                    <p>{{ post.post_excerpt }}</p>
                    {% if post.thumbnail %}
                        <img src="{{ post.thumbnail.file_loc | resize(262, 194) }}" alt="{{ post.thumbnail._wp_attachment_image_alt }}">
                    {% endif %}
                </article>
            {% endfor %}
        </div>
     **/
    public function by_tax(\WP_REST_Request $request) {
        $args['tax_query'] = array();

        // Build taxonomy query if any
        if (isset($request['tax_slug']) && isset($request['term_id'])) {
            if ($request['term_id'] === 'all') {
                array_push($args['tax_query'], array(
                    'taxonomy' => $request['tax_slug'],
                    'operator' => 'EXISTS'
                ));
            } else {
                array_push($args['tax_query'], array(
                    'taxonomy' => $request['tax_slug'],
                    'field'    => is_numeric($request['term_id']) ? 'term_id' : 'slug',
                    'terms'    => $request['term_id'],
                ));
            }
        }

        $args = wp_parse_args($args, array(
            'post_type'           => 'any',
            'paged'               => $request->get_param('page'),
            'posts_per_page'      => $request->get_param('per_page'),
            'meta_key'            => $request->get_param('meta_key'),
            'meta_value'          => $request->get_param('meta_value'),
            'featured'            => $request->get_param('featured'),
            'ignore_sticky_posts' => $request->get_param('ignore_sticky') ? $request->get_param('ignore_sticky') : true
        ));

        $posts = new \Timber\PostQuery($args);

        $posts_w_custom_meta = $posts->get_posts();
        foreach($posts_w_custom_meta as &$post){
            $post->{'thumbnail'} = $post->thumbnail;
            $post->{'link'} = $post->link;
        }

        return $posts_w_custom_meta;
    }
}

new Rest();

<?php

namespace m21;

class Ctd_Events_Helper {
    private static $instance;

    protected function __construct() {

    }

    /**
     * @param $args
     *
     * @return null|array
     */
    public static function get_group_posts($args) {
        // Attributes
        $args = wp_parse_args(
            $args,
            array(
                'title'      => '',
                'class'      => '',
                'endpoint'   => '',
                'hide_empty' => true,
                'headers'    => true,
                'combine'    => true,
                'expanded'   => false,
                'details'    => true
            )
        );

        $class = $args['title'] ? $args['title'] : $args['cat'];

        if ($class) {
            $args['class'] = sanitize_key($class);
        }

        $posts = self::get_rest_response($args['endpoint']);

        if (( ! is_array($posts) || ! count($posts)) && $args['hide_empty']) {
            return null;
        } else {
            return array('args' => $args, 'posts' => $posts);
        }
    }

    /**
     * Holds events in correct order, FIFO. Allows stacking of
     * recurrent event details in one container - based on title - instead of multiple instances.
     * Bit of a kludge to determine the title link:
     * If event is recurring, use recurrence_url.
     * If event is singular, use event_url.
     * If multiple singular events share the same name (such as Music Ensembles), do not link title.
     *
     * @param $posts
     * @param $args
     *
     * @throws Exception
     */
    public static function organize_group($group) {
        list('args' => $args, 'posts' => $posts) = $group;

        $events = array();
        foreach ($posts as $post) {
            $before = $args['expanded'] ? $post->before_title : null;
            $after  = $args['expanded'] ? $post->after_title : null;
            $key    = '';

            // Store combined events under their common title and push individual details below.
            if ($args['combine'] && ! $key = array_search($post->post_title, array_column($events, 'title'))) {
                $event = array(
                    /* recurrence_url = all; event_url = single */
                    'title_url'           => $post->recurring ? $post->recurrence_url : $post->event_url,
                    'title'               => $post->post_title,
                    'img'                 => $post->img,
                    'before'              => $before,
                    'after'               => $after,
                    'integrated_parent'   => $post->integrated_parent,
                    'integrated_children' => $post->integrated_children,
                    'details'             => array(),
                );
                $count = array_push($events, $event);
                // Convert count to index.
                $key = $count - 1;
            } elseif ( ! $args['combine']) {
                $event = array(
                    'title_url'           => $post->event_url, // single
                    'title'               => $post->post_title,
                    'img'                 => $post->img,
                    'before'              => $before,
                    'after'               => $after,
                    'integrated_parent'   => $post->integrated_parent,
                    'integrated_children' => $post->integrated_children,
                    'details'             => array()
                );
            }

            if ($args['details']) {
                $details = array();

                $details['detail_url'] = $post->event_url; // single

                $eventStart = new DateTime($post->StartDateTime);
                $now        = new DateTime();

                $details['daymonth'] = $eventStart->format('D M');
                $details['date']     = intval($eventStart->format('d'));

                if ($eventStart > $now) {
                    $details['time'] = $eventStart->format('g:i A');
                }

                $details['venue'] = $post->venue;
                $details['room']  = $post->venue_room;

                if ($args['combine']) {
                    array_push($events[ $key ]['details'], $details);
                } else {
                    array_push($event['details'], $details);
                    array_push($events, $event);
                }
            }
        }

        return $events;
    }

    public static function get_rest_response($endpoint) {
        //console_log($endpoint);
        $response = wp_remote_get($endpoint, array('timeout' => 10000));
        if (is_wp_error($response)) {
            return null;
        }

        return json_decode(wp_remote_retrieve_body($response));

    }

    /**
     * Returns the singleton instance of this class.
     *
     * @return Ctd_Events_Widget The singleton instance.
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
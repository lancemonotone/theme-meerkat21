<?php

namespace m21;

/**
 * Class Ctd_Events_Shortcode
 *
 * It's a shame that this file has to exist, because it
 * binds the Events theme to CTD in the wrong direction.
 * However, there does not appear to be a better way to
 * hook a non-events theme via AJAX into the Tribe functionality to allow our custom
 * event types and other taxonomies, with all their associated
 * knock-on effects to links and flow logic.
 */
class Ctd_Events_Shortcode {
    private static $instance;
    // Allows us to add arbitrary taxonomy (or other info to [tribe_events] query)
    public static $tribe_events_shortcode_atts = array();
    public static $DEPT_CONST = 'event_departments';
    public static $original_site;

    protected function __construct() {
        //add_action('tribe_events_month_get_events_in_month', array(&$this, 'tribe_events_month_get_events_in_month'), 20, 3);
        $this->register_hooks();
        //add_action('wp_ajax_tribe_calendar', array(&$this, 'register_hooks'), 20);
        //add_action('wp_ajax_nopriv_tribe_calendar', array(&$this, 'register_hooks'), 20);
    }

    public function register_hooks() {
        // This one does the heavy lifting. It overwrites the tribe query and takes into account our
        // custom taxonomies.
        add_action('tribe_events_month_get_events_in_month', array(&$this, 'tribe_events_month_get_events_in_month'), 20, 3);
        // Rerun the whole f'in tribe_get_events() because the ajax calendar query doesn't have any hooks to modify it
        //add_filter('tribe_get_events', array(&$this, 'ajax_tribe_get_events'), 20, 3);
        // Fixes the URLs for dates and 'view all' links in the grid calendar when shortcode is used.
        add_filter('tribe_events_get_current_month_day', array(&$this, 'fix_view_more_link'), 20, 1);
        // Fixes the grid calendar date link, should lead back to the events site showing the correct category (music, ctd, ?)
        add_filter('tribe_get_day_link', array(&$this, 'fix_date_link'), 20, 2);
        // The next two filters add our custom taxonomies to the prev/next month links (loaded via ajax)
        add_filter('tribe_get_next_month_link', array(&$this, 'fix_month_link'), 20, 1);
        add_filter('tribe_get_previous_month_link', array(&$this, 'fix_month_link'), 20, 1);
        // Fixes grid calendar shortcode [tribe_events], which doesn't recognize custom taxonomies
        add_filter('shortcode_atts_tribe_events', array(&$this, 'filter_tribe_events_shortcode'), 20, 4);
        //add_action('tribe_events_pro_tribe_events_shortcode_pre_render', array(&$this, 'switch_to_events'));
        add_action('tribe_events_before_view', array(&$this, 'switch_to_events'), 5);
        //add_action('tribe_events_pro_tribe_events_shortcode_pre_render', array(&$this, 'switch_to_events'));
        add_action('tribe_events_after_view', array(&$this, 'switch_from_events'), 5);
        // Need to add the current department to the header or the date picker will return empty
        add_filter('tribe_events_header_attributes', array(&$this, 'add_dept_to_header_attributes'), 10, 2);
        add_action('wp_print_footer_scripts', array(&$this, 'add_dept_to_canonical_link'), 10, 2);
    }

    /**
     * @param $out
     * @param $pairs
     * @param $atts
     * @param $shortcode
     *
     * @return mixed
     */
    static function switch_to_events($obj) {
        //global $post;
        self::$original_site = get_current_blog_id();
        if (self::$original_site !== Ctd_Events::$events_id/* && $post->post_name === 'calendar'*/) {
            switch_to_blog(Ctd_Events::$events_id);
        }

        //return $obj;
    }

    static function switch_from_events() {
        //global $post;
        if ( ! empty(self::$original_site)/* && $post->post_name === 'calendar'*/) {
            switch_to_blog(self::$original_site);
        }
    }


    public function ajax_tribe_get_events($result, $args, $full) {

        return $result;
    }

    /**
     * Hook into Tribe shortcode preparation and store taxonomy for future use.
     *
     * @param $out
     * @param $pairs
     * @param $atts
     * @param $shortcode
     *
     * @return mixed
     * @todo $taxonomy needs to be an array
     *
     */
    public function filter_tribe_events_shortcode($out, $pairs, $atts, $shortcode) {
        if ($shortcode !== 'tribe_events') return $out;

        if (isset($atts['taxonomy'])) {
            $out['taxonomy'] = $atts['taxonomy'];
        }

        if (isset($atts['depts'])) {
            $out['depts'] = self::sanitize_depts($atts['depts']);
        }

        if (isset($atts['category'])) {
            $out['category'] = $atts['category'];
        }

        // Reset to prevent unintended consequences
        self::$tribe_events_shortcode_atts = $out;

        return $out;
    }

    /**
     * @return string
     */
    public static function sanitize_depts(string $dept_list) {
        $depts = array();

        foreach (explode(',', $dept_list) as $el) {
            $endpoint = Ctd_Events::$rest_api . 'get/tax/' . self::$DEPT_CONST . '/term/' . $el;
            $term     = Ctd_Events_Helper::get_rest_response($endpoint);
            array_push($depts, $term->term_taxonomy_id);
        }

        return implode(',', $depts);
    }

    /**
     * @param $null null
     * @param $start_date string
     * @param $end_date string
     *
     * @return mixed
     *
     * Inject stored taxonomy into shortcode query.
     *
     * Cobbled together from \Tribe__Events__Template__Month::set_events_in_month
     *
     */
    public function tribe_events_month_get_events_in_month($null, $start_date, $end_date) {
        // Check to make sure we've already initialized [tribe_events] shortcode.
        if ( ! empty($_REQUEST['action']) && $_REQUEST['action'] === 'tribe_calendar') {
            if ( ! empty($_REQUEST['taxonomy'])) {
                // Ajax payload from clicking next/prev month links gives us
                $taxonomy = self::$tribe_events_shortcode_atts['taxonomy'] = $_REQUEST['taxonomy'];
            }
            if ( ! empty($_REQUEST['depts'])) {
                // Explode the shortcode department array using the events calendar events_department tax/terms
                $depts = self::$tribe_events_shortcode_atts['depts'] = $_REQUEST['depts'];
            }
        } else if ( ! empty(self::$tribe_events_shortcode_atts['depts']) || ! empty(self::$tribe_events_shortcode_atts['taxonomy'])) {
            if ( ! empty(self::$tribe_events_shortcode_atts['taxonomy'])) {
                $taxonomy = self::$tribe_events_shortcode_atts['taxonomy'];
            }
            if ( ! empty(self::$tribe_events_shortcode_atts['depts'])) {
                $depts = self::$tribe_events_shortcode_atts['depts'];
            }
        }

        // If there's nothing to query on, this is a regular grid request, so bail.
        if (empty($taxonomy) && empty($depts)) {
            return null;
        }

        switch_to_blog(Ctd_Events::$events_id);

        global $wpdb;

        $taxonomy_clause = empty($taxonomy) ? 0 : <<< EOD
                EXISTS(
                    SELECT 1
                    FROM $wpdb->term_relationships
                    INNER JOIN $wpdb->term_taxonomy ON $wpdb->term_taxonomy.term_taxonomy_id = $wpdb->term_relationships.term_taxonomy_id
                    WHERE $wpdb->term_taxonomy.taxonomy = '$taxonomy' AND $wpdb->term_relationships.object_id = wp_247_posts.ID
                )
EOD;
        $depts_clause    = empty($depts) ? 0 : <<< EOD
                tr.term_taxonomy_id IN ($depts)
EOD;

        // @todo make this query work with an array of taxonomies, terms and/or categories
        //$taxonomy = !empty($taxonomy) ? explode(',', $taxonomy) : '';

        $post_stati = array('publish');
        if (is_user_logged_in()) {
            $post_stati[] = 'private';
        }

        $post_stati = implode("','", $post_stati);

        // Despite the method name, this obtains a list of post IDs to be hidden from *all* event listings
        $ignore_events = Tribe__Events__Query::getHideFromUpcomingEvents();

        // If it is empty we don't need to do anything further
        if (empty($ignore_events)) {
            return '';
        }

        // Let's ensure they are all absolute integers then collapse into a string
        $ignore_events = implode(',', array_map('absint', $ignore_events));

        // Terminate with AND so it can easily be combined with the rest of the WHERE clause
        $ignore_hidden_events_AND = " $wpdb->posts.ID NOT IN ( $ignore_events ) AND ";

        $start_date_sql = esc_sql($start_date);
        $end_date_sql   = esc_sql($end_date);

        $events_request = /** @lang MySQL */
            <<< EOD
        SELECT DISTINCT tribe_event_start.post_id as ID,
            tribe_event_start.meta_value as EventStartDate,
            tribe_event_end_date.meta_value as EventEndDate
        FROM $wpdb->postmeta AS tribe_event_start
        LEFT JOIN $wpdb->posts ON tribe_event_start.post_id = $wpdb->posts.ID
        LEFT JOIN $wpdb->postmeta AS tribe_event_end_date ON ( tribe_event_start.post_id = tribe_event_end_date.post_id AND tribe_event_end_date.meta_key = '_EventEndDate' )
        LEFT JOIN $wpdb->term_relationships AS tr ON (tribe_event_start.post_id = tr.object_id) 
        WHERE $ignore_hidden_events_AND tribe_event_start.meta_key = '_EventStartDate'
        AND (
            (
                tribe_event_start.meta_value >= '{$start_date_sql}'
                AND tribe_event_start.meta_value <= '{$end_date_sql}'
            )
            OR (
                tribe_event_end_date.meta_value >= '{$start_date_sql}'
                AND tribe_event_end_date.meta_value <= '{$end_date_sql}'
            )
            OR (
                tribe_event_start.meta_value < '{$start_date_sql}'
                AND tribe_event_end_date.meta_value > '{$end_date_sql}'
            )
        )
        AND $wpdb->posts.post_status IN('$post_stati')
        AND (
            $taxonomy_clause
            OR (
                $depts_clause
            )
        )
        ORDER BY $wpdb->posts.menu_order ASC, DATE(tribe_event_start.meta_value) ASC, TIME(tribe_event_start.meta_value) ASC;
					
EOD;

        $events_in_month = $wpdb->get_results($events_request);

        // Reset to avoid unintended consequences.
        //self::$tribe_events_shortcode_atts = array();

        restore_current_blog();

        return $events_in_month;
    }

    // Fixes the URLs for dates and 'view all' links in the grid calendar when ajax is used
    // There is another version of this in CTD functions.php for shortcode calls
    public function fix_view_more_link($day) {
        if ( ! empty($day['view_more'])) {
            $parts            = parse_url($day['view_more']);
            $segments         = array_filter(explode('/', $parts['path']));
            $segments         = implode('/', $segments);
            $server           = Wms_Server::instance()->server ? Wms_Server::instance()->server . '.' : '';
            $link             = Wms_Server::instance()->protocol . "events." . $server . Wms_Server::instance()->domain . '/' . $segments . '/?tribe_event_display=day&tribe-bar-date=' . $day['date'];
            $day['view_more'] = $link;
        }

        return $day;
    }

    public function add_dept_to_header_attributes($attrs, $current_view) {
        $attrs['data-depts'] = self::$tribe_events_shortcode_atts['depts'];
        $attrs['data-taxonomy'] = self::$tribe_events_shortcode_atts['taxonomy'];

        return $attrs;
    }

    public function add_dept_to_canonical_link() {
        ?>
        <script>
          "use strict";
          (function ( $ ) {
            add_depts();

            function add_depts() {
              const $header = $( document.getElementById( 'tribe-events-header' ) );
              if ( $header.length ) {
                const $canonical = $( 'link[rel="canonical"]' );
                let url = $canonical.attr( 'href' );

                let $depts = $header.data( 'depts' );
                let $taxonomy = $header.data( 'taxonomy' );
                if ( $depts ) {
                  url += '&depts=' + $depts;
                }
                if($taxonomy){
                  url += '&taxonomy=' + $taxonomy;
                }
                  tribe_ev.fn.update_base_url( url );
              }
            }
          })( jQuery );
        </script>
        <?php
    }

    /**
     * Adds our custom taxonomy to the next/prev month links
     * in [tribe_events view="month"] grid calendar (possibly others, not tested).
     *
     * @param $output
     *
     * @return string
     */
    public function fix_month_link($output) {
        $output = add_query_arg('taxonomy', self::$tribe_events_shortcode_atts['taxonomy'], $output);
        $output = add_query_arg('depts', self::$tribe_events_shortcode_atts['depts'], $output);

        // Remove extra eventDate
        $output = remove_query_arg('eventDate', $output);

        //$output = add_query_arg('category', self::$tribe_events_shortcode_atts['category'], $output);

        return $output;
    }

    // Fixes the URLs for dates and 'view all' links in the grid calendar when shortcode is used.
    // There is another version of this in M16 Events lib/class.ctd.php for ajax calls.
    public function fix_date_link($link, $date) {
        $link1 = $link2 = '';
        // Right for index?
        /*if ( ! empty($date)) {
            $parts    = parse_url($link);
            $segments = array_filter(explode('/', $parts['path']));
            array_pop($segments);
            $segments = count($segments) ? '/' . implode('/', $segments) : '';
            $server   = Wms_Server::instance()->server ? Wms_Server::instance()->server . '.' : '';
            $link1    = Wms_Server::instance()->protocol . "events." . $server . Wms_Server::instance()->domain . $segments . '/?tribe_event_display=day&tribe-bar-date=' . $date;
        }*/

        //Right for ajax?
        if ( ! empty($date)) {
            $parts = parse_url($link);
            parse_str($parts['query'], $query);
            $segments = array_filter(explode('/', $parts['path']));
            array_pop($segments);
            $segments = implode('/', $segments);
            $server   = Wms_Server::instance()->server ? Wms_Server::instance()->server . '.' : '';
            $link2    = Wms_Server::instance()->protocol . "events." . $server . Wms_Server::instance()->domain . '/' . $segments . '/?tribe_event_display=day&tribe-bar-date=' . $date;
        }

        return $link2 ? $link2 : ($link1 ? $link1 : $link);
    }

    /**
     * Returns the singleton instance of this class.
     *
     * @return Ctd_Events_Shortcode The singleton instance.
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

Ctd_Events_Shortcode::instance();
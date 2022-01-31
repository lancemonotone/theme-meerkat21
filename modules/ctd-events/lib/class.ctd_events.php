<?php

namespace m21;

class Ctd_Events {
    public static $events_id = 247;
    public static $rest_api;

    private static $instance;
    private static $current_season;
    private static $request_season;
    private static $season;
    private static $group = 'month';
    private static $details = 'y';
    private static $hide_empty = 'y';
    private static $hide_season_header = 'y';
    private static $hide_widget_title = 'n';
    private static $display_images = 'n';
    private static $headers = 'y';
    private static $combine = 'y';
    private static $expanded = 'n';
    private static $per_page = 200;
    private static $start_month = 7; // inclusive
    private static $end_month = 7; // exclusive
    private static $earliest_season = '2005-06';
    private static $num_days;
    private static $featured = 'n';
    private static $cat = 'all';
    private static $user_logged_in; // hide private events if user not logged in
    private static $site;
    private static $original_site;
    private static $sites = array(
        '186' => array(
            'name'   => "'62 Center",
            'slug'   => "62_center",
            'venues' => array(/*'3060' => "'62 Center for Theater & Dance",
                '2939' => "MainStage, '62 Center",
                '2817' => "Adams Memorial Theatre, '62 Center",
                '2843' => "CenterStage, '62 Center"*/
            )
        ),
        '196' => array(
            'name'   => "Music Department",
            'slug'   => 'music',
            'venues' => array()
        ),
        '244' => array(
            'name'   => "STUDIO'62",
            'slug'   => 'studio62',
            'venues' => array()
        )
    );
    private static $defaults = array(
        'hide_season_header',
        'hide_widget_title',
        'display_images',
        'group',
        'tax',
        'cat',
        'exclude_terms',
        'headers',
        'combine',
        'expanded',
        'featured',
        'per_page',
        'start_date',
        'end_date',
        'num_days',
        'spinner',
        'target',
        'func',
        'season',
        'url',
        'depts',
        'venues',
        'per_page',
        'num_days',
        'details',
        'endpoint',
        'title',
    );

    protected function __construct() {
        \Timber\Timber::$locations = array_merge((array) \Timber\Timber::$locations, array(get_template_directory() . '/modules/ctd-events/views'));

        add_shortcode('wms_eventslist', array(&$this, 'get_season'));
        add_shortcode('get_season_by_cat', array(&$this, 'get_season'));

        add_action('wp_ajax_nopriv_ajax_refresh', array(&$this, 'ajax_refresh'));
        add_action('wp_ajax_ajax_refresh', array(&$this, 'ajax_refresh'));

        $current_blog = strval(get_current_blog_id());
        self::$site   = ! empty(self::$sites[ $current_blog ]) ? self::$sites[ $current_blog ] : null;

        self::$rest_api = get_site_url(self::$events_id) . "/wp-json/wms/events/v1/";

        self::$user_logged_in = is_user_logged_in();
    }

    public static function get_all_season_years() {
        $season_years = array();

        $earliest_season = self::$earliest_season;
        $current_season  = self::get_current_season();

        $earliest_year = intval(explode('-', $earliest_season)[0]);
        $current_year  = intval(explode('-', $current_season)[0]);

        $count = $current_year - $earliest_year + 1; // add one for current season
        for ($i = 0; $i < $count; $i++) {
            $this_year = $earliest_year + $i;
            $next_year = substr(strval($this_year + 1), 2);
            array_push($season_years, strval($this_year) . '-' . $next_year);
        }

        return array_reverse($season_years);
    }

    /**
     * @param $args
     * @param $content (not used)
     * @param $tag
     *
     * @return bool|string
     * @throws Exception
     * @todo Update these instructions
     *  You can pass any parameter to override the default value.
     *
     *  $args['url']        (string)    Base rest api URL
     *  $args['depts']      (string)    Joined url_encoded string of dept id or slug, matching any of these taxonomies:
     *                                  'tribe_events_cat', 'event_category' or 'event_departments' ('&depts[]=314&depts[]=62_center)
     *  $args['venues']     (string)    Joined url_encoded string of venue ids (&venues[]=844&venues[]=347)
     *  $args['per_page']   (int)       How many results to return in one call
     *  $args['details']    (bool)      Display date and venue information
     *
     */
    public static function get_season($args, $content = '', $tag = '') {
        // Convert $args from Obj to Arr and unset empty elements for sanity.
        $args = array_filter((array) $args, function($a) {
            return $a !== "";
        });

        $args['season'] = self::init_season($args);

        // Backpat with existing but clunky shortcode
        if ($tag === 'get_season_by_cat') {
            $args['group'] = 'list';
        }

        $season = self::get_season_by($args);

        // Delete empty array elements for sanity
        $args = array_filter(array_intersect_key($args, array_flip(self::$defaults)));

        // Allow administrators and editors to flush the transient via front-end button.
        // Look in meerkat-ctd/main.js for relevant script.
        $id      = 'season-event-list';
        $hash    = uniqid();
        $options = array(
            'spinner' => esc_attr(admin_url('images/loading.gif')),
            'target'  => $id . '-' . $hash,
            'season'  => self::$request_season,
        );

        $options = wp_parse_args($options, $args);

        $button_data = self::get_button_data($options);

        $widget_args = array(
            'title'              => $args['title'],
            'hide_season_header' => $args['hide_season_header'],
            'hide_widget_title'  => $args['hide_widget_title'],
            'display_images'     => $args['display_images'],
            'button_data'        => $button_data,
            'id'                 => $id,
            'hash'               => $hash,
            'season'             => $season,
            'request_season'     => isset($_REQUEST['season']) ? $_REQUEST['season'] : ''
        );

        $groups     = array();
        $jump_links = array();
        foreach ((array) $season as $group_args) {
            if ($group = Ctd_Events_Helper::get_group_posts($group_args)) {
                array_push($jump_links, array(
                        'class' => $group['args']['class'],
                        'title' => $group['args']['title'])
                );
                if ($events = Ctd_Events_Helper::organize_group($group)) {
                    array_push($groups, array('args' => $group['args'], 'events' => $events));
                }
            }
        }

        $context = array_merge(
            array(
                'season'      => self::$request_season,
                'args'        => $widget_args,
                'groups'      => $groups,
                'all_seasons' => self::get_all_season_years(),
                'jump_links'  => $jump_links
            )
        );

        $out = \Timber\Timber::fetch('ctd-events.twig', $context);

        return $out;
    }

    /**
     * @param $args
     *
     * @return array|null
     */
    public static function get_season_by($args) {
        $args = wp_parse_args($args, array(
            'url'                => '',
            'endpoint'           => '',
            'title'              => '',
            'depts'              => self::$site['depts'],
            'venues'             => self::$site['venues'],
            'cat'                => self::$cat,
            'exclude_terms'      => '',
            'per_page'           => self::$per_page,
            'num_days'           => self::$num_days,
            'details'            => self::$details,
            'headers'            => self::$headers,
            'combine'            => self::$combine,
            'expanded'           => self::$expanded,
            'featured'           => self::$featured,
            'hide_empty'         => self::$hide_empty,
            'hide_season_header' => self::$hide_season_header,
            'hide_widget_title'  => self::$hide_widget_title,
            'display_images'     => self::$display_images,
            'hide_upcoming'      => ! self::$user_logged_in, // hide private events if user isn't logged in
        ));

        switch ($args['group']) {
            case 'upcoming':
                // Events widget
                $season = self::get_upcoming($args);
                break;
            case 'list':
                // Dance Season
                $season = self::get_season_by_list($args);
                break;
            case 'tax':
                // '62 Center Season
                $season = self::get_season_by_terms($args);
                break;
            case 'month':
            default:
                // Music Season
                $season = self::get_season_by_month($args);
                break;
        }

        return $season;
    }

    public static function get_upcoming($args) {
        if (empty($terms = self::get_terms($args))) {
            return null;
        }

        try {
            $args = self::sanitize_args($args);
        } catch (Exception $e) {
        }

        // @todo We don't want to get all events unless the season field is completed in the form.
        $args['start_date'] = '&start_date=today';

        $args['end_date'] = '&end_date=' . end(self::$season['end_dates']);

        $season           = array();
        $url              = $args['url'] . '/term/' . current($terms)['term_id'] . '/?';
        $args['endpoint'] = $url . $args['depts'] . $args['venues'] . $args['per_page'] . $args['num_days'] . $args['start_date'] . $args['end_date'] . $args['exclude_terms'] . $args['hide_upcoming'] . $args['featured'];
        $args['title']    = $args['title'] ? $args['title'] : '';
        array_push($season, $args);

        return $season;
    }

    public static function get_season_by_terms($args) {
        $args['start_date'] = reset(self::$season['start_dates']);
        $args['end_date']   = end(self::$season['end_dates']);

        if (empty($terms = self::get_terms($args))) {
            return null;
        }

        // Clean up vars
        try {
            $args = self::sanitize_args($args);
        } catch (Exception $e) {
        }

        $season = array();
        foreach ($terms as $term) {
            $url              = $args['url'] . '/term/' . $term['term_id'] . '/?';
            $args['endpoint'] = $url . $args['depts'] . $args['venues'] . $args['per_page'] . $args['num_days'] . $args['start_date'] . $args['end_date'] . $args['exclude_terms'] . $args['hide_upcoming'] . $args['featured'];
            $args['title']    = $term['name'];

            array_push($season, $args);
        }

        return $season;
    }

    public static function get_season_by_month($args) {
        if (empty($terms = self::get_terms($args))) {
            return null;
        }

        // Clean up vars
        try {
            $args = self::sanitize_args($args);
        } catch (Exception $e) {
        }

        // If no start date, do entire season by month
        $season = array();
        if ( ! $args['start_date']) {
            for ($i = 0; $i < count(self::$season['start_dates']); $i++) {
                $url                = $args['url'] . '/term/all/?';
                $args['start_date'] = '&start_date=' . self::$season['start_dates'][ $i ];
                $args['end_date']   = '&end_date=' . self::$season['end_dates'][ $i ];

                $args['endpoint'] = $url . $args['depts'] . $args['venues'] . $args['per_page'] . $args['num_days'] . $args['start_date'] . $args['end_date'] . $args['exclude_terms'] . $args['hide_upcoming'] . $args['featured'];
                $args['title']    = date('F', strtotime(self::$season['start_dates'][ $i ]));

                array_push($season, $args);
            }
        } else {
            foreach ($terms as $term) {
                $url              = $args['url'] . '/term/' . $term['term_id'] . '/?';
                $args['endpoint'] = $url . $args['depts'] . $args['venues'] . $args['per_page'] . $args['num_days'] . $args['start_date'] . $args['end_date'] . $args['exclude_terms'] . $args['hide_upcoming'] . $args['featured'];

                array_push($season, $args);
            }
        }

        return $season;
    }

    public static function get_season_by_list($args = array()) {
        if (empty($terms = self::get_terms($args))) {
            return null;
        }

        try {
            $args = self::sanitize_args($args);
        } catch (Exception $e) {
        }

        // @todo We don't want to get all events unless the season field is completed in the form.
        $args['start_date'] = '&start_date=' . call_user_func(function($args) {
                if ( ! empty($args['start_date'])) {
                    return $args['start_date'];
                } else if ( ! $args['season']) {
                    return reset(self::$season['start_dates']);
                } else {
                    return 'today';
                }
            }, $args);

        $args['end_date'] = '&end_date=' . call_user_func(function($args) {
                if ( ! empty($args['end_date'])) {
                    return $args['end_date'];
                } else if ( ! $args['season']) {
                    return end(self::$season['end_dates']);
                } else {
                    return '+30 year';
                }
            }, $args);

        $season           = array();
        $url              = $args['url'] . '/term/' . current($terms)['term_id'] . '/?';
        $args['endpoint'] = $url . $args['depts'] . $args['venues'] . $args['per_page'] . $args['num_days'] . $args['start_date'] . $args['end_date'] . $args['exclude_terms'] . $args['hide_upcoming'];
        $args['title']    = $args['title'] ? $args['title'] : '';

        array_push($season, $args);

        return $season;
    }

    /**
     * Prioritize season passed by query, then widget/module, default to current.
     *
     * @param array $args
     *
     * @return mixed|string
     */
    public static function init_season($args = array()) {
        self::$current_season = self::get_current_season();
        $request_season       = '';

        // Prioritize season passed by query, then widget/module, default to current.
        if ( ! empty($_REQUEST['season'])) {
            self::$request_season = $request_season = $_REQUEST['season'];
        } else if ( ! empty($args['season'])) {
            self::$request_season = $args['season'];
        } else {
            self::$request_season = self::$current_season;
        }

        // Separate years
        self::$season['year'] = explode('-', ! empty($season) ? $season : self::$request_season);

        // Correct the end year: '19' --> '2019'
        self::$season['year'][1] = strval(intval(self::$season['year'][0]) + 1);

        self::$season['start_month'] = self::$start_month;
        self::$season['end_month']   = self::$end_month;

        self::$season['start_dates'] = array();
        self::$season['end_dates']   = array();

        for ($month = self::$season['start_month'], $year = self::$season['year'][0], $i = 0, $count = 0; $i < 12; $i++, $count++) {
            if ($count === 12) {
                break;
            }
            if ($month > 12) {
                $month = 1;
                $year  = self::$season['year'][1];
            }
            array_push(self::$season['start_dates'], $year . '-' . $month);

            $end_month = $month + 1;
            $end_year  = $year;
            if ($end_month > 12) {
                $end_month = 1;
                $end_year  = self::$season['year'][1];
            }
            array_push(self::$season['end_dates'], $end_year . '-' . $end_month);

            $month++;
        }

        return $request_season;
    }

    /**
     * @return string
     */
    public static function get_current_season() {
        // current year
        $year = intval(date('Y'));
        // current month number without zero
        $m = intval(date('n'));

        // if 0 < current_month < end_month, season starts prior calendar year.
        $start_y = $m > 0 && $m < self::$start_month ? intval($year) - 1 : intval($year);
        $end_y   = strval($start_y + 1);

        return $start_y . '-' . substr($end_y, -2);
    }

    /**
     * Retrieve tax terms via rest or transient.
     *
     * @param $args ['cat' => 'term-slug', 'form' => bool, 'tax' => 'tax-slug']
     *
     * @return array|mixed
     */
    public static function get_terms($args) {
        // Unset empty args for sanity.
        $args = array_filter((array) $args, function($a) {
            return $a !== "";
        });

        // Get term via kludgy BB module form.
        // $args['tax'] holds the CTD taxonomy.
        // $args[$args['tax']] holds the term slug.
        $args['cat'] = ! empty($args[ $args['tax'] ]) ? $args[ $args['tax'] ] : (empty($args['cat']) ? 'all' : $args['cat']);

        // If tax listing we need all terms.
        $is_tax = $args['group'] === 'tax';
        // If cat is 'all' we can just pass 'all' to the endpoint to get all.
        // posts in tax, regardless of term
        $not_all = $args['cat'] !== 'all';
        // If there are no headers and cat is single term, we need the term.
        $not_headers = ! bool_from_yn($args['headers']);

        $terms = array();
        if ($is_tax) {
            // Get all terms in taxonomy
            $endpoint = self::$rest_api . 'get/terms/' . $args['tax'];
            $temp     = Ctd_Events_Helper::get_rest_response($endpoint);
            foreach ($temp as $t) {
                array_push($terms, array('term_id' => $t->term_id, 'slug' => $t->slug, 'name' => $t->name));
            }
        } else if ($not_all) {
            // Get single term
            $endpoint = self::$rest_api . 'get/tax/' . $args['tax'] . '/term/' . $args['cat'];
            $temp     = Ctd_Events_Helper::get_rest_response($endpoint);
            array_push($terms, array('term_id' => $temp->term_id, 'slug' => $temp->slug, 'name' => $temp->name));
        } else {
            // Used when the caller is a widget (not form) with All cats and NO headers.
            // We want to get all events regardless of category, in chron order.
            array_push($terms, array('term_id' => 'all', 'slug' => 'all', 'name' => 'All'));
        }

        return $terms;
    }

    /**
     * @param $args
     *
     * @return mixed
     * @throws Exception
     */
    public static function sanitize_args($args) {
        $args['details']            = bool_from_yn($args['details']);
        $args['headers']            = bool_from_yn($args['headers']);
        $args['combine']            = bool_from_yn($args['combine']);
        $args['expanded']           = bool_from_yn($args['expanded']);
        $args['featured']           = bool_from_yn($args['featured']);
        $args['hide_empty']         = bool_from_yn($args['hide_empty']);
        $args['hide_season_header'] = bool_from_yn($args['hide_season_header']);
        $args['hide_widget_title']  = bool_from_yn($args['hide_widget_title']);
        $args['display_images']     = bool_from_yn($args['display_images']);

        // URL queryify parameters
        $args['exclude_terms'] = call_user_func(function($args) {
            // Make compat with BB form select, which is a bit of a workaround. Widget only needs 'exclude_terms'.
            // This might already be in the correct form if the widget is being refreshed.
            $exclude_terms = ! empty($args[ 'exclude_terms_' . $args['tax'] ]) ? $args[ 'exclude_terms_' . $args['tax'] ] : $args['exclude_terms'];

            if (is_array($exclude_terms)) {
                $exclude_terms = '&exclude_terms[]=' . join('&exclude_terms[]=', $exclude_terms);
            }

            return ! empty($exclude_terms) ? $exclude_terms : '';
        }, $args);

        $args['depts'] = call_user_func(function($args) {
            return ! empty($args['depts']) ? '&depts[]=' . join('&depts[]=', $args['depts']) : '';
        }, $args);

        $args['venues'] = call_user_func(function($args) {
            return ! empty($args['venues']) ? '&venues[]=' . join('&venues[]=', array_keys($args['venues'])) : '';
        }, $args);

        $args['featured']      = ! empty($args['featured']) ? '&featured=1' : null;
        $args['hide_upcoming'] = ! empty($args['hide_upcoming']) ? '&hide_upcoming=1' : null;
        $args['per_page']      = ! empty($args['per_page']) ? '&per_page=' . $args['per_page'] : null;
        $args['num_days']      = ! empty($args['num_days']) ? '&num_days=' . $args['num_days'] : null;
        $args['url']           = self::$rest_api . 'list/tax/' . $args['tax'];

        $args['start_date'] = ! empty($args['start_date']) ? '&start_date=' . $args['start_date'] : '';

        if ( ! empty($args['end_date'])) {
            $date = new DateTime($args['end_date']);
            // @todo Is adding one day really necessary?
            //$date             = $date->add(new DateInterval('P1D'));
            $args['end_date'] = $date->format('Y-m-d');
        }
        $args['end_date'] = ! empty($args['end_date']) ? '&end_date=' . $args['end_date'] : '';

        return $args;
    }

    /**
     * Clear cached transient and refresh HTML
     *
     * @param      $args
     * @param bool $die
     *
     * @throws Exception
     */
    public static function ajax_refresh($args, $die = true) {
        // Get args from ajax call
        $args = ! empty($args) ? $args : $_POST['dataset'];
        // Filter empty keys
        $args = array_intersect_key($args, array_flip(self::$defaults));
        // Flag to force transient refresh
        $args['refresh'] = true;

        return $die ? die(self::get_season($args)) : self::get_season($args);
    }

    public static function get_site_info($which, $what) {
        return self::$sites[ $which ][ $what ];
    }

    public static function is_current_season() {
        return self::$current_season === self::$request_season;
    }

    public static function get_title() {
        self::init_season();
        $title = self::$season['year'][0] . '-' . substr(self::$season['year'][1], 2) . ' Season';

        return $title;
    }

    /**
     * Returns the singleton instance of this class.
     *
     * @return Ctd_Events The singleton instance.
     */
    public static function instance() {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * @param array $options
     *
     * @return string
     */
    public static function get_button_data(array $options): string {
        $button_data = '';
        foreach ($options as $key => $value) {
            if (is_array($value)) {
                $k     = '&' . $key . '[]=';
                $v     = join($k, $value);
                $value = $k . $v;
            }
            $button_data .= "data-{$key}='{$value}' ";
        }

        return $button_data;
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

Ctd_Events::instance();
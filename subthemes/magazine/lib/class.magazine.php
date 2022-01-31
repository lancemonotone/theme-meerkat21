<?php

namespace m21;

class Magazine {
    public
        $issue_names,
        $context_issue,
        $context_year,
        $latest_issue,
        $latest_year;

    public function __construct() {
        add_filter('category_crumb', array(&$this, 'get_category_crumb'), 10, 2);
        add_filter('custom_end_crumb', array(&$this, 'get_custom_end_crumb'));

        $this->set_issue_names();

    }

    /**
     * Insert context edition link in breadcrumbs before end crumb
     *
     * @return string
     */
    function get_custom_end_crumb(): string {
        $out = '';
        if ( ! is_page() && ! is_404()) {
            global $wp_query;
            if ($wp_query->query_vars['volume_year']) {
                $out .= $this->get_context_edition_crumb(true);
            }
        }

        return $out;
    }

    /**
     * @param array  $crumb
     * @param object $cat_info
     *
     * @return array
     */
    function get_category_crumb(array $crumb, object $cat_info): array {
        $name  = $cat_info->name;
        $url   = $this->get_issue_href() . '/' . $cat_info->slug;
        $class = 'magazine-crumb';

        return array($name, $url, $class);
    }

    //---- ISSUE & YEAR DETECTION ----//

    /**
     * Do we know for a fact that we're inside an issue? Returns true or false.
     *
     * @return bool
     */
    public function issue_known(): bool {
        global $wp_query;

        // dissect url & query vars to get our context
        $uri = $_SERVER['REQUEST_URI'];

        if ($uri == '/') {
            // front page is always the latest edition
            return true;
        }

        $vol_yr  = $wp_query->query_vars['volume_year'];
        $vol_iss = $wp_query->query_vars['volume_issue'];
        if ($vol_yr && $vol_iss) return true;

        return false;
    }

    private function get_issue_name_array($slug): array {
        if ($this->issue_names[ $slug ]) {
            $name = $this->issue_names[ $slug ];
        } else {
            $name = ucfirst($slug);
        }

        return array('slug' => $slug, 'name' => $name);
    }

    private function set_latest_year() {
        $year_obj = get_field('current_edition_year', 'options');
        // Hack for ACF backwards compat.
        if (is_array($year_obj)) {
            $year_obj = get_term($year_obj[0], 'volume_year');
        }
        $this->latest_year = $year_obj->slug;
    }

    private function set_latest_issue() {
        $issue_obj = get_field('current_edition_issue', 'options');
        if (is_array($issue_obj)) {
            $issue_obj = get_term($issue_obj[0], 'volume_issue');
        }
        $this->latest_issue = $this->get_issue_name_array($issue_obj->slug);
    }

    public function get_latest_year() {
        if ( ! $this->latest_year) $this->set_latest_year();

        return $this->latest_year;
    }

    public function get_latest_issue() {
        if ( ! $this->latest_issue) $this->set_latest_issue();

        return $this->latest_issue;
    }

    /**
     * Gets issue of magazine edition - either latest issue (as set in theme options) or currently viewed issue.
     *
     */
    private function set_context_issue() {
        global $wp_query;

        if ($wp_query->query_vars['volume_issue']) {
            // get issue by query context
            $slug                = $wp_query->query_vars['volume_issue'];
            $this->context_issue = $this->get_issue_name_array($slug);
        } else {
            // use default as set in acf theme options
            $this->context_issue = $this->get_latest_issue();
        }
    }

    public function get_context_issue() {
        if ( ! $this->context_issue) $this->set_context_issue();

        return $this->context_issue;
    }

    /**
     * Gets year of magazine edition - either latest issue year (as set in theme options) or currently viewed issue year.
     *
     */
    private function set_context_year() {
        global $wp_query;
        if ($wp_query->query_vars['volume_year']) {
            // get issue year by query context
            $context_year = $wp_query->query_vars['volume_year'];
        } else {
            $context_year = $this->get_latest_year();
        }
        // use default as set in acf theme options
        $this->context_year = $context_year;
    }

    public function get_context_year() {
        if ( ! $this->context_year) $this->set_context_year();

        return $this->context_year;
    }

    /**
     * Gets issue that a specific post belongs to
     *
     * @param $id
     *
     * @return array ( [slug] => jan [name] => January )
     */
    public function get_post_issue($id): array {
        $issue = get_the_terms($id, 'volume_issue')[0];

        return array(
            'slug' => $issue->slug,
            'name' => $issue->name
        );
    }

    /**
     * Gets year that a specific post belongs to
     *
     * @param $id
     *
     * @return string
     */
    public function get_post_year($id): string {
        $year = get_the_terms($id, 'volume_year')[0];

        return $year->slug;
    }

    /**
     * Returns array of year/issue data
     *
     * @return array
     */
    public function get_edition_array(): array {
        return array(
            'issue' => $this->get_context_issue(),
            'year'  => $this->get_context_year()
        );
    }

    /**
     * Returns link to latest issue or currently viewed issue.
     *
     * @param bool $wrap
     *
     * @return array
     */
    public function get_context_edition_crumb($wrap = false): array {
        $issue = $this->get_context_issue();
        $year  = $this->get_context_year();

        $name  = $issue['name'] . ' ' . $year;
        $url   = $this->get_context_edition_url();
        $class = 'current-edition';

        return array($name, $url, $class, $wrap);
    }

    public function get_latest_edition_crumb(): array {
        $breadcrumb = new Breadcrumbs();
        $name  = 'Latest Issue';
        $url   = $this->get_latest_edition_url();
        $class = 'latest-edition';

        return $breadcrumb->one_crumb($name, $url, $class, false);
    }

    /**
     * @return array
     */
    public function get_edition_link(): array {
        if ($this->issue_known()) {
            return $this->get_context_edition_crumb();
        } else {
            return $this->get_latest_edition_crumb();
        }
    }

    /**
     * @return string
     */
    public function get_context_edition_url(): string {
        $issue = $this->get_context_issue();
        $year  = $this->get_context_year();

        return '/' . $year . '/' . $issue['slug'] . '/';
    }

    /**
     * Returns link to most recent issue, as defined by the theme options.
     *
     * @return string
     */
    public function get_latest_edition_url(): string {
        $issue = $this->get_latest_issue();
        $year  = $this->get_latest_year();

        return '/' . $year . '/' . $issue['slug'] . '/';
    }

    /**
     * Is this a homepage.  A homepage is the actual home URL, or a /year/issue query.
     *  /year/issue/category and /year/issue/category/post are NOT valid.
     * @return bool
     */
    public function is_front_page(): bool {
        global $wp_query;

        return is_front_page() || (
                2 === count($wp_query->query) &&
                array_key_exists('volume_year', $wp_query->query) &&
                array_key_exists('volume_issue', $wp_query->query)
            );
    }

    /**
     * Is this issue published?
     * @return bool
     */
    public function is_published(): bool {
        $issue    = $this->get_context_issue();
        $year     = $this->get_context_year();
        $toc_list = $this->get_toc_post($year . '-' . $issue['slug']);
        $toc_post = reset($toc_list);

        return 'publish' === $toc_post->post_status;
    }

    /**
     * Returns full year/issue href for breadcrumbs
     *
     * @return string http://blog.com/year/issue
     */
    public function get_issue_href(): string {
        $issue = $this->get_edition_array();

        return home_url() . '/' . $issue['year'] . '/' . $issue['issue']['slug'];
    }

    /**
     * Get featured posts that have ACF featured images.
     *
     * @param bool $orderby_grid
     *
     * @return array Featured posts
     */
    public function get_magazine_features($orderby_grid = false): array {
        $issue = $this->get_context_issue();
        $year  = $this->get_context_year();

        $args = array('post_type' => 'feature',
                      'tax_query' => array(
                          array(
                              'taxonomy' => 'volume_issue',
                              'field'    => 'slug',
                              'terms'    => $issue['slug']
                          ),
                          array(
                              'taxonomy' => 'volume_year',
                              'field'    => 'slug',
                              'terms'    => $year
                          ),
                      ),
        );
        if ($orderby_grid) {
            $orderby_arr = array(
                'meta_key' => 'section_grid_location',
                'orderby'  => 'meta_value_num',
                'order'    => 'ASC'
            );
        } else {
            $orderby_arr = array(
                'orderby' => 'menu_order',
                'order'   => 'ASC'
            );
        }
        $args = array_merge($args, $orderby_arr);

        return get_posts($args);
    }

    /**
     * Get all editions by published TOC posts.
     *
     * @return array 'year,issue_slug'
     */
    public function get_editions(): array {
        $editions      = array();
        $volume_years  = get_terms('volume_year');
        $volume_issues = get_terms('volume_issue');
        foreach ($volume_years as $y) {
            foreach ($volume_issues as $i) {
                if ($this->issue_has_content(array($y->slug, $i->slug))) {
                    $editions[] = $y->slug . ',' . $i->slug;
                }
            }
        }

        return $editions;
    }

    /**
     * Returns posts for section, ordered by date DESC.
     *
     * @return array TOC post
     */
    public function get_toc_posts(): array {
        global $current_user;
        $post_status = user_can($current_user, 'read_private_posts') ? array('publish', 'draft') : 'publish';
        $editions    = array();
        $args        = array(
            'post_type'      => 'toc_desc',
            'post_status'    => $post_status,
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'ASC'
        );
        $toc_posts   = get_posts($args);

        foreach ($toc_posts as $toc_post) {
            $issue      = explode('-', $toc_post->post_name);
            $editions[] = $issue[0] . ',' . $issue[1];
        }

        return $editions;
    }

    /**
     * Checks to see if there is content associated with specific year and section.
     *
     * @param array $issue_array array('2012','fall')
     * @param null  $section 'features'
     *
     * @return bool
     */
    public function issue_has_content(array $issue_array, $section = null): bool {

        $tax_query = array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'volume_year',
                'field'    => 'slug',
                'terms'    => array($issue_array[0])),
            array(
                'taxonomy' => 'volume_issue',
                'field'    => 'slug',
                'terms'    => array($issue_array[1]))
        );
        if ($section) {
            $section_array = array(
                array('taxonomy' => 'category',
                      'field'    => 'slug',
                      'terms'    => array($section))
            );
            $tax_query     = array_merge($section_array, $tax_query);
        }
        $args = array(
            'post_type'   => 'any',
            'tax_query'   => $tax_query,
            'post_status' => 'publish'
        );

        $the_posts = get_posts($args);

        return count($the_posts) ? true : false;
    }


    /**
     * Get next and previous elements in an array
     *
     * @param mixed $needle
     * @param mixed $haystack
     * @param bool  $previous
     * @param bool  $wrap
     *
     * @return string Next/Previous element or null if no value
     */
    public function get_adjacent_value($needle, $haystack, $previous = true, $wrap = false): string {
        $current_index = array_search($needle, $haystack);

        // Find the index of the next/prev items
        if ($previous) {
            if ($wrap) {
                $output = $haystack[ ($current_index - 1 < 0) ? count($haystack) - 1 : $current_index - 1 ];
            } else {
                $output = $haystack[ ($current_index - 1 < 0) ? null : $current_index - 1 ];
            }
        } else {
            if ($wrap) {
                $output = $haystack[ ($current_index + 1 == count($haystack)) ? 0 : $current_index + 1 ];
            } else {
                $output = $haystack[ ($current_index + 1 == count($haystack)) ? null : $current_index + 1 ];
            }
        }

        return $output;
    }

    /**
     * Returns 'disabled' class for use in links where there is no content.
     *
     * @param array $issue_array
     * @param null  $section
     *
     * @return void
     */
    public function maybe_disabled(array $issue_array, $section = null): void {
        if ( ! $this->issue_has_content($issue_array, $section)) {
            echo 'disabled';
        }
    }

    /**
     * Returns posts for section.
     *
     * @param string TOC title (e.g., 2012-summer)
     *
     * @return array single TOC post
     *
     */
    public function get_toc_post($toc_title): array {
        global $current_user;
        $post_status = user_can($current_user->ID, 'read_private_posts') ? array('publish', 'draft') : 'publish';
        $args        = array(
            'post_type'      => 'toc_desc',
            'post_status'    => $post_status,
            'name'           => $toc_title,
            'posts_per_page' => 1,
        );

        return get_posts($args);
    }

    public function set_issue_names() {
        switch (\Wms_Server::instance()->subdomain) {
            case 'alumni-news':
                $this->issue_names = array(
                    'jan' => 'January',
                    'feb' => 'February',
                    'mar' => 'March',
                    'apr' => 'April',
                    'may' => 'May',
                    'jun' => 'June',
                    'jul' => 'July',
                    'aug' => 'August',
                    'sep' => 'September',
                    'oct' => 'October',
                    'nov' => 'November',
                    'dec' => 'December');
                break;
            case 'magazine':
                $this->issue_names = array(
                    'spring' => 'Spring',
                    'summer' => 'Summer',
                    'fall'   => 'Fall',
                    /*'winter' => 'Winter'*/);
                break;
        }
    }
}

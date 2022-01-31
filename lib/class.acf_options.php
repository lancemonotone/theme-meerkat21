<?php

namespace m21;

/*
 * ACF OPTIONS
 * sets up fields for use by the plugin advanced custom fields, which handles all the heavy lifting
 * base code exported via the plugin settings, then cleaned up & commented
 *
 * Register field groups
 * The register_field_group function accepts 1 array which holds the relevant data to register a field group
 * You may edit the array as you see fit. However, this may result in errors if the array is not compatible with ACF
 * This code must run every time the functions.php file is read
 *
 * @see https://www.advancedcustomfields.com/resources/custom-location-rules/
 * @see https://www.billerickson.net/acf-custom-location-rules/
 */

class Acf_Options {
    public function __construct() {
        // Next 3 filters add a Site field to the ACF location rules dropdown.
        add_filter('acf/location/rule_types', [$this, 'acf_rule_type_site_id']);
        add_filter('acf/location/rule_values/site_id', [$this, 'acf_rule_values_site_id']);
        add_filter('acf/location/rule_match/site_id', [$this, 'acf_location_rule_match_site_id'], 10, 3);

        add_filter('admin_body_class', [$this, 'add_admin_body_class']);
        add_action('admin_menu', [$this, 'remove_acf_menu'], 10000);

        // Make parent acf-json files available to child themes for import, instead of manually creating duplicates
        // https://support.advancedcustomfields.com/forums/topic/parent-child-theme-json-sync/
        add_filter('acf/settings/load_json', [$this, 'parent_theme_field_groups']);

    }

    function parent_theme_field_groups($paths) {
        $path    = get_template_directory() . '/acf-json';
        $paths[] = $path;

        return $paths;
    }

    function acf_rule_type_site_id($choices) {
        $choices['Site']['site_id'] = 'Site';

        return $choices;
    }

    function acf_rule_values_site_id($choices) {
        $sites = $this->get_sites();
        foreach ($sites as $site) {
            $choices[ $site->id ] = \WP_Site::get_instance($site->id)->blogname;
        }

        return $choices;
    }

    function acf_location_rule_match_site_id($match, $rule, $screen) {
        $site          = get_current_blog_id();
        $selected_site = (int) $rule['value'];

        if ($rule['operator'] == "==") {
            $match = ($site == $selected_site);
        } elseif ($rule['operator'] == "!=") {
            $match = ($site != $selected_site);
        }

        return $match;
    }

    function add_admin_body_class($classes) {
        if (is_super_admin()) {
            $classes .= ' super-admin';
        }
        $classes .= ' blog-' . get_current_blog_id();

        return $classes;
    }

    /**
     * @return mixed|string[]
     */
    function get_sites() {
        // Rebuild list of academic sites if transient has expired.
        $sites_transient = 'academic_sites_array';
        $sites           = get_site_transient($sites_transient);

        if (empty($sites)) {
            $sites   = array('' => '');
            $results = get_sites();
            foreach ($results as $site) {
                $details                 = get_blog_details($site->blog_id);
                $sites[ $site->blog_id ] = $details->blogname;
            }
            asort($sites);
            set_site_transient($sites_transient, $sites, WEEK_IN_SECONDS);
        }

        return $sites;
    }

    /**
     * Get a list of sites for an ACF select element
     */
    function get_sites_select_choices($field) {
        $sites = self::get_sites();

        // reset choices
        $field['choices'] = array();

        // loop through array and add to field 'choices'
        foreach ($sites as $site) {
            $field['choices'][ $site ] = $site;
        }

        // return the field
        return $field;
    }

    //always hide options for non-admins, this covers ACF json options also
    function remove_acf_menu() {
        if ( ! current_user_can('activate_plugins')) { // Administrator
            remove_menu_page('acf-options');
        }
    }

}

new Acf_Options();

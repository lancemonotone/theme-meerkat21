<?php
namespace m21;
/**
 * Class M21_BB_Acf
 *
 * @link https://www.advancedcustomfields.com/resources/custom-location-rules/
 * @link https://www.billerickson.net/acf-custom-location-rules/
 */
class Acf {

    public function __construct() {
        // Next 3 filters add a Site field to the ACF location rules dropdown.
        add_filter('acf/location/rule_types', array(&$this, 'acf_rule_type_site_id'));
        add_filter('acf/location/rule_values/site_id', array(&$this, 'acf_rule_values_site_id'));
        add_filter('acf/location/rule_match/site_id', array(&$this, 'acf_location_rule_match_site_id'), 10, 3);

        //page/post level rules- theme wide
        add_action('acf/init', array(&$this,'wms_page_post_acf_fields'));

    }

    function acf_rule_type_site_id($choices) {
        $choices['Site']['site_id'] = 'Site';

        return $choices;
    }

    function acf_rule_values_site_id($choices) {
        $sites = get_sites(array(
            'public'  => 1,
            'number'  => 500,
            'orderby' => 'domain'
        ));
        foreach ($sites as $site) {
            $choices[ $site->id ] = \WP_Site::get_instance($site->id)->blogname;
        }

        return $choices;
    }

    function acf_location_rule_match_site_id($match, $rule, $screen) {
        $site  = get_current_blog_id();
        $selected_site = (int) $rule['value'];

        if ($rule['operator'] == "==") {
            $match = ($site == $selected_site);
        } elseif ($rule['operator'] == "!=") {
            $match = ($site != $selected_site);
        }

        return $match;
    }

    
    function wms_page_post_acf_fields() {
        
        /* These are added via code instead of acf json
         * in order to show/hide them wrt to how site wide 
         * options for the same controls are set
         */

         $remove_site_nav_sitewide = get_field('remove_site_nav', 'option'); 
         $remove_sidebar_sitewide = get_field('remove_sidebar', 'option');  

        if( function_exists('acf_add_local_field_group') ):

            acf_add_local_field_group(array(
                'key' => 'group_608c69c6cda33',
                'title' => 'Post/Page Options',
                'fields' => array(
                    $remove_site_nav_sitewide ? 
                    array(
                        'key' => 'field_60900973a6652',
                        'label' => '',
                        'name' => '',
                        'type' => 'message',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'message' => '<strong>The site nav is already disabled sitewide on the <a href="/wp-admin/admin.php?page=acf-options">options page</a> in the WordPress Admin.</strong>',
                        'new_lines' => 'wpautop',
                        'esc_html' => 0,
                    )
                    : array(
                        'key' => 'field_608c6a0d772b5',
                        'label' => 'Hide Site Nav?',
                        'name' => 'hide_site_nav',
                        'type' => 'true_false',
                        'instructions' => 'Toggle this switch to hide/show the left sidebar on this page only.',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'message' => '',
                        'default_value' => 0,
                        'ui' => 1,
                        'ui_on_text' => '',
                        'ui_off_text' => '',
                    ),
                     $remove_sidebar_sitewide ? 
                     
                    array(
                        'key' => 'field_60900973a6653',
                        'label' => '',
                        'name' => '',
                        'type' => 'message',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'message' => '<strong>The sidebar is already disabled sitewide on the  <a href="/wp-admin/admin.php?page=acf-options">options page</a> in the WordPress Admin.</strong>',
                        'new_lines' => 'wpautop',
                        'esc_html' => 0,
                    )
                     
                     : array(
                        'key' => 'field_608c6a41192ef',
                        'label' => 'Hide Sidebar?',
                        'name' => 'hide_sidebar',
                        'type' => 'true_false',
                        'instructions' => 'Toggle this switch to hide/show the right sidebar on this page only.',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'message' => '',
                        'default_value' => 0,
                        'ui' => 1,
                        'ui_on_text' => '',
                        'ui_off_text' => '',
                    ),
                ),
                'location' => array(
                    array(
                        array(
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'post',
                        ),
                    ),
                    array(
                        array(
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'page',
                        ),
                    ),
                ),
                'menu_order' => 0,
                'position' => 'normal',
                'style' => 'default',
                'label_placement' => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen' => '',
                'active' => true,
                'description' => '',
            ));

        endif;

    }
}

new Acf();
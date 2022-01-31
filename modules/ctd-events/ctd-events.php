<?php

namespace m21;

include('lib/class.ctd_shortcode.php');
include('lib/class.ctd_events.php');
include('lib/class.ctd_events_helper.php');

class Ctd_Events_Module extends \FLBuilderModule {
    public $name = 'Events Module';
    public $enabled = true;
    public static $events_tax = 'event_category';
    public static $ctd_tax = 'ctd_series_cats';
    public static $music_tax = 'music_dept_season_types';

    // You shouldn't need to change anything past this point.
    public $slug = __DIR__;

    public function __construct() {
        parent::__construct(array(
            // Shouldn't need to change these...
            'name'            => __($this->name, 'fl-builder'),
            'description'     => __($this->description, 'fl-builder'),
            'group'           => 'Williams',
            'category'        => __('Williams Modules', 'fl-builder'),
            'dir'             => THEME_DIR . '/modules/' . $this->slug . '/',
            'url'             => THEME_URL . '/modules/' . $this->slug . '/',
            //
            'icon'            => 'button.svg',
            'editor_export'   => true, // Defaults to true and can be omitted.
            'enabled'         => $this->enabled, // Defaults to true and can be omitted.
            'partial_refresh' => false, // Defaults to false and can be omitted.
        ));
    }

    /**
     * @param String $tax
     * @param bool   $all True: include 'All' option; or false for just terms.
     *
     * @return array
     */
    public static function get_terms(string $tax): array {
        $terms = [];
        if (stristr($_SERVER["REQUEST_URI"], 'fl_builder')) {
            foreach (self::get_form_terms(array(
                'form' => true,
                'tax'  => $tax
            )) as $term) {
                $terms[ $term['term_id'] ] = $term['name'];
            }
        }

        return $terms;
    }

    public static function get_form_terms($args) {
        $terms = array();

        $endpoint = Ctd_Events::$rest_api . 'get/terms/' . $args['tax'];
        if ($temp = Ctd_Events_Helper::get_rest_response($endpoint)) {
            foreach ($temp as $t) {
                array_push($terms, array('term_id' => $t->term_id, 'slug' => $t->slug, 'name' => $t->name));
            }
        }

        return $terms;
    }
}


\FLBuilder::register_module('Ctd_Events_Module',
    array(
        'my-tab-1' => array(
            'title'    => 'Settings',
            'sections' => array(
                'my-section-1' => array(
                    'title'  => 'Events Module',
                    'fields' => array(
                        'refresh'                                         => 'y',
                        'title'                                           => array(
                            'type'  => 'text',
                            'label' => 'Title',
                        ),
                        'hide_widget_title'                               => array(
                            'type'    => 'select',
                            'label'   => 'Hide module title?',
                            'default' => 'n',
                            'options' => array(
                                'y' => 'Yes',
                                'n' => "No",
                            ),
                        ),
                        'display_images'                                  => array(
                            'type'    => 'select',
                            'label'   => 'Display featured image?',
                            'default' => 'n',
                            'options' => array(
                                'y' => 'Yes',
                                'n' => "No",
                            ),
                        ),
                        'group'                                           => array(
                            'type'    => 'select',
                            'default' => 'month',
                            'options' => array(
                                'upcoming' => 'Upcoming',
                                'list'     => 'List',
                                'month'    => 'Months',
                                'tax'      => 'Categories'
                            ),
                            'toggle'  => array(
                                'list'  => array(
                                    'fields' => array(
                                        'season',
                                        'start_date',
                                        'end_date',
                                        'num_days',
                                    )
                                ),
                                'month' => array(
                                    'fields' => array(
                                        'season',
                                        'start_date',
                                        'end_date',
                                        'num_days',
                                    )
                                ),
                                'tax'   => array(
                                    'fields' => array(
                                        'season',
                                        'start_date',
                                        'end_date',
                                        'num_days',
                                    )
                                ),
                            ),
                            'label'   => 'Group Events by',
                            'help'    => "<em>List</em> creates a flat list with no headers. <em>Months</em> creates month name headers. <em>Categories</em> creates category name headers."
                        ),
                        'tax'                                             => array(
                            'type'    => 'select',
                            'label'   => 'Event Type',
                            'options' => array(
                                Ctd_Events_Module::$events_tax => 'All Events',
                                Ctd_Events_Module::$music_tax  => 'Music',
                                Ctd_Events_Module::$ctd_tax    => "CTD",
                            ),
                            /*Ctd_Events_Module::$music_tax => 'Music',
                            Ctd_Events_Module::$ctd_tax   => "CTD",*/
                            // Conditional display. If this selection matches one of the
                            // select fields below, show that field.
                            'toggle'  => array(
                                Ctd_Events_Module::$events_tax => array(
                                    'fields' => array(
                                        Ctd_Events_Module::$events_tax,
                                        'exclude_terms_' . Ctd_Events_Module::$events_tax
                                    )
                                ),
                                Ctd_Events_Module::$music_tax  => array(
                                    'fields' => array(
                                        Ctd_Events_Module::$music_tax,
                                        'exclude_terms_' . Ctd_Events_Module::$music_tax,
                                        'season',
                                        'hide_season_header'
                                    )
                                ),
                                Ctd_Events_Module::$ctd_tax    => array(
                                    'fields' => array(
                                        Ctd_Events_Module::$ctd_tax,
                                        'exclude_terms_' . Ctd_Events_Module::$ctd_tax,
                                        'season',
                                        'hide_season_header'
                                    )
                                ),
                            ),
                            'help'    => "Choose the CTD department to display"
                        ),
                        Ctd_Events_Module::$events_tax                    => array(
                            'type'    => 'select',
                            'label'   => 'Category',
                            //'default' => 'all',
                            'options' => array('all' => 'All') + Ctd_Events_Module::get_terms(Ctd_Events_Module::$events_tax),
                            'toggle'  => array(
                                'all' => array(
                                    'fields' => array('exclude_terms_' . Ctd_Events_Module::$events_tax)
                                )
                            )
                        ),
                        Ctd_Events_Module::$music_tax                     => array(
                            'type'    => 'select',
                            'label'   => 'Category',
                            //'default' => 'all',
                            'options' => array('all' => 'All') + Ctd_Events_Module::get_terms(Ctd_Events_Module::$music_tax),
                            'toggle'  => array(
                                'all' => array(
                                    'fields' => array('exclude_terms_' . Ctd_Events_Module::$ctd_tax)
                                )
                            )
                        ),
                        Ctd_Events_Module::$ctd_tax                       => array(
                            'type'    => 'select',
                            'label'   => 'Category',
                            //'default' => 'all',
                            'options' => array('all' => 'All') + Ctd_Events_Module::get_terms(Ctd_Events_Module::$ctd_tax),
                            'toggle'  => array(
                                'all' => array(
                                    'fields' => array('exclude_terms_' . Ctd_Events_Module::$music_tax))
                            )
                        ),
                        'exclude_terms_' . Ctd_Events_Module::$events_tax => array(
                            'type'         => 'select',
                            'label'        => 'Exclude Categories',
                            'multi-select' => true,
                            'options'      => Ctd_Events_Module::get_terms(Ctd_Events_Module::$events_tax),
                            'help'         => "Choose which categories to exclude if Category above is 'All'."
                        ),
                        'exclude_terms_' . Ctd_Events_Module::$music_tax  => array(
                            'type'         => 'select',
                            'label'        => 'Exclude Categories',
                            'multi-select' => true,
                            'options'      => Ctd_Events_Module::get_terms(Ctd_Events_Module::$music_tax),
                            'help'         => "Choose which categories to exclude if Category above is 'All'."
                        ),
                        'exclude_terms_' . Ctd_Events_Module::$ctd_tax    => array(
                            'type'         => 'select',
                            'label'        => 'Exclude Categories',
                            'multi-select' => true,
                            'options'      => Ctd_Events_Module::get_terms(Ctd_Events_Module::$ctd_tax),
                            'help'         => "Choose which categories to exclude if Category above is 'All'."
                        ),
                        'season'                                          => array(
                            'type'  => 'text',
                            'label' => 'Season',
                            'hint'  => 'Ex: <em>2019-20</em>. Leave blank for current season.'
                        ),
                        'hide_season_header'                              => array(
                            'type'    => 'select',
                            'label'   => 'Hide season header?',
                            'default' => 'y',
                            'hint'    => 'Hides season picker and jump links.',
                            'options' => array(
                                'y' => 'Yes',
                                'n' => "No",
                            ),
                        ),
                        'headers'                                         => array(
                            'type'    => 'select',
                            'label'   => 'Show Headers?',
                            'default' => 'y',
                            'options' => array(
                                'y' => 'Yes',
                                'n' => "No",
                            ),
                        ),
                        'combine'                                         => array(
                            'type'    => 'select',
                            'label'   => 'Combine recurring events?',
                            'default' => 'y',
                            'options' => array(
                                'y' => 'Yes',
                                'n' => "No",
                            ),

                        ),
                        'expanded'                                        => array(
                            'type'    => 'select',
                            'label'   => 'Show extra title information?',
                            'default' => 'n',
                            'options' => array(
                                'y' => 'Yes',
                                'n' => "No",
                            ),
                        ),
                        'details'                                         => array(
                            'type'    => 'select',
                            'label'   => 'Show date and venue?',
                            'default' => 'y',
                            'options' => array(
                                'y' => 'Yes',
                                'n' => "No",
                            ),
                        ),
                        'per_page'                                        => array(
                            'type'    => 'select',
                            'label'   => 'Number of events to display',
                            'default' => '-1',
                            'options' => array(
                                '-1'  => 'All',
                                '1'   => "1",
                                '2'   => "2",
                                '3'   => "3",
                                '4'   => "4",
                                '5'   => "5",
                                '10'  => "10",
                                '15'  => "15",
                                '25'  => "25",
                                '50'  => "10",
                                '100' => "100",
                                '200' => "200",
                            ),
                        ),
                        'start_date'                                      => array(
                            'type'  => 'date',
                            'label' => 'Start Date',
                        ),
                        'end_date'                                        => array(
                            'type'  => 'date',
                            'label' => 'End Date'
                        ),
                        'num_days'                                        => array(
                            'type'    => 'unit',
                            'label'   => 'Number of days',
                            'default' => ''
                        ),
                        'featured'                                        => array(
                            'type'    => 'select',
                            'label'   => 'Featured events',
                            'hint'    => 'Display only featured events; overrides categories.',
                            'default' => 'n',
                            'options' => array(
                                'y' => 'Yes',
                                'n' => "No",
                            ),
                        )
                    )
                )
            )
        )
    )
);

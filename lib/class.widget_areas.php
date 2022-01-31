<?php

namespace m21;

class Widget_Areas {

    public $widget_areas = array(
        array(
            'name'        => 'Sidebar',
            'id'          => 'sidebar',
            'description' => 'Right sidebar widgets'
        ),
        array(
            'name'        => 'Homepage Widget Area',
            'id'          => 'home-widget-area',
            'description' => 'Located at the top of your site\'s homepage'
        )
    );

    public function __construct(){
        add_action('widgets_init', [$this, 'sidebar_setup']);
    }

    /**
     * @param $widget_areas
     */
    function sidebar_setup() {
        foreach ($this->widget_areas as $widget_area) {
            register_sidebar(array(
                'name'           => $widget_area['name'],
                'id'             => $widget_area['id'],
                'description'    => $widget_area['description'],
                'before_widget'  => '<div id="%1$s" class="widget %2$s">',
                'after_widget'   => '</div>',
                'before_title'   => '<h3 class="widgettitle">',
                'after_title'    => '</h3>',
                'before_insides' => '<div class="widget-insides">',
                'after_insides'  => $this->edit_widget_link($widget_area['id']) . '</div>'
            ));
        }
    }

    /**
     * @param $sidebar
     *
     * @return bool|string
     */
    function edit_widget_link($sidebar) {
        // Creates an edit link for logged in admins that appears in sidebars/widgetized areas.
        // Note: edit widget anchor tag href is modified in main.js to point specifically to the widget, instead of just the sidebar.
        // You can override this behavior when building your own edit links like this:
        // '<span class="edit-me"><a href="edit.php"></span>'
        if (current_user_can('edit_others_pages')) {
            $siteurl = get_home_url();

            return <<< EOD
            <a class="edit-me" href="$siteurl/wp-admin/widgets.php?sidebar=$sidebar">Edit Widget</a>
EOD;
        } else {
            return false;
        }
    }
}

new Widget_Areas();
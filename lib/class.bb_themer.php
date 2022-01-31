<?php

namespace m21;

class BB_Themer {
    public function __construct() {
        add_filter('fl_theme_builder_template_include', array(&$this, 'override_bb_themer_layout'), 999, 2);
    }

    /**
     * Override render of themer layout and use Timberizer instead.
     * Pass rendered layout content via 'bb_themer_layout_content' filter,
     * which is picked up at the end of Timberizer::render_template().
     *
     * @param String $template
     * @param Int    $id
     *
     * @return void | string;
     */
    function override_bb_themer_layout(string $template, int $id) {
        $ids = \FLThemeBuilderLayoutData::get_current_page_content_ids();

        if (empty($ids)) {
            return '';
        }

        if ('fl-theme-layout' == get_post_type() && count($ids) > 1) {
            $post_id = \FLBuilderModel::get_post_id();
        } else {
            $post_id = $ids[0];
        }

        ob_start();
        \FLBuilder::render_content_by_id($post_id, 'div', apply_filters('fl_theme_builder_content_attrs', array()));
        $bb_themer_layout_content = ob_get_clean();

        if ($bb_themer_layout_content) {
            add_filter('timberizer_before_render', function($context) use ($bb_themer_layout_content) {
                $context['bb_themer_layout_content'] = $bb_themer_layout_content;

                return $context;
            });

            // Get our original theme index.php and twig.
            $template = get_index_template();
            include($template);
            die();
        } else {
            return $template;
        }

    }
}

new BB_Themer();
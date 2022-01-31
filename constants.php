<?php

namespace m21;
// TEMPORARY to HOOK UP OLD JS
define('M16_TEMP_JS_URL','/wp-content/themes/meerkat16/assets/build/js' );

define('WWW_BLOG_ID', 93);
define('WWW_BLOG_URL', get_site_url(WWW_BLOG_ID));

define('CAPABILITY_THRESH', 'edit_theme_options');

define('THEME_URL', get_template_directory_uri());
define('THEME_DIR', get_template_directory());

define('THEME_IMG_URL', THEME_URL . '/assets/img');

define('THEME_JS_PATH', THEME_DIR . '/assets/build/js');
define('THEME_JS_URL', THEME_URL . '/assets/build/js');

define('THEME_CSS_PATH', THEME_DIR . '/assets/build/css');
define('THEME_CSS_URL', THEME_URL . '/assets/build/css');

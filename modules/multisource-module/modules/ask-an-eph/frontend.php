<?php

namespace m21;

/**
 * This file should be used to render each module instance.
 * You have access to two variables in this file:
 *
 * $module An instance of your module class.
 * $settings The module's settings.
 *
 */

use Timber\Helper;

$query = \FLBuilderLoop::query($settings);
if ($query->have_posts()) {
    $posts = $query->posts;
} else {
    return false;
}

foreach ($posts as &$post) {

    $thumb = get_field('aae_image', $post->ID);
    if ( ! $thumb) break;

    $majors     = get_field('aae_major', $post->ID);
    $majors_arr = array();
    foreach ($majors as $major) array_push($majors_arr, $major->name);

    $post->context = array(
        'img'            => new \Timber\Image($thumb),
        'major'          => join('<br style="display: inherit">', $majors_arr),
        'edit_link'      => Helper::ob_function('edit_post_link', array(__('Edit'), '<span class="edit-me">', '</span>', $post->ID)),
        'name'           => get_field('aae_display_name', $post->ID),
        'year'           => substr(get_field('aae_graduation_year', $post->ID)->name, -2),
        'town'           => get_field('aae_hometown', $post->ID),
        'state'          => get_field('aae_state_country', $post->ID)->name,
        'unix'           => get_field('aae_alias', $post->ID),
        'activities'     => get_field('aae_activities', $post->ID),
        'hobbies'        => get_field('aae_hobbies', $post->ID),
        'experience'     => get_field('aae_meaningful_experience', $post->ID),
        'favorite_place' => get_field('aae_favorite_place', $post->ID),
        'why_williams'   => get_field('aae_why_williams', $post->ID),
        'accept_email'   => get_field('aae_receive_emails', $post->ID),
        'link_url'       => get_the_permalink(get_page_by_path('/connect/ask-an-eph/'))
    );
}

// Get the query data.
$template  = isset($settings->layout) ? $settings->template : 'grid';

$context = array(
    'posts'      => $posts,
    'template'   => $template,
    'settings'   => $settings,
    'module'     => $module
);

\Timber\Timber::render('ask-an-eph.twig', $context);
<?php

/**
 * This file should be used to render each module instance.
 * You have access to two variables in this file:
 *
 * $module An instance of your module class.
 * $settings The module's settings.
 *
 */

//switch to the requested blog to perform the query on that site
switch_to_blog($settings->source_site_id );


$args = array(
    'numberposts'     => 10,
    'post_type'       => $settings->post_type,
    'post_status'     => 'publish',
    'category'        => $settings->tax_query,
    'order'           => $settings->order,
    'orderby'         => $settings->order_by,
    'offset'          => $settings->offset,
    'exclude'         => $settings->exclude_self,
);
 
$q = get_posts( $args );

//get the images and merge with post meta
$send_array = array();

foreach ($q as $_post) {
    $post_id = get_post_thumbnail_id( $_post->ID );
    $image = wp_get_attachment_image_src( $post_id, 'large');
    $img_caption = wp_get_attachment_caption( $post_id );
    $img_alt = get_post_meta($post_id, '_wp_attachment_image_alt', TRUE);
    $scrape_url = get_permalink($_post->ID);
    if ($settings->source_site_id  == 181 ){ //fix magazine urls
        $scrape_url = preg_replace('/%[\s\S]+?%/', '', $scrape_url);
        $scrape_url = preg_replace('/(\/\/\/)/','/',$scrape_url);
    }


    $send_array[] = array(
        'id'            => $_post->ID,
        'post_title'    => $_post->post_title,
        'post_content'  => $_post->post_content,
        'permalink'     => $scrape_url,
        'thumbnail'     => $image[0],
        'caption'       => $img_caption,
        'alt'           => $img_alt,
    );
};

//restore current site
restore_current_blog();

$context = array_merge(
    \Timber\Timber::get_context(),
    array(
        'posts'               => $send_array,
        'section_header'      => $settings->ms_post_header,
    )
);

\Timber\Timber::render('posts-ms.twig', $context);

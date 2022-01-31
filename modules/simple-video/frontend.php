<?php

/**
 * This file should be used to render each module instance.
 * You have access to two variables in this file:
 *
 * $module An instance of your module class.
 * $settings The module's settings.
 *
 */




$context = array_merge(
    \Timber\Timber::get_context(),
    array(
        'videos'               => $settings->simple_video_form,
        'section_header'      => $settings->ms_post_header,
    )
);

\Timber\Timber::render('simple-video.twig', $context);

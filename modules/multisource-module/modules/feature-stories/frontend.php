<?php

/**
 * This file should be used to render each module instance.
 * You have access to two variables in this file:
 *
 * $module An instance of your module class.
 * $settings The module's settings.
 *
 */

$file  = file_get_contents($settings->url_endpoint);
$posts = json_decode($file);

// Get the query data.
$template = isset($settings->layout) ? $settings->template : 'grid';

$context = array(
    'posts'    => $posts,
    'template' => $template,
    'settings' => $settings,
    'module'   => $module,
);

\Timber\Timber::render('feature-stories.twig', $context);
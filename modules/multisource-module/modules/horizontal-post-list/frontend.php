<?php

/**
 * This file should be used to render each module instance.
 * You have access to two variables in this file:
 *
 * $module An instance of your module class.
 * $settings The module's settings.
 *
 */


$file   = file_get_contents($settings->url_endpoint);
$json   = json_decode($file);


$context = array(
    'posts'         => $json,
    'settings'      => $settings,
    'module'        => $module,
);

\Timber\Timber::render('post-list.twig', $context);

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
$file2   = file_get_contents($settings->url_endpoint2);
$json2   = json_decode($file2);

$context = array(
    'posts'         => $json,
    'posts2'        => $json2,
    'settings'      => $settings,
    'module'        => $module,
);

\Timber\Timber::render('www-stories.twig', $context);

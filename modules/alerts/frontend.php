<?php

/**
 * This file should be used to render each module instance.
 * You have access to two variables in this file:
 *
 * $module An instance of your module class.
 * $settings The module's settings.
 *
 */
$cur_alert = json_decode($module->cur_alert);
$context = array_merge(
    \Timber\Timber::get_context(),
    array(
        'alert'         => get_fields($cur_alert->ID),
        'alertpage'     => get_permalink($cur_alert->ID),
        'hideid'          => $cur_alert->ID,
        'settings'      => $settings,
    )
);

\Timber\Timber::render('alerts.twig', $context);
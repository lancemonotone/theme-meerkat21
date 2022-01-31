<?php

/**
 * This file should be used to render each module instance.
 * You have access to two variables in this file:
 *
 * $module An instance of your module class.
 * $settings The module's settings.
 *
 *
 */

$context = \Timber\Timber::get_context();
$context['wms_stats'] = $settings->wms_stats;
$context['node']  = $module->node;
\Timber\Timber::render('wms-stats.twig', $context);

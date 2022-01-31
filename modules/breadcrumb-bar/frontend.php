<?php

namespace m21;

/**
 * This file should be used to render each module instance.
 * You have access to two variables in this file:
 *
 * @global $module object An instance of your module class.
 * @global $settings object The module's settings.
 *
 *
 */

$breadcrumbs = new Breadcrumbs();
$context['breadcrumbs'] = $breadcrumbs->get_breadcrumbs();
$context['node']      = $module->node;
\Timber\Timber::render('breadcrumb-bar.twig', $context);
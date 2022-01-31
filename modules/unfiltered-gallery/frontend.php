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
 $context['ug_photo_ids'] = $settings->ug_photos_field;
\Timber\Timber::render('unfiltered-gallery.twig', $context);
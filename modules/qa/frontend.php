<?php

/**
 * This file should be used to render each module instance.
 * You have access to two variables in this file:
 *
 * $module An instance of your module class.
 * $settings The module's settings.
 *
 */

// $questions = $settings->qa_form;

$context = array_merge(
    \Timber\Timber::get_context(),
    array(
        'settings'  => $settings
    )
);

\Timber\Timber::render('qa.twig', $context);

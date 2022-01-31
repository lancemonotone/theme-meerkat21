<?php

namespace m21;

/**
 * This file should be used to render each module instance.
 * You have access to two variables in this file:
 *
 * $module An instance of your module class.
 * $settings The module's settings.
 *
 */

$file   = file_get_contents($settings->url_endpoint); // url is sheet v4 API formatted
$json   = json_decode($file);
$data   = $json->values;

// Make the data easier to work with by creating Label => Data arrays.
// We do need to know the column labels, but that was always the case.
// They are:
//
//           Statistic, Label, Type, Display, Note
//
$keys = array_shift($data); // First array is labels/keys
for ($i = 0; $i < count($data); $i++) {
    for ($j = 0; $j < count($data[$i]); $j++) {
        if (isset($keys[$j])) {
            $rows[$i][$keys[$j]] = $data[$i][$j];
        }
    }
}

$groups       = array();
$group_colors = array('lichen-dark lightfont', 'purple lightfont', 'lichen lightfont', 'purple-light darkfont', 'gold darkfont');

foreach ($rows as $row) {
    if (!$org_name = $row['Full Name of the Organization']) continue;
    $group_key            = preg_replace('/^The /i', '', $org_name);
    $group_key           = preg_replace('/^Williams (College )?/i', '', $group_key);
    $group_name           = preg_replace('/^Williams (College )?/i', '', $org_name);
    $groups[ $group_key ] = array('name' => $group_name, 'class' => 'student-group');
}
ksort($groups);

// Get the query data.
$template = isset($settings->layout) ? $settings->template : 'grid';

$context = array(
    'groups'     => $groups,
    'template'   => $template,
    'settings'   => $settings,
    'module'     => $module,
);

\Timber\Timber::render('student-groups.twig', $context);

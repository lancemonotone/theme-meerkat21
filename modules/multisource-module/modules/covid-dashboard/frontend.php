<?php

namespace m21;

/**
 * This file should be used to render each module instance.
 * You have access to two variables in this file:
 *
 * $module An instance of your module class.
 * $settings The module's settings.
 *
 *
 * About this module: It's special purpose is to take the
 * Covid Dashboard spreadsheet data, format it in html and 
 * insert it into a webpage. The sheet URL could be hardcoded,
 * but that's not how the multisource module works.
 *
 * As of August 2021 we are using Google Sheet's v4 API with a 
 * developer key.
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

$stats  = array(); // Container for stats we want to display

foreach ($rows as $row) {
    // Skip non-approved rows
    if ($row['Display'] != 'yes') continue;
    $stat = $row['Statistic'];
    if ($row['Type'] == 'percent') {
        $stat .= '%';
    } else if ($row['Type'] == 'integer') {
        // add commas to numbers >= 1000
        $stat = number_format($stat);
    }
    $stat_label = $row['Label'];
    $stats[] = array('stat' => $stat, 'label' => $stat_label, 'type' => $row['Type']);
}

$context = array(
    'stats'     => $stats,
    'settings'   => $settings,
    'module'     => $module,
);

\Timber\Timber::render('covid-dashboard.twig', $context);

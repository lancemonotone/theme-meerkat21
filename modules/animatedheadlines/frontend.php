<?php

/**
 * This file should be used to render each module instance.
 * You have access to two variables in this file:
 *
 * $module An instance of your module class.
 * $settings The module's settings.
 *
 */

$file         = file_get_contents($settings->url_endpoint);
$json         = json_decode($file);
$rows         = $json->{'feed'}->{'entry'};
$max_chars    = isset($settings->max_characters) ? $settings->max_characters : 0;

switch ($settings->data_restriction) {
    case 'first_half':
	     $rows = array_slice($rows, 0, ceil(count($rows) / 2));
	     break;
    case 'second_half':
	     $rows = array_slice($rows, floor(count($rows) / 2));
	     break;
    case 'first_third':
	     $rows = array_slice($rows, 0, ceil(count($rows) / 3));
	     break;
    case 'second_third':
	     $rows = array_slice($rows, floor(count($rows) / 3), floor(count($rows) / 3));
	     break;
    case 'third_third':
	     $rows = array_slice($rows, floor(count($rows) / 3) * 2);
	     break;
}
foreach ($rows as $row) {
    $string = $row->{'gsx$messagetotheclassof2020'}->{'$t'};
	$more = '';
	if ($max_chars && strlen($string) > $max_chars) {
        $string = substr($string, 0, strpos(wordwrap($string, $max_chars), "\n"));
		$more = '...';
	}
    $quotes[] = $string . $more . '<br>' 
	    . '<span class="attribution">' . $row->{'gsx$namepleaseincludeclassyearifyouareawilliamsgraduate'}->{'$t'} . ', ' . $row->{'gsx$title'}->{'$t'} . '</span>';
}

$context = array_merge(
    \Timber\Timber::get_context(),
    array('settings' => $settings, 'quotes' => $quotes)
);

\Timber\Timber::render('animatedheadlines.twig', $context);

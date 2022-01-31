<?php

/**
 * This file should be used to render each module instance.
 * You have access to two variables in this file:
 *
 * $module An instance of your module class.
 * $settings The module's settings.
 *
 */

$num_to_show = 5; // hardcoded to 5 for now



if (!function_exists('curl_init')){ 
    die('CURL is not installed!');
}

$ch = curl_init();

// CURLOPT_VERBOSE: TRUE to output verbose information. Writes output to STDERR,
// or the file specified using CURLOPT_STDERR.
//curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLOPT_URL, $settings->url_endpoint);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla");
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$feed = curl_exec($ch);
$curlinfo = curl_getinfo($ch);
// get error string, if any
//$error = curl_error($ch);
curl_close($ch);

$stories = [];

$xml = new SimpleXMLElement($feed);

foreach ($xml->channel->item as $game) {
    if (preg_match("/\[[lwt]\]/i", $game->{'description'})) {
        $title = array();
        preg_match("/^(\d{1,2}\/\d{1,2}).*\s+\[([lwt])\]\s+Williams College\s+(.*)\s(at|vs)\s(.*)/i", $game->{'title'}, $title);
        $description = explode('\n', $game->{'description'});
        $score = preg_replace("/^[lwt]\s*/i", '', $description[1]);
        // $outcome = ($title[2] == 'W') ? 'defeats' : 'defeated by';

        switch ($title[2]) {
            case "L":
                $outcome = 'defeated by';
                break;
            case "T":
                $outcome = 'tied with';
                break;
            default:
                $outcome = 'defeats';
        } 
        $location = ($title[4] == 'vs') ? 'at home' : 'away';
        $time = strtotime($title[1]);
        $story['title'] = "$title[3]";
        $story['excerpt'] = "$title[1]: Williams $title[3] $outcome $title[5] $score $location.";
        $stories[$time.'-'.$game->guid] = $story;
    }
}

if(empty($stories)) return;

krsort($stories);

$context = array(
    'stories'     => $stories,
    'num_to_show'     => $num_to_show,
    'module'     => $module,
);

\Timber\Timber::render('sports-scores.twig', $context);

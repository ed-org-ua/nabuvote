<?php

if (php_sapi_name() != "cli")
    die("Please use php-cli\n");

require "functions.php";
require "candidates.php";
require "settings.php";

restore_error_handler();
error_reporting(E_ALL);
ini_set('display_errors', 'On');

if (empty($settings['show_res_secret']))
    die("Not configured\n");

if (empty($argv[1]) or $argv[2] != $settings['show_res_secret'])
    die("Usage: php show_res.php <mode> <secret>\n");

if ($argv[1] == "export") {
    export_results();
    exit;
}

if ($argv[1] == "db")
    $res = get_db_results();

if ($argv[1] == "file")
    $res = get_file_results();

$res = transcode_results($res);
show_results($res);
exit;

function get_db_results() {
    $db = db_connect();
    $res = $db->query("SELECT choice FROM ballot_box");
    $out = array();
    while ($obj = $res->fetch_object())
        $out[] = $obj->choice;
    $res->close();
    db_close($db);
    return $out;
}

function get_file_results() {
    global $settings;
    $filename = $settings['public_report'];
    if (!file_exists($filename))
        $filename = '../'.$filename;
    $lines = file($filename);
    if (empty($lines))
        die("File not found $filename\n");
    $out = array();
    foreach ($lines as $s) {
        if (($pos = strpos($s, " SEL=")) === false)
            continue;
        $pos += 5;
        if (($end = strpos($s, " ", $pos)) === false)
            $end = strlen($s);
        $out[] = trim(substr($s, $pos, $end-$pos));
    }
    return $out;
}

function compare_vote($a, $b) {
    $diff = $b['votes'] - $a['votes'];
    if ($diff != 0)
        return $diff;
    return (int)$a['id'] - (int)$b['id'];
}

function transcode_results($res) {
    global $candidates;
    $out = array();
    foreach ($candidates as $c) {
        $id = (string)$c['id'];
        $out[$id] = $c;
        $out[$id]['votes'] = 0;
    }
    foreach ($res as $r) {
        $items = explode(',', $r);
        $uniqs = array();
        foreach ($items as $i) {
            $id = (string)$i;
            if (isset($uniqs[$id]))
                die("two time vote line='$r' item='$i'\n");
            $uniqs[$id] = 1;
            if (empty($out[$id]))
                die("bad vote key line='$r' item='$i'\n");
            $out[$id]['votes'] += 1;
        }
    }
    usort($out, 'compare_vote');
    // merge places
    $votes = -1;
    for ($i = 0; $i < count($out); $i++) {
        if ($votes != $out[$i]['votes']) {
            $votes = $out[$i]['votes'];
            $place = $i + 1;
            $end = 0;
            for ($j = $i + 1; $j < count($out); $j++) {
                if ($votes == $out[$j]['votes'])
                    $end = $j + 1;
                else
                    break;
            }
            if ($end)
                $place = "$place-$end";
        }
        $out[$i]['place'] = "$place";
    }
    return $out;
}

function show_results($res) {
    $n = 0;
    printf("%5s | %8s | %s\n", "No", "Голосів ", "Кандидат");
    printf("----+----------+----------------------------\n");
    foreach ($res as $r) {
        $n += 1;
        printf("%5s | %8d | %2d. %s\n", $r['place'], $r['votes'],
            $r['id'], $r['name']);
    }
}

function results_table($results) {
    $table = '';
    foreach ($results as $c) {
        $table .= sprintf('<tr><th>%s</th>', $c['place']);
        $table .= sprintf('<td class="nowrap">%s (№ %d)</td>',
            h($c['name']), (int)$c['id']);
        $table .= sprintf('<td>%s</td>', h($c['org']));
        $table .= sprintf('<td>%d</td>', $c['votes']);
        $table .= "</tr>\n";
    }
    return $table;
}

function export_results() {
    global $candidates, $settings;
    $current_date = date('Y-m-d H:i:s', time()-900);

    if ($current_date < $settings['close_elections_time'])
        die("Error: elections not cloed. Please wait until ".$settings['close_elections_time']." +15 min.\n");

    $res_db = get_db_results();
    $res_file = get_file_results();

    sort($res_db);
    sort($res_file);

    if (count($res_db) != count($res_file) or $res_db !== $res_file)
        die("Error: results in db and public report not equal\n");

    $out_db = transcode_results($res_db);
    $out_file = transcode_results($res_file);

    if (count($out_db) != count($out_file) or $out_db != $out_file)
        die("Error: results in db and public report not equal\n");

    for ($i = 0; $i < count($out_db); $i++)
        if ($out_db[$i]['votes'] != $out_file[$i]['votes'])
            die("Error: results in db and public report not equal\n");

    if (file_exists($settings['results_html']))
        die("Error: results file already exists\n");

    ob_start();
    $results = $out_file;
    require('templates/table.php');
    $table = ob_get_contents();
    ob_end_clean();

    file_put_contents($settings['results_html'], $table);
    echo("Results saved to ".$settings['results_html']."\n");
}

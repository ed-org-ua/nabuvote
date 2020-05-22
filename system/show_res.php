<?php

if (php_sapi_name() != "cli")
    die("Please use php-cli\n");

require "functions.php";
require "candidates.php";
require "settings.php";

restore_error_handler();
error_reporting(E_ALL);
ini_set('display_errors', 'On');

main($argv);
exit;

function main($argv) {
    global $settings;

    if (empty($settings['show_res_secret']))
        die("Not configured\n");

    if (empty($argv[2]) or $argv[2] != $settings['show_res_secret'])
        die("Usage: php show_res.php <mode> <secret>\n");

    if ($argv[1] == "export")
        return export_results();

    if ($argv[1] == "check")
        return check_hashes();

    else if ($argv[1] == "db")
        $res = get_db_results();

    else if ($argv[1] == "file")
        $res = get_file_results();

    else
        die("Error: unknown command\n");

    $res = transcode_results($res);
    show_results($res);
}

function get_db_results() {
    $db = db_connect();
    $res = $db->query("SELECT id,choice FROM ballot_box");
    $out = array();
    while ($obj = $res->fetch_object())
        $out[intval($obj->id)] = $obj->choice;
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
        if (($pos = strpos($s, " ID=")) === false)
            continue;
        $pos += 4;
        $end = strpos($s, " ", $pos+1);
        $id = intval(substr($s, $pos, $end-$pos));
        if (isset($out[$id]))
            trigger_error("Record with ID=$id already exists", E_USER_ERROR);

        if (($pos = strpos($s, " SEL=")) === false)
            continue;
        $pos += 5;
        if (($end = strpos($s, " ", $pos)) === false)
            $end = strlen($s);
        $out[$id] = trim(substr($s, $pos, $end-$pos));
    }
    return $out;
}

function compare_vote($a, $b) {
    $diff = $b['votes'] - $a['votes'];
    if ($diff == 0)
        $diff = strnatcmp($a['id'], $b['id']);
    return $diff;
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
    printf("%5s | %8s | %s\n", "Місце", "Голосів ", "Кандидат");
    printf("------+----------+----------------------------\n");
    foreach ($res as $r) {
        $n += 1;
        printf("%5s | %8d | %2d. %s\n", $r['place'], $r['votes'],
            $r['id'], $r['name']);
    }
}

function check_hashes() {
    global $settings;
    $public_lines = file($settings['public_report']);
    $hashed_lines = file($settings['hashed_report']);

    if (count($public_lines) != count($hashed_lines))
        die("Error: lines count mismatch\n");

    for ($i = 0; $i < count($public_lines); $i++) {
        $ps = $public_lines[$i];
        $hs = $hashed_lines[$i];
        $epos = strpos($ps, " EML=");
        if ($epos === false || $epos < 40)
            die("Error on line $i, EML not found\n");
        if (substr($ps, 0, $epos) != substr($hs, 0, $epos))
            die("Error on line $i, date/id mismatch\n");
        $hpos = strpos($hs, " HASH=");
        if ($hpos === false || $hpos != $epos)
            die("Error on line $i, date/id mismatch\n");
        $forhash = trim(substr($ps, $epos));
        $hash = "HASH=".hash_logline($forhash);
        if (trim(substr($hs, $hpos)) != $hash)
            die("Error on line $i, hash mismatch\n");
        echo $hs;
    }
    echo "$i lines OK\n";
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

    if (count($res_db) != count($res_file) or $res_db !== $res_file)
        die("Error: results in db and public report not equal\n");

    // check for holes
    for ($i = 1; $i <= count($res_db); $i++)
        if (empty($res_db[$i]) || $res_db[$i] != $res_file[$i])
            die("Error: results in db and public report not equal\n");

    $out_db = transcode_results($res_db);
    $out_file = transcode_results($res_file);

    if (count($out_db) != count($out_file) or $out_db != $out_file)
        die("Error: results in db and public report not equal\n");

    if (empty($settings['results_html']) || strlen($settings['results_html']) < 20)
        die("Error: settings[results_html] not set, please update settings.\n");

    if (file_exists($settings['results_html']) && filesize($settings['results_html']) > 20)
        die("Error: results file already exists\n");

    ob_start();
    $results = $out_file;
    require('templates/table.php');
    $table = ob_get_contents();
    ob_end_clean();

    file_put_contents($settings['results_html'], $table);
    echo("Results saved to ".$settings['results_html']."\n");
    return true;
}

<?php

if (php_sapi_name() != "cli")
    die("Please use php-cli\n");

require "functions.php";
require "candidates.php";
require "settings.php";

set_error_handler(NULL);

if (empty($settings['show_res_secret']))
    die("Not configured\n");

if ($argv[2] != $settings['show_res_secret'])
    die("Usage: php show_res.php <mode> <secret>\n");

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
    db_close();
    return $out;
}

function get_file_results() {
    global $settings;
    $filename = $settings['public_report'];
    if (file_exists($filename))
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
    return $b['votes'] - $a['votes'];
}

function transcode_results($res) {
    global $candidates;
    $out = array();
    foreach ($candidates as $c) {
        $id = (int)$c['id'];
        $out[$id] = $c;
        $out[$id]['votes'] = 0;
    }
    foreach ($res as $r) {
        $items = explode(',', $r);
        foreach ($items as $i) {
            $id = (int)$i;
            if (!$id || empty($out[$id]))
                die("Error: bad vote id=$i\n");
            $out[$id]['votes'] += 1;
        }
    }
    usort($out, 'compare_vote');
    return $out;
}

function show_results($res) {
    $n = 0;
    printf("%3s | %8s | %s\n", "No", "Голосів ", "Кандидат");
    printf("----+----------+----------------------------------------\n");
    foreach ($res as $r) {
        $n += 1;
        printf("%3d | %8d | %2d. %s\n", $n, $r['votes'],
            $r['id'], $r['name']);
    }
}

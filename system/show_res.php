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

    if (empty($argv[2])) {
        fwrite(STDERR, "Password: ");
        system('stty -echo');
        $secret = trim(fgets(STDIN));
        system('stty echo');
        fwrite(STDERR, "\n");
    } else {
        $secret = $argv[2];
    }

    if (empty($argv[1]) or sha1($secret) != $settings['show_res_secret'])
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

function get_db_results_full() {
    $db = db_connect();
    $res = $db->query("SELECT id,ts,ip_addr,email,mobile,choice FROM ballot_box");
    $out = array();
    while ($row = $res->fetch_assoc())
        $out[intval($row['id'])] = $row;
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

function get_file_results_full() {
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
        $p = explode(" ", $s);
        $row = array(
            'id' => substr($p[2], 3),
            'ts' => $p[0]." ".$p[1],
            'ip_addr' => substr($p[3], 3),
            'email' => substr($p[4], 4),
            'mobile' => substr($p[5], 4),
            'choice' => substr($p[6], 4)
        );
        $id = intval($row['id']);
        if (isset($out[$id]))
            trigger_error("Record with ID=$id already exists", E_USER_ERROR);
        $out[$id] = $row;
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
    if (empty($res))
        die("Error: empty results\n");
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

function export_results() {
    global $candidates, $settings;
    $current_date = date('Y-m-d H:i:s', time()-900);

    if ($current_date < $settings['close_elections_time'])
        die("Error: elections not cloed. Please wait until ".$settings['close_elections_time']." +15 min.\n");

    $res_db = get_db_results_full();
    $res_file = get_file_results_full();

    if (empty($res_db) or count($res_db) != count($res_file))
        die("Error: results in db and public report not equal\n");

    $out = array();
    foreach ($candidates as $c) {
        $id = (string)$c['id'];
        $out[$id] = array();
    }
    foreach ($res_db as $k => $row) {
        $ref = $res_file[$k];
        if ($row['choice'] != $ref['choice'])
            trigger_error("Record ID=$k choice mismatch", E_USER_ERROR);
        $uniq = array();
        $sel = explode(",", $row['choice']);
        unset($row['choice']);
        foreach ($sel as $c) {
            if (!isset($out[$c]))
                trigger_error("Record ID=$k candidate $c not found", E_USER_ERROR);
            if (isset($uniq[$c]))
                trigger_error("Record ID=$k two time vote $c", E_USER_ERROR);
            $out[$c][] = $row;
            $uniq[$c] = 1;
        }
    }
    printf("Key,Count,E-Mail,Mobile,IP Addr,Uniq IP,IPs 2-5,IPs 6+,\r\n");
    foreach ($out as $k => $rows) {
        $ips = array();
        foreach ($rows as $r) {
            $ip = $r['ip_addr'];
            if (empty($ips[$ip]))
                $ips[$ip] = 1;
            else
                $ips[$ip] += 1;
        }
        $ip1 = $ip25 = $ip6 = 0;
        foreach ($ips as $n) {
            if ($n <= 1)
                $ip1 += 1;
            else if ($n <= 5)
                $ip25 += $n;
            else
                $ip6 += $n;
        }
        $c = count($rows);
        printf("%s,%d,-,-,-,%d,%d,%d,\r\n", $k, $c, $ip1, $ip25, $ip6);
        foreach ($rows as $r) {
            printf("%s,-,\"%s\",\"x%s\",\"%s\",,,,\r\n",
                $k, $r['email'], $r['mobile'], $r['ip_addr']);
        }
        printf("-,,,,,,,,\r\n");
    }
}

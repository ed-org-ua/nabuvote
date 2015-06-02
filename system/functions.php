<?php

/**
 * Short alias for htmlspecialchars
 */
function h($s) {
    return htmlspecialchars($s);
}

/**
 * return verified arg from $_POST or empty string
 */
function post_arg($name, $filter=false, $pattern=false, $maxlen=250) {
    if (empty($_POST[$name]))
        return "";
    if (strlen($value = trim($_POST[$name])) > $maxlen)
        return "";
    if ($value && $filter)
        $value = call_user_func($filter, $value);
    if ($pattern && !preg_match($pattern, $value))
        return "";
    return $value;
}

/**
 *
 */
function get_csrf_token() {
    if (empty($_SESSION['csrf_token']))
        $_SESSION['csrf_token'] = uniqid(mt_rand(), true);
    return $_SESSION['csrf_token'];
}

/**
 *
 */
function csrf_token_input() {
    return '<input type="hidden" name="csrf_token" value="'.
        h(get_csrf_token()).'">';
}

/**
 *
 */
function check_csrf_token() {
    if (empty($_SESSION['csrf_token']))
        return false;
    if ($_POST['csrf_token'] != $_SESSION['csrf_token'])
        die("csrf protection");
    unset($_SESSION['csrf_token']);
}

/**
 *
 */
function check_request_referer() {
    if (!empty($_SERVER['HTTP_HOST']))
        $host = $_SERVER['HTTP_HOST'];
    else
        $host = $_SERVER['SERVER_NAME'];
    if (empty($host))
        return false;
    $ref = parse_url($_SERVER['HTTP_REFERER']);
    if (!empty($ref['host']) && strcasecmp($host, $ref['host']) != 0) {
        if (empty($_GET['error']))
            redirect('index.php?error=bad_referer');
        else
            die("Bad referer");
    }
}

/**
 * Simple remove + - () and spaces from mobile number
 */
function clean_mobile($mobile) {
    $mobile = preg_replace('/[\+\-\(\)\s]/', '', $mobile);
    if (strlen($mobile) == 10)
        $mobile = "38".$mobile;
    return $mobile;
}

/**
 * Simple redirect and die
 */
function redirect($location) {
    header('Location: '.$location);
    die;
}

/**
 * Append error message to global $_ERRORS array
 */
function append_error($msg) {
    global $_ERRORS;
    if (empty($_ERRORS))
        $_ERRORS = array();
    $_ERRORS[] = $msg;
}

/**
 * print global $_ERRORS array in div class=alert
 */

function print_errors() {
    global $_ERRORS;
    if ($_ERRORS) {
        array_walk($_ERRORS, 'htmlspecialchars');
        print('<div class="alert alert-danger" role="alert">');
        print(implode('<br>', $_ERRORS).'</div>');
    }
}

/**
 * return full remote addr include ip behind proxy
 */
function full_remote_addr() {
    $ip_addr = $_SERVER['REMOTE_ADDR'];
    if (!empty($_SERVER['HTTP_CLIENT_IP']))
        $ip_addr .= "/".$_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ip_addr .= "/".$_SERVER['HTTP_X_FORWARDED_FOR'];
    return $ip_addr;
}

/**
 * Save message to debug.log
 */
function log_debug($func, $msg="-") {
    global $settings;
    if (!($filename = $settings['debug_log']))
        return false;
    if (!($fp = fopen($filename, "at")))
        return false;
    $logline = date("Y-m-d H:i:s").substr(microtime(), 1, 4);
    $logline .= " ".full_remote_addr();
    $logline .= " ".session_id();
    $logline .= " ".http_build_query($_SESSION);
    $logline .= " ".$func;
    $logline .= " ".$msg."\r\n";
    if (flock($fp, LOCK_EX))
        fwrite($fp, $logline);
    flock($fp, LOCK_UN);
    fclose($fp);
}

/**
 * Error handler with save to debug.log
 */
function debug_error_handler($errno, $errstr, $errfile, $errline) {
    log_debug("$errfile:$errline", "Error($errno) $errstr");
}

/**
 * return true if reCAPTCHA verified
 */
function captcha_verify() {
    global $settings;
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $privatekey = $settings['recaptcha_secret'];
    $response = file_get_contents($url.
        "?secret=".$privatekey.
        "&response=".$_POST['g-recaptcha-response'].
        "&remoteip=".$_SERVER['REMOTE_ADDR']);
    $data = json_decode($response);
    if (isset($data->success) && $data->success == true) {
        return true;
    }
    return false;
}

/**
 * start the session and set basic limits
 */
function init_user_session() {
    global $settings;
    session_set_cookie_params($settings['session_lifetime']);
    if (session_id())
        session_destroy();
    session_start();
    $_SESSION = array();
    $_SESSION['ip_addr'] = full_remote_addr();
    $_SESSION['expires'] = time() + $settings['session_lifetime'];
    $_SESSION['total_post_limit'] = $settings['total_post_limit'];
    $_SESSION['check_email_limit'] = $settings['check_email_limit'];
    $_SESSION['check_mobile_limit'] = $settings['check_mobile_limit'];
    log_debug('init_user_session');
}

/**
 * verify basic session restrictions
 */
function check_session_limits() {
    if ($_SESSION['ip_addr'] != full_remote_addr())
        return false;
    if ($_SESSION['expires'] < time())
        return false;
    return true;
}

/**
 * returns current session left time in seconds
 */
function current_session_lifetime() {
    $left = $_SESSION['expires'] - time();
    if ($left < 0)
        $left = 0;
    return $left;
}

/**
 *
 */
function get_selected_limit() {
    global $settings;
    if (empty($settings['max_selected_limit']))
        return 15;
    return $settings['max_selected_limit'];
}

/**
 * database abstract layer - connect
 */
function db_connect() {
    global $settings;
    $db = mysqli_connect(
        $settings['mysql_host'],
        $settings['mysql_user'],
        $settings['mysql_password'],
        $settings['mysql_database'])
    or die("Can't connect to database");
    return $db;
}

/**
 * database abstract layer - close
 */
function db_close($db) {
    mysqli_close($db);
}

/**
 * database abstract layer - test row exists by unique key
 */
function db_row_exists($db, $key, $value, $table="ballot_box") {
    // $key and $table isn't user data so we can use it safe w/o escaping
    $stmt = $db->prepare("SELECT $key FROM $table WHERE $key = ? LIMIT 1");
    $stmt->bind_param("s", $value);
    if ($stmt && $stmt->execute() && $stmt->store_result())
        return $stmt->num_rows;
    return 1; // if query fails we assume it as row exists
}

/**
 * database abstract layer - insert single row from assoc array
 */
function db_insert_row($db, $row, &$insert_id, $table="ballot_box") {
    $keys = implode(",", array_keys($row));
    $count = count($row);
    $types = str_repeat("s", $count);
    $values = substr(str_repeat(",?", $count), 1);
    $stmt = $db->prepare("INSERT INTO $table ($keys) VALUES ($values)");
    // Care must be taken when using mysqli_stmt_bind_param() in conjunction
    // with call_user_func_array(). Note that mysqli_stmt_bind_param() requires
    // parameters to be passed by reference, whereas call_user_func_array() can
    // accept as a parameter a list of variables that can represent references
    // or values. From http://php.net/manual/en/mysqli-stmt.bind-param.php
    $bind_args = array($types);
    foreach ($row as &$r)
        $bind_args[] = &$r;
    call_user_func_array(array($stmt, 'bind_param'), $bind_args);
    if ($stmt && $stmt->execute()) {
        $insert_id = $stmt->insert_id;
        return ($stmt->affected_rows > 0);
    }
    return false;
}

/**
 * check for previous used email
 */
function email_not_used($email) {
    $db = db_connect();
    $res = db_row_exists($db, 'email', $email);
    db_close($db);
    log_debug('email_not_used', $email." res=".$res);
    return ($res == 0);
}

/**
 * check for previous used mobile number
 */
function mobile_not_used($mobile) {
    $db = db_connect();
    $res = db_row_exists($db, 'mobile', $mobile);
    db_close($db);
    log_debug('mobile_not_used', $mobile." res=".$res);
    return ($res == 0);
}

/**
 *
 */
function send_email_code($email, $code) {
    global $settings;
    if (strpos($email, "\n") !== false)
        return false;
    if (strpos($email, ",") !== false)
        return false;
    $headers = "From: ".$settings['email_from_header']."\r\n".
        "MIME-Version: 1.0\r\n".
        "Content-Type: text/plain; charset=\"UTF-8\"\r\n".
        "Content-Transfer-Encoding: binary\r\n".
        "Content-Disposition: inline";
    $subject = $settings['email_subject_header'];
    $message = "Код перевірки {$code}\r\n";
    if ($settings['email_base_url']) {
        $message .= "\r\n";
        $message .= "або перейдіть ".$settings['email_base_url'].$code;
        $message .= "\r\n";
    }
    mail($email, $subject, $message, $headers);
    log_debug('send_email_code', "to=$email");
}

/**
 * send SMS via Kyivstar CPI
 */
function send_mobile_code($mobile, $code) {
    global $settings;
    if (!preg_match('/^380\d{9}$/', $mobile))
        return false;
    $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".
        '<message mid="%s" paid="%s" bearer="SMS">'."\n".
        '<sn>NAB vote</sn><sin>%s</sin>'."\n".
        '<body content-type="text/plain">%s</body></message>';
    $mid = time().".".$mobile;
    $sin = $mobile;
    $paid = $settings['kyivstar_cpi_paid'];
    $text = "Kod proverki ".$code;
    $postdata = sprintf($xml, $mid, $paid, $sin, $text);
    $url = $settings['kyivstar_cpi_url'];
    $username = $settings['kyivstar_cpi_username'];
    $password = $settings['kyivstar_cpi_password'];
    $curlopts = array(
        CURLOPT_URL => $url,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => "$username:$password",
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $postdata,
    );
    $ch = curl_init();
    curl_setopt_array($ch, $curlopts);
    $res = curl_exec($ch);
    curl_close($ch);
    $res = strtr($res, "\r\n", "  ");
    $res = "mid=$mid sin=$sin ".$res;
    log_debug("send_mobile_code", $res);
}

/**
 * check and decrease limit by name
 */
function check_and_dec_limit($name, $start='step1.php') {
    if ((int)$_SESSION[$name] < 1)
        redirect($start);
    $_SESSION[$name] -= 1;
}

/**
 * set some test (captcha, email, mobile) as passed
 */
function set_test_passed($name) {
    $_SESSION[$name.'_pass'] = 1;
    log_debug('set_test_passed', $name);
}

/**
 * check test or redirect to start
 */
function require_test_pass($name, $start='step1.php') {
    if (empty($_SESSION[$name.'_pass']))
        redirect($start);
}

/**
 *
 */
function next_if_test_pass($name, $next) {
    if (!empty($_SESSION[$name.'_pass']))
        redirect($next);
}

/**
 * unset passed tests on final page
 */
function clean_passed_tests($tests) {
    foreach ($tests as $name)
        unset($_SESSION[$name.'_pass']);
}

/**
 * anonymize ip address
 */
function anon_ipaddr($ip) {
    $arr = explode('.', $ip, 4);
    $arr[2] = '***';
    return implode('.', $arr);
}

/**
 * anonymize email address
 */
function anon_email($email) {
    $p = explode('@', $email, 2);
    $n = strlen($p[0]) < 6 ? 2 : 4;
    $d = strlen($p[1]) < 6 ? 4 : 6;
    $p[0] = substr($p[0], 0, $n).'***';
    $p[1] = '***'.substr($p[1], -1*$d);
    return implode('@', $p);
}

/**
 * anonymize mobile number
 */
function anon_mobile($mob) {
    return substr($mob, 0, 5)."***".substr($mob, 8);
}

/**
 * save vote using database abstraction layer api
 */
function save_vote_database($table="ballot_box") {
    $db = db_connect();
    $row = array();
    $row['ip_addr'] = $_SESSION['ip_addr'];
    $row['email'] = $_SESSION['email_value'];
    $row['mobile'] = $_SESSION['mobile_value'];
    $row['choice'] = implode(',', $_SESSION['vote_keys']);
    if (db_row_exists($db, 'email', $row['email']))
        append_error("Такий e-mail вже проголосував.");
    if (db_row_exists($db, 'mobile', $row['mobile']))
        append_error("Такий мобільний вже проголосував.");
    if (db_insert_row($db, $row, $ballot_id) == false)
        append_error("Запис голосу не вдався.");
    $_SESSION['ballot_id'] = $ballot_id;
}

/**
 * save vote to public report
 */
function save_vote_public() {
    global $settings, $_ERRORS;
    $logline = date("Y-m-d H:i:s").substr(microtime(), 1, 4);
    $logline .= " ID=".(string)$_SESSION['ballot_id'];
    $logline .= " IP=".anon_ipaddr($_SESSION['ip_addr']);
    $logline .= " EML=".anon_email($_SESSION['email_value']);
    $logline .= " MOB=".anon_mobile($_SESSION['mobile_value']);
    $logline .= " SEL=".implode(',', $_SESSION['vote_keys']);
    if (!empty($settings['public_mac_algo'])) {
        $logline .= " MAC=".
            hash_hmac($settings['public_mac_algo'],
            $logline, $settings['public_mac_key']);
    }
    if ($_ERRORS)
        $logline .= " WITH_ERRORS";
    $logline .= "\r\n";
    if (!($filename = $settings['public_report']))
        return false;
    if (!($fp = fopen($filename, "at")))
        return false;
    if (flock($fp, LOCK_EX))
        fwrite($fp, $logline);
    flock($fp, LOCK_UN);
    fclose($fp);
}

/**
 *
 */
function safe_save_vote($keys) {
    global $_ERRORS;
    $_SESSION['vote_time'] = time();
    $_SESSION['vote_keys'] = $keys;
    save_vote_database();
    save_vote_public();
    log_debug("save_vote", implode(",", $keys).
        " errors=".count($_ERRORS));
    return (count($_ERRORS) == 0);
}

/**
 * return full template filename for use in include
 */
function get_template($name) {
    return "system/templates/{$name}.php";
}

/**
 * return html candidates from array of ids
 */
function keys_to_candidates($keys) {
    if (empty($candidates))
        require("candidates.php");
    $list = array();
    foreach ($candidates as $c) {
        if (in_array($c['id'], $keys)) {
            $list[] = sprintf("%d. %s",
                $c['id'], h($c['name']));
        }
    }
    return implode('<br>', $list);
}

/**
 * return html table with candidates
 */
function candidates_table($form=false) {
    if (empty($candidates))
        require("candidates.php");
    $table = '';
    foreach ($candidates as $c) {
        $table .= '<tr>';
        if ($form) {
            $table .= sprintf('<td><input type="checkbox" '.
                'id="id_%d" name="id[%d]"></td>',
                (int)$c['id'], (int)$c['id']);
        }
        $table .= sprintf('<td><label for="id_%d">%d. %s</label></td>',
            (int)$c['id'], (int)$c['id'], h($c['name']));
        $table .= sprintf('<td>%s</td>', h($c['org']));
        $table .= sprintf('<td class="nowrap"><a href="%s%s">',
            'http://nabu.gov.ua/', h($c['link']));
        $table .= 'досьє</a></td>';
        $table .= "</tr>\n";
    }
    return $table;
}

# vim: syntax=php ts=4

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
 * return meta refresh tag for fatal errors
 */
function goto_on_die($location='index.php') {
    return ' <meta http-equiv="refresh" content="5;URL='.h($location).'"/>';
}

/**
 * return random csrf token
 */
function get_csrf_token() {
    // Of course uniqid(rand) is not cryptographically secure
    // but glibc rand() based on LFSR in combination with CAPTCHA and
    // time-limited sessions is good enough for this particular task
    if (empty($_SESSION['csrf_token']))
        $_SESSION['csrf_token'] = uniqid(rand(), true);
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
    if ($_POST['csrf_token'] != $_SESSION['csrf_token']) {
        log_debug("check_csrf_token", "csrf protection");
        die("csrf protection ".goto_on_die('step1.php'));
    }
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
    if (empty($host)) {
        log_debug("check_request_referer", "empty host");
        return false;
    }
    $ref = parse_url($_SERVER['HTTP_REFERER']);
    if (!empty($ref['host']) && strcasecmp($host, $ref['host']) != 0) {
        log_debug("check_request_referer", "not match ".$ref['host']);
        // protect against redirect loops
        if (empty($_GET['error']))
            redirect('index.php?error=bad_referer');
        else
            die("Bad referer");
    }
}

/**
 * return filtered inval code, remove spaces and dahses
 */
function clean_intval($var) {
    $var = preg_replace("/\D/", "", $var);
    return intval($var);
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
    echo('<meta http-equiv="refresh" content="0;URL='.$location.'"/>');
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
    log_debug("append_error", $msg);
}

/**
 * print global $_ERRORS array in div class=alert
 */

function print_errors() {
    global $_ERRORS;
    if ($_ERRORS) {
        $safe_errors = array_map('htmlspecialchars', $_ERRORS);
        print('<div class="alert alert-danger" role="alert">');
        print(implode('<br>', $safe_errors).'</div>');
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
    // http headers data are possibly unsafe
    if (strpbrk($ip_addr, " ,;'=\"\t\r\n"))
        $ip_addr = $_SERVER['REMOTE_ADDR'];
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
    if (!empty($_SESSION))
        $session_data = http_build_query($_SESSION);
    else
        $session_data = "-";
    $logline = date("Y-m-d H:i:s").substr(microtime(), 1, 4);
    $logline .= " ".full_remote_addr();
    $logline .= " ".session_id();
    $logline .= " ".$_SERVER['REQUEST_URI'];
    $logline .= " ".$session_data;
    $logline .= " ".$func;
    $logline .= " ".$msg."\r\n";
    if (flock($fp, LOCK_EX))
        fwrite($fp, $logline);
    flock($fp, LOCK_UN);
    fclose($fp);
}

/**
 *  log all post data
 */
function log_debug_post_data() {
    $post_data = http_build_query($_POST);
    log_debug("RAW_POST_DATA", $post_data);
}

/**
 * Error handler with save to debug.log
 */
function debug_error_handler($errno, $errstr, $errfile, $errline) {
    log_debug("$errfile:$errline", "Error($errno) $errstr");
}

/**
 * one time random seed using openssl
 */
function safe_seed_random() {
    $b = openssl_random_pseudo_bytes(4);
    $i = unpack('L', $b);
    if (empty($i[1]))
        $i[1] = 10000 * microtime(true);
    srand((int)$i[1]);
}

/**
 * rand with extra seed (may be slow)
 */
function safe_rand($min, $max, $times=1) {
    safe_seed_random();
    for($res = ""; $times > 0; $times--)
        $res .= rand($min, $max);
    return $res;
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
    if ($_SESSION['ip_addr'] != full_remote_addr()) {
        log_debug("check_session_limits", "ip not match");
        return false;
    }
    if ($_SESSION['expires'] < time()) {
        log_debug("check_session_limits", "session expired");
        return false;
    }
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
 * database abstract layer - count by columnt value
 */
function db_row_count($db, $key, $value, $table="ballot_box") {
    // $key and $table isn't user data so we can use it safe w/o escaping
    $stmt = $db->prepare("SELECT COUNT(1) FROM $table WHERE $key = ?");
    $stmt->bind_param("s", $value);
    if ($stmt && $stmt->execute() && $stmt->store_result()) {
        $count = 0; $stmt->bind_result($count);
        if($stmt->fetch())
            return $count;
    }
    return 1; // if query fails we assume it as only one row exists
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
 * load ip addr map from exceptions.txt
 */
function load_ip_addr_exceptions() {
    $lines = @file("system/exceptions.txt");
    $exceptions = array();
    foreach ($lines as $line) {
        $p = explode("=", $line);
        $exceptions[trim($p[0])] = (int)$p[1];
    }
    return $exceptions;
}

/**
 * check limits based on IP address, return true if exceeded
 */
function check_ip_addr_limits() {
    global $settings;
    if (empty($settings['votes_per_ip_limit']))
        return false;
    $ip_addr = $_SESSION['ip_addr'];
    $db = db_connect();
    $res = db_row_exists($db, 'ip_addr', $ip_addr);
    if ($res > 0)
        $res = db_row_count($db, 'ip_addr', $ip_addr);
    db_close($db);
    if ($res < $settings['votes_per_ip_limit'])
        return false;
    $exceptions = load_ip_addr_exceptions();
    foreach ($exceptions as $key => $limit) {
        if ((strpos($ip_addr, $key) === 0) && ($res < $limit))
            return false;
    }
    log_debug("check_ip_addr_limits", "res=$res");
    return true;
}

/**
 * return session expire value in HH:MM
 */
function session_expires_hhmm() {
    return date("H:i", $_SESSION['expires']);
}

/**
 *
 */
function format_secret_code($code) {
    return implode("-", str_split($code, 2));
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
    $subject = $settings['email_subject_header'] . date(" H:m");
    $code = format_secret_code($code);
    $message = "Код перевірки {$code}\r\n";
    if (!empty($settings['email_code_url'])) {
        $message .= "\r\n";
        $message .= "або перейдіть ".$settings['email_base_url'].$code;
        $message .= "\r\n";
    }
    $message .= "\r\n"."дійсний до ".session_expires_hhmm()."\r\n";
    mail($email, $subject, $message, $headers);
    log_debug('send_email_code', "to=$email");
}

function send_summary_email($publine, $logline) {
    global $settings;
    $email = $_SESSION['email_value'];
    $selected = implode(',', $_SESSION['vote_keys']);
    if (strpos($email, "\n") !== false)
        return false;
    if (strpos($email, ",") !== false)
        return false;
    $headers = "From: ".$settings['email_from_header']."\r\n".
        "MIME-Version: 1.0\r\n".
        "Content-Type: text/plain; charset=\"UTF-8\"\r\n".
        "Content-Transfer-Encoding: binary\r\n".
        "Content-Disposition: inline";
    $vcodes = substr($logline, strpos($logline, " K1="));
    $subject = "=?UTF-8?b?0JLQsNGIINCz0L7Qu9C+0YEg0LfQsdC10YDQtdC20LXQvdC+?=";
    $message = "Дякуємо що проголосували!\r\n"."\r\n".
        "Ви обрали кандидатів з номерами: {$selected}\r\n"."\r\n".
        "\r\n".
        "до кінця голосування ваш голос в протоколі буде закодованим і виглядатиме так:\r\n".
        "{$publine}\r\n".
        "\r\n".
        "З повагою,\r\n".
        "Розробники системи рейтингового інтернет-голосування.\r\n".
        "Запитання та зауваження надсилайте на info@mva.gov.ua";
    mail($email, $subject, $message, $headers);
    log_debug('send_summary_email', "to=$email");
}

/**
 *  check mobile code before send
 */
function check_mobile_operator_code($mobile) {
    $allowed_codes = array(
        "50", // МТС, Vodafone Україна
        "63", // Lifecell
        "66", // МТС, Vodafone Україна
        "67", // Київстар
        "68", // Київстар
        "73", // Lifecell
        "91", // ТриМоб
        "92", // PEOPLEnet
        "93", // Lifecell
        "95", // МТС, Vodafone Україна
        "96", // Київстар
        "97", // Київстар
        "98", // Київстар
        "99"  // МТС, Vodafone Україна
    );
    if (substr($mobile, 0, 3) !== "380")
        return false;
    $code = substr($mobile, 3, 2);
    return in_array($code, $allowed_codes);
}

/**
 * send SMS via Kyivstar CPI (new format)
 */
function send_mobile_code_new($mobile, $code) {
    global $settings;
    if (!preg_match('/^380\d{9}$/', $mobile))
        return false;
    $xml = '<?xml version="1.0" encoding="UTF-8"?>'.
        '<message xmlns="http://goldetele.com/cpa">'.
        '<login>%s</login>'.
        '<paswd>%s</paswd>'.
        '<tid>1</tid>'.
        '<sin>%s</sin>'.
        '<service>bulk-request</service>'.
        '<body content-type="text/plain">%s</body>'.
        '</message>';
    $code = format_secret_code($code);
    $text = "Kod perevirky $code \n".
        "dijsnyj do ".session_expires_hhmm();
    $url = $settings['kyivstar_cpi_url'];
    $username = $settings['kyivstar_cpi_username'];
    $password = $settings['kyivstar_cpi_password'];
    $postdata = sprintf($xml, $username, $password,
        $mobile, $text);
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
    preg_match('/mid="(\d+)"/', $res, $m);
    $mid = isset($m[1]) ? $m[1] : "-";
    $sin = $mobile;
    $res = strtr($res, "\r\n", "  ");
    $res = "mid=$mid sin=$sin ".$res;
    log_debug("send_mobile_code", $res);
}

/**
 * send SMS via Kyivstar CPI (old format)
 */
function send_mobile_code_old($mobile, $code) {
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
    $text = "Kod perevirky $code \n".
        "dijsnyj do ".session_expires_hhmm();
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
 * Send SMS router
 */
function send_mobile_code($mobile, $code) {
    global $settings;
    if ($settings['kyivstar_cpi_new'])
        send_mobile_code_new($mobile, $code);
    else
        send_mobile_code_old($mobile, $code);
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
    if (empty($_SESSION[$name.'_pass'])) {
        log_debug("require_test_pass", "$name redirect to $start");
        redirect($start);
    }
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
    $unsafe = " ,;'=\"\t\r\n";
    if (strpbrk($ip, $unsafe))
        $ip = "UN.SA.*.FE";
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
 * hash user voting data for intermediate logfile
 */
function hash_logline($data) {
    $n = 100000;
    while ($n--)
        $data = hash("sha256", $data, 1);
    return bin2hex($data);
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
 * search encodedd (hashed) value in public log
 */
function search_log_line($args) {
    global $settings;
    // these keys will be hashed
    $forhash = "EML=".anon_email($args['email_value']);
    $forhash .= " MOB=".anon_mobile($args['mobile_value']);
    $forhash .= " SEL=".$args['vote_keys'];
    // salt logline with known rand values before hashing
    $forhash .= " K1=".$args['email_code'];
    $forhash .= " K2=".$args['mobile_code'];
    // construct lines
    $publine = " HASH=".hash_logline($forhash);
    $logline = $forhash;
    $foundline = "(не знайдено)";
    if (($filename = $settings['hashed_report']) !== false) {
        if (($fp = fopen($filename, "r")) !== false) {
            while (($s = fgets($fp, 500)) !== false) {
                if (strpos($s, $publine) !== false)
                    $foundline = $s;
            }
            fclose($fp);
        }
    }
    return array($publine, $logline, $foundline);
}

/**
 * save vote to public report
 */
function save_vote_public() {
    global $settings, $_ERRORS;
    if (empty($_SESSION['ballot_id']))
        return false;
    $logbase = date("Y-m-d H:i:s").substr(microtime(), 1, 4);
    $logbase .= " ID=".(string)$_SESSION['ballot_id'];
    $logbase .= " IP=".anon_ipaddr($_SESSION['ip_addr']);
    // these keys will be hashed
    $forhash = "EML=".anon_email($_SESSION['email_value']);
    $forhash .= " MOB=".anon_mobile($_SESSION['mobile_value']);
    $forhash .= " SEL=".implode(',', $_SESSION['vote_keys']);
    // salt logline with known rand values before hashing
    $forhash .= " K1=".$_SESSION['email_code'];
    $forhash .= " K2=".$_SESSION['mobile_code'];
    // construct lines
    $publine = $logbase." HASH=".hash_logline($forhash)."\r\n";
    $logline = $logbase." ".$forhash;
    if ($_ERRORS)
        $logline .= " WITH_ERRORS";
    if (strpbrk($logline, "\r\n"))
        $logline = strtr($logline, "\r\n", "  ")." UNSAFE_DATA";
    $logline .= "\r\n";
    // first write public_report
    if (($filename = $settings['public_report']) !== false) {
        if (($fp = fopen($filename, "at")) !== false) {
            if (flock($fp, LOCK_EX))
                fwrite($fp, $logline);
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }
    // and then hashed_report
    if (($filename = $settings['hashed_report']) !== false) {
        if (($fp = fopen($filename, "at")) !== false) {
            if (flock($fp, LOCK_EX))
                fwrite($fp, $publine);
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }
    // send notification email
    send_summary_email($publine, $logline);
}

/**
 * save selected candidates to database and public report
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
 *  get candidates list (from json, db, cache, etc)
 */
function get_candidates() {
    global $g_candidates;
    if (!isset($g_candidates)) {
        $res = json_decode(file_get_contents("system/candidates.json"), true);
        $g_candidates = $res['candidates'];
    }
    return $g_candidates;
}

/**
 * filter user selected ids using real candidate ids
 */
function filter_candidates($keys) {
    if (!is_array($keys))
        return array();
    $keys_map = array_flip($keys);
    $keys_out = array();
    $candidates = get_candidates();
    foreach ($candidates as $c) {
        $id = (string)$c['id'];
        if (isset($keys_map[$id]))
            $keys_out[] = $id;
    }
    // compare input and filtered
    if (count($keys) !== count($keys_out)) {
        log_debug("bad_keys", serialize($keys));
        return array();
    }
    sort($keys_out, SORT_NUMERIC);
    return $keys_out;
}

/**
 * return html candidates from array of ids
 */
function keys_to_candidates($keys) {
    $candidates = get_candidates();
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
    $candidates = get_candidates();
    $table = '';
    foreach ($candidates as $c) {
        if (empty($c['org']) && !empty($c['ngo_name']))
            $c['org'] = $c['ngo_name'];
        if (empty($c['link']))
            $c['link'] = "../candidates/".$c['id'].".html";
        $table .= '<tr>';
        if ($form) {
            $table .= sprintf(
                '<td><input type="checkbox" '.
                'id="id_%d" name="id[%d]"></td>',
                (int)$c['id'], (int)$c['id']);
        }
        $table .= sprintf(
            '<td class="nowrap">'.
            '<label for="id_%d">%d. %s</label>'.
            '</td>',
            (int)$c['id'], (int)$c['id'], h($c['name']));
        $table .= sprintf(
            '<td>%s</td>',
            h($c['org']));
        $table .= sprintf(
            '<td class="nowrap">'.
            '<a href="%s" target="_blank">сторінка</a>'.
            '</td>',
            h($c['link']));
        $table .= "</tr>\n";
    }
    return $table;
}

# vim: syntax=php ts=4

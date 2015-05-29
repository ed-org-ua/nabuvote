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
function post_arg($name, $pattern=false, $filter=false) {
    if (!isset($_POST[$name]))
        return "";
    $value = trim($_POST[$name]);
    if ($value && $filter)
        $value = call_user_func($filter, $value);
    if ($pattern && !preg_match($pattern, $value))
        return "";
    return $value;
}

/**
 * Simple remove + - () and spaces from mobile number
 */
function mobile_clean($mobile) {
    $mobile = preg_replace('/[\+\-\(\)\s]/', '', $mobile);
    if (strlen($mobile) == 9)
        $mobile = "380".$mobile;
    elseif (strlen($mobile) == 10)
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
    $_ERRORS[] = $msg;
}

/**
 * print global $_ERRORS array in div class=alert
 */

function print_errors() {
    global $_ERRORS;
    if ($_ERRORS) {
        array_walk($_ERRORS, htmlspecialchars);
        print('<div class="alert alert-danger" role="alert">');
        print(implode('<br>', $_ERRORS).'</div>');
    }
}

/**
 * Save message to debug.log
 */
function log_debug($func, $msg="-") {
    global $settings;
    if (!($filename = $settings['debug_log']))
        return;
    if (!($fp = fopen($filename, "at")))
        return;
    $logline = date("Y-m-d H:i:s").substr(microtime(), 1, 4);
    $logline .= " ".$_SERVER['REMOTE_ADDR'];
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
 * Error handler
 */
function debug_log_err_handler($errno, $errstr, $errfile, $errline) {
    log_debug("$errfile:$errline", "errno, $errstr");
}

/**
 * return true if reCAPTCHA verified
 */
function captcha_verify() {
    global $settings;
    if ((int)$settings['captcha_always_true'])
        return true;
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
    session_unset();
    session_start();
    $_SESSION['ip_addr'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['expires'] = time() + $settings['session_lifetime'];
    $_SESSION['total_post_limit'] = $settings['total_post_limit'];
    $_SESSION['check_email_limit'] = $settings['check_email_limit'];
    $_SESSION['check_mobile_limit'] = $settings['check_mobile_limit'];
    $_SESSION['email_value'] = "";
    $_SESSION['mobile_value'] = "";
    log_debug('init_user_session');
}

/**
 * verify basic session restrictions
 */
function check_session() {
    if ($_SESSION['ip_addr'] != $_SERVER['REMOTE_ADDR'])
        return false;
    if ($_SESSION['expires'] < time())
        return false;
    return true;
}

/**
 *
 */
function email_not_used($email) {
    return true;
}

/**
 *
 */
function send_email_code($email, $code) {
    global $settings;
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
    log_debug('send_email_code');
}

/**
 *
 */
function mobile_not_used($mobile) {
    return true;
}

/**
 *
 */
function send_mobile_code($mobile, $code) {
    global $settings;
    if (!preg_match('/^380\d{9}$/', $mobile))
        return;
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
    log_debug("send_mobile_code", $res);
}

/**
 * check and decrease limit by name
 */
function check_and_dec_limit($name) {
    if ((int)$_SESSION[$name] < 1)
        redirect('step1.php');
    $_SESSION[$name] -= 1;
}

/**
 * set some test (captcha, email, mobile) as passed
 */
function set_test_passed($name) {
    $name = $name.'_pass';
    $_SESSION[$name] = 1;
}

/**
 * check test or redirect to start
 */
function require_test_pass($name, $start) {
    $name = $name.'_pass';
    if (!isset($_SESSION[$name]))
        redirect($start);
}

/**
 *
 */
function next_if_test_pass($name, $next) {
    $name = $name.'_pass';
    if (isset($_SESSION[$name]))
        redirect($next);
}

/**
 * return full template filename for use in include
 */
function get_template($name) {
    return "system/templates/{$name}.php";
}

/**
 * return html table with candidates
 */
function candidates_table($form=false) {
    require_once("candidates.php");
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

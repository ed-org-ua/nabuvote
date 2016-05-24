<?php

require("functions.php");
require("settings.php");

set_error_handler("debug_error_handler");
date_default_timezone_set("Europe/Kiev");

$_ERRORS = array();

/**
 * session starts only if cookie present
 */
if (isset($_COOKIE[session_name()])) {
    session_start();
}

/**
 * log all post data
 */
if ($_POST) {
    log_debug_post_data();
}

/**
 * if session present check common restrictions
 */
if (!empty($_SESSION) && !check_session_limits()) {
    session_unset();
    session_destroy();
    $_SESSION = array();
}

/**
 * check total post limit
 */
if (!empty($_SESSION) && $_POST) {
    check_and_dec_limit('total_post_limit');
    check_csrf_token();
}

/**
 * verify HTTP Referer only for POST
 */
if (!empty($_SERVER['HTTP_REFERER']) && $_POST) {
    check_request_referer();
}

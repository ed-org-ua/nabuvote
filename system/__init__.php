<?php

require("functions.php");
require("settings.php");

$_ERRORS = array();

/**
 * session starts only if cookie present
 */
if (isset($_COOKIE[session_name()])) {
    session_start();
}

/**
 * if session present check common restrictions
 */
if (isset($_SESSION) && $_SESSION && !check_session_limits()) {
    session_unset();
    session_destroy();
}

/**
 * check total post limit
 */
if (isset($_SESSION) && $_SESSION && $_POST) {
    check_and_dec_limit('total_post_limit');
}
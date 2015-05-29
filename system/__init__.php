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
 * if session present check base restrictions
 */
if ($_SESSION && !check_session()) {
    session_unset();
    session_destroy();
}

/**
 * check total post limit
 */
if ($_SESSION && $_POST) {
    check_and_dec_limit('total_post_limit');
}
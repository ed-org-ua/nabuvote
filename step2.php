<?php

require("system/__init__.php");

require_test_pass('captcha', 'step1.php');
next_if_test_pass('email',   'step3.php');

/**
 * Set defaults
 */
$email_value = "";
$email_readonly = "";
$ecode_value = "";

/**
 * Handle form data
 */
if ($_POST) {
    check_and_dec_limit('check_email_limit');

    $email_value = post_arg('email_input', '/^[\w\d_\-\+\.]+@[\w\d\-\.]+\.\w+$/', 'strtolower');
    $ecode_value = post_arg('ecode_input', 'intval');

    if ($ecode_value && $_SESSION['email_value'])
        $email_value = $_SESSION['email_value'];

    /**
     * if email already sent
     */
    if ($_SESSION['email_value'] && $_SESSION['ecode_value']) {
        if ($ecode_value && $ecode_value == $_SESSION['ecode_value']) {
            set_test_passed('email');
            redirect('step3.php');
        } else {
            append_error("Код невірний");
            $ecode_value = "";
        }
    } else {
        if ($email_value && email_not_used($email_value)) {
            $secret_code = rand(100000, 999999);
            $_SESSION['email_value'] = $email_value;
            $_SESSION['ecode_value'] = $secret_code;
            send_email_code($email_value, $secret_code);
            $ecode_value = "";
        } else {
            append_error("Цю адресу неможливо використати.");
            $email_value = "";
        }
    }
} else {
    /**
     * get code from query string if present
     */
    if (isset($_SESSION['email_value'])) {
        $email_value = $_SESSION['email_value'];
    }
    if (isset($_GET['code'])) {
        $ecode_value = $_GET['code'];
    }
}

if ($email_value)
    $email_readonly = 'readonly="readonly"';

require(get_template('step2'));
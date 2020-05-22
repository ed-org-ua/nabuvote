<?php

require("system/__init__.php");

require_test_pass('captcha', 'step1.php');
next_if_test_pass('email',   'step3.php');


/**
 * Set defaults
 */
$email_value = "";
$email_readonly = "";
$email_code = "";

/**
 * Handle form data
 *
 * There are two steps on same form
 * 1. Entering e-mail address
 * 2. Entering verification code
 */
if ($_POST) {
    check_and_dec_limit('check_email_limit');

    $email_value = post_arg('email_input', 'strtolower', '/^[\w\d_\-\.]+@[\w\d\-\.]+\.[a-z]+$/');
    $email_code = post_arg('email_code_input', 'clean_intval', '/^\d+$/');

    // if we on second step restore email from session
    if ($email_code && $_SESSION['email_value'])
        $email_value = $_SESSION['email_value'];

    /**
     * if email already sent
     */
    if (!empty($_SESSION['email_value']) &&
        !empty($_SESSION['email_code'])) {
        // pass this test if user has entered correct code
        if ($email_code && $email_code == $_SESSION['email_code']) {
            set_test_passed('email');
            redirect('step3.php');
        } else {
            append_error("Код невірний");
            $email_code = "";
        }
    } else {
        // some checks before send code
        if (strlen($email_value) < 6)
            $email_value = "";
        if (strpbrk($email_value, " ,;'\"\t\n") !== false)
            $email_value = "";
        // .ru domain is forbidden
        if (substr($email_value, -3) === '.ru')
            $email_value = "";
        // verify not empty and not used email then send code
        if ($email_value && email_not_used($email_value)) {
            $secret_code = safe_rand(1000, 9999, 2);
            $_SESSION['email_value'] = $email_value;
            $_SESSION['email_code'] = $secret_code;
            send_email_code($email_value, $secret_code);
            $email_code = "";
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
        $email_code = $_GET['code'];
    }
}

if ($email_value)
    $email_readonly = ' readonly="readonly"';

require(get_template('step2'));

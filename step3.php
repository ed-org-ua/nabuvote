<?php

require("system/__init__.php");

require_test_pass('captcha', 'step1.php');
require_test_pass('email',   'step2.php');
next_if_test_pass('mobile',  'step4.php');


/**
 * Set defaults
 */
$mobile_value = "";
$mobile_readonly = "";
$mobile_code = "";

/**
 * Handle form data
 *
 * There are two steps on same form
 * 1. Entering mobile phone number
 * 2. Entering verification code
 */
if ($_POST) {
    check_and_dec_limit('check_mobile_limit');

    $mobile_value = post_arg('mobile_input', 'clean_mobile', '/^[\d]{10,12}$/');
    $mobile_code = post_arg('mobile_code_input', 'intval');

    // if we on second step restore mobile number from session
    if ($mobile_code && $_SESSION['mobile_value'])
        $mobile_value = $_SESSION['mobile_value'];

    /**
     * if sms already sent
     */
    if (!empty($_SESSION['mobile_value']) &&
        !empty($_SESSION['mobile_code'])) {
        // pass this test if user has entered correct code
        if ($mobile_code && $mobile_code == $_SESSION['mobile_code']) {
            set_test_passed('mobile');
            redirect('step4.php');
        } else {
            append_error("Код невірний");
            $mobile_code = "";
        }
    } else {
        // last few simple checks
        if (strlen($mobile_value) != 12)
            $mobile_value = "";
        if (substr($mobile_value, 0, 3) != "380")
            $mobile_value = "";
        if (!check_mobile_operator_code($mobile_value))
            $mobile_value = "";
        // verify not empty and not used mobile number
        if ($mobile_value && mobile_not_used($mobile_value)) {
            $secret_code = safe_rand(100000, 999999);
            $_SESSION['mobile_value'] = $mobile_value;
            $_SESSION['mobile_code'] = $secret_code;
            // accept any +380 mobile w/o sms test
            if (!empty($settings['disable_sms_test'])) {
                set_test_passed('mobile');
                redirect('step4.php');
            }
            send_mobile_code($mobile_value, $secret_code);
            $mobile_code = "";
        } else {
            append_error("Цей номер телефону неможливо використати.");
            $mobile_value = "";
        }
    }
} else {
    /**
     * get mobile number from session if present
     */
    if (isset($_SESSION['mobile_value'])) {
        $mobile_value = $_SESSION['mobile_value'];
    }
}

if ($mobile_value)
    $mobile_readonly = ' readonly="readonly"';

require(get_template('step3'));

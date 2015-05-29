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
$mcode_value = "";

/**
 * Handle form data
 */
if ($_POST) {
    check_and_dec_limit('check_mobile_limit');

    $mobile_value = post_arg('mobile_input', 'mobile_phone', '/^[\d]{10,12}$/');
    $mcode_value = post_arg('mcode_input', 'intval');

    if ($mcode_value && $_SESSION['mobile_value'])
        $mobile_value = $_SESSION['mobile_value'];

    /**
     * if sms already sent
     */
    if ($_SESSION['mobile_value'] && $_SESSION['mcode_value']) {
        if ($mcode_value && $mcode_value == $_SESSION['mcode_value']) {
            set_test_passed('mobile');
            redirect('step4.php');
        } else {
            append_error("Код невірний");
            $mcode_value = "";
        }
    } else {
        if (strlen($mobile_value) != 12)
            $mobile_value = "";
        if (substr($mobile_value, 0, 3) != "380")
            $mobile_value = "";
        if ($mobile_value && mobile_not_used($mobile_value)) {
            $secret_code = rand(100000, 999999);
            $_SESSION['mobile_value'] = $mobile_value;
            $_SESSION['mcode_value'] = $secret_code;
            send_mobile_code($mobile_value, $secret_code);
            $mcode_value = "";
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
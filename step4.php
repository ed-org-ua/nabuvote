<?php

require("system/__init__.php");

require_test_pass('captcha', 'step1.php');
require_test_pass('email',   'step2.php');
require_test_pass('mobile',  'step3.php');
next_if_test_pass('voting',  'step5.php');


if ($_POST) {
    $pids = $_POST['id'];
    $keys = array_keys($pids);
    if (count($keys) < 1) {
        append_error("Ви не обрали жодного кандидата.");
    } elseif (count($keys) > 15) {
        append_error("Ви обрали більше 15 кандидатів.");
    } else {
        save_vote($keys);
        set_test_passed('voting');
        redirect('step5.php');
    }
}

require(get_template('step4'));
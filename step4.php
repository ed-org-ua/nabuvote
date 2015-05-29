<?php

require("system/__init__.php");

require_test_pass('captcha', 'step1.php');
require_test_pass('email',   'step2.php');
require_test_pass('mobile',  'step3.php');
next_if_test_pass('voting',  'step5.php');


if ($_POST) {
    var_dump($_POST);
}

require(get_template('step4'));
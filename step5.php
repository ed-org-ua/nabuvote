<?php

require("system/__init__.php");

require_test_pass('vote',  'step4.php');

clean_passed_tests(array('captcha', 'email', 'mobile'));

require(get_template('step5'));
<?php

error_reporting(E_ALL);
date_default_timezone_set("Europe/Kiev");

$settings = array();

$settings['debug_log'] = '/path/to/protected/debug.log';

$settings['session_lifetime'] = 900;
$settings['total_post_limit'] = 10;
$settings['check_email_limit'] = 5;
$settings['check_mobile_limit'] = 5;

$settings['email_from_header'] = "=?UTF-8?b?0J3QkNCRINCj0LrRgNCw0ZfQvdC4?= <no-reply@nabu.gov.ua>";
$settings['email_subject_header'] = "=?UTF-8?b?0JrQvtC0INC/0LXRgNC10LLRltGA0LrQuA==?=";
$settings['email_base_url'] = "http://ed.org.ua/nabu/step2.php?code=";

$settings['captcha_always_true'] = false;
$settings['recaptcha_key'] = '6Le__________________________________Rtn-';
$settings['recaptcha_secret'] = '6Le_________________________________apRf';

$settings['kyivstar_cpi_url'] = "http://cpa.net.ua:8080/cpa2/receiver";
$settings['kyivstar_cpi_paid'] = "free";
$settings['kyivstar_cpi_username'] = "_username_";
$settings['kyivstar_cpi_password'] = "_password_";

# vim: syntax=php ts=4
<?php

error_reporting(0);
set_error_handler(debug_error_handler);
date_default_timezone_set("Europe/Kiev");

$settings = array();

$settings['debug_log'] = '/path/to/nabu_debug.log'; # MUST BE SECURE!!!
$settings['public_log'] = '/path/to/report.txt'; # MUST BE OPEN!!!

$settings['session_lifetime'] = 900;
$settings['total_post_limit'] = 10;
$settings['check_email_limit'] = 5;
$settings['check_mobile_limit'] = 5;

$settings['email_from_header'] = "=?UTF-8?b?0J3QkNCRINCj0LrRgNCw0ZfQvdC4?= <no-reply@nabu.gov.ua>";
$settings['email_subject_header'] = "=?UTF-8?b?0JrQvtC0INC/0LXRgNC10LLRltGA0LrQuA==?=";

$settings['email_base_url'] = "http://_____(change_me)_____/step2.php?code=";

#$settings['captcha_always_true'] = false;
$settings['recaptcha_key'] = '6Le___________(change_me)_______________Rtn-';
$settings['recaptcha_secret'] = '6Le______________(change_me)___________apRf';

$settings['mysql_host']     = 'localhost';
$settings['mysql_user']     = '______(change_me)______';
$settings['mysql_password'] = '______(change_me)______';
$settings['mysql_database'] = '______(change_me)______';

$settings['kyivstar_cpi_url'] = "http://sdp1.cpa.net.ua:8080/cpa2/receiver";
$settings['kyivstar_cpi_paid'] = "2000"; # tarif
$settings['kyivstar_cpi_username'] = "______(change_me)______";
$settings['kyivstar_cpi_password'] = "______(change_me)______";

# vim: syntax=php ts=4
<?php

error_reporting(0);
set_error_handler(debug_error_handler);
date_default_timezone_set("Europe/Kiev");

$settings = array();

$settings['debug_log'] = '/path/to/secure/nabu_debug.log'; # MUST BE SECURE!!!
$settings['public_report'] = 'public/report.txt';

$settings['session_lifetime'] = 900;
$settings['total_post_limit'] = 10;
$settings['check_email_limit'] = 5;
$settings['check_mobile_limit'] = 5;
$settings['max_selected_limit'] = 15;

$settings['email_from_header'] = "=?UTF-8?b?__________(change_me)__________u.gov.ua>";
$settings['email_subject_header'] = "=?UTF-8?b?0JrQvtC0INC/0LXRgNC10LLRltGA0LrQuA==?=";
$settings['email_base_url'] = "http://__________(change_me)__________/step2.php?code=";

$settings['recaptcha_key']    = '__________(change_me)__________';
$settings['recaptcha_secret'] = '__________(change_me)__________';

$settings['mysql_host']     = 'localhost';
$settings['mysql_user']     = '__________(change_me)__________';
$settings['mysql_password'] = '__________(change_me)__________';
$settings['mysql_database'] = '__________(change_me)__________';

#$settings['disable_sms_test'] = false;
$settings['kyivstar_cpi_url'] = "http://__________(change_me)__________/cpa2/receiver";
$settings['kyivstar_cpi_paid'] = "2000";
$settings['kyivstar_cpi_username'] = "__________(change_me)__________";
$settings['kyivstar_cpi_password'] = "__________(change_me)__________";

# vim: syntax=php ts=4

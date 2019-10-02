<?php

error_reporting(E_ALL);
ini_set('display_errors', 'Off');

$settings = array();

$settings['open_elections_time'] = "2015-05-31 08:00:00";
$settings['close_elections_time'] = "2015-06-06 20:00:00";

$settings['debug_log'] = '/path/to/nabu_debug.log'; # MUST BE SECURE!!!
$settings['public_report'] = 'public/report.txt';

$settings['session_lifetime'] = 900;
$settings['total_post_limit'] = 10;
$settings['check_email_limit'] = 5;
$settings['check_mobile_limit'] = 5;
$settings['max_selected_limit'] = 15;
$settings['votes_per_ip_limit'] = 20;

$settings['mysql_host']     = 'localhost';
$settings['mysql_user']     = '__________(change_me)__________';
$settings['mysql_password'] = '__________(change_me)__________';
$settings['mysql_database'] = '__________(change_me)__________';

# sign each line of public report
#$settings['public_mac_algo'] = 'sha256';
#$settings['public_mac_key'] = '__________(change_me)__________';

# used in show_res.php to protect calls from command line
$settings['show_res_secret'] = '_____used_in_show_res.php_____';

$settings['recaptcha_key'] = '__________(change_me)__________';
$settings['recaptcha_secret'] = '__________(change_me)__________';

# sender address can be simple or mime encoded =?UTF-8?b?...?=
$settings['email_feedback_addr'] = 'info@....';
$settings['email_from_header'] = "=?UTF-8?b?0J__________(change_me)__________.gov.ua>";
$settings['email_subject_header'] = "=?UTF-8?b?0JrQvtC0INC/0LXRgNC10LLRltGA0LrQuA==?=";
$settings['email_code_url'] = false; // "http://ed.org.ua/nabu-test/step2.php?code=";

$settings['disable_sms_test'] = true;
#$settings['kyivstar_cpi_url'] = "http://__________(change_me)__________/cpa2/receiver";
#$settings['kyivstar_cpi_paid'] = "2000";
#$settings['kyivstar_cpi_username'] = "__________(change_me)__________";
#$settings['kyivstar_cpi_password'] = "__________(change_me)__________";

# vim: syntax=php ts=4

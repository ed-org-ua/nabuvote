<?php

error_reporting(E_ALL);
ini_set('display_errors', 'Off');

$settings = array();

$settings['open_elections_time'] = "2015-05-31 08:00:00";
$settings['close_elections_time'] = "2015-06-06 20:00:00";

$settings['debug_log'] = '/path/to/log/nabu_debug.log'; # MUST BE SECURE ~~~ ALL TIME ~~~
$settings['public_report'] = '/path/to/log/public_report.txt'; # MUST BE SECURE TILL END OF VOTING
$settings['hashed_report'] = '/var/www/voting/public/hashed_report.txt'; # SHOULD BE PUBLIC
$settings['results_html'] = '/var/www/voting/pre_results.html'; # WILL BE PUBLIC

$settings['session_lifetime'] = 900;
$settings['total_post_limit'] = 10;
$settings['check_email_limit'] = 5;
$settings['check_mobile_limit'] = 5;
$settings['max_selected_limit'] = 9;
$settings['votes_per_ip_limit'] = 10;

$settings['mysql_host']     = 'localhost';
$settings['mysql_user']     = '__________(change_me)__________';
$settings['mysql_password'] = '__________(change_me)__________';
$settings['mysql_database'] = '__________(change_me)__________';

# used in show_res.php to protect calls from command line
$settings['show_res_secret'] = '___change_me_to_secret_password___';

$settings['recaptcha_key'] = '__________(change_me)__________';
$settings['recaptcha_secret'] = '__________(change_me)__________';

# sender address can be simple or mime encoded =?UTF-8?b?...?=
$settings['email_from_header'] = "ARMA Gromrada <arma.rada___(change_me)___.gov.ua>";
$settings['email_subject_header'] = "Kod perevirky Gromrada";
$settings['email_code_url'] = false;

$settings['disable_sms_test'] = false;
$settings['kyivstar_cpi_new'] = true;
#$settings['kyivstar_cpi_url'] = "https://commercial.cc.kyivstar.ua/sms_service/cpa.phtml";
#$settings['kyivstar_cpi_paid'] = "2000";
#$settings['kyivstar_cpi_username'] = "__________(change_me)__________";
#$settings['kyivstar_cpi_password'] = "__________(change_me)__________";

# vim: syntax=php ts=4

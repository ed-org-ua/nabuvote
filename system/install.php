<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (php_sapi_name() != "cli")
    die("Please use php-cli\n");

if (phpversion() < "5.3")
  die("Required PHP >= 5.3");

$required_modules = array('mysqli' => '0.1');

foreach ($required_modules as $mod => $ver)
  if (phpversion($mod) < $ver)
    die("Required module $mod v$ver not found\n");

$required_functions = array('mysqli_connect', 'mysqli_close',
  'curl_init', 'curl_setopt_array', 'curl_exec', 'curl_close',
  'preg_match', 'preg_replace', 'strpbrk', 'hash_hmac',
  'openssl_random_pseudo_bytes');

foreach ($required_functions as $func)
  if (!function_exists($func))
    die("Required function $func not found\n");

require("settings.php");

set_error_handler(NULL);

$create_table = <<<SQL
CREATE TABLE IF NOT EXISTS `ballot_box` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_addr` varchar(60) NOT NULL,
  `email` varchar(250) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `choice` varchar(250) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`email`),
  UNIQUE KEY (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
SQL;

$db = mysqli_connect(
    $settings['mysql_host'],
    $settings['mysql_user'],
    $settings['mysql_password'],
    $settings['mysql_database'])
or die("Error ".mysqli_error());

$db->query($create_table);

mysqli_close($db);
<?php

if (php_sapi_name() != "cli")
    die("please use php-cli");

require("settings.php");

$create_table = <<<SQL
CREATE TABLE IF NOT EXISTS `ballot_box` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_addr` char(16) NOT NULL,
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
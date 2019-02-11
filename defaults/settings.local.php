<?php

$databases['default']['default'] = array(
  'database' => 'osd8dev',
  'username' => 'osd8dev',
  'password' => 'drupal',
  'host' => 'mariadb',
  'port' => '3306',
  'driver' => 'mysql',
  'prefix' => '',
  'collation' => 'utf8mb4_general_ci',
);

$settings['hash_salt'] = 'd41d8cd98f00b204e9800998ecf8427e';
$config['system.logging']['error_level'] = 'verbose';
$config['config_split.config_split.config_dev']['status'] = TRUE;

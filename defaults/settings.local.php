<?php

/**
 * @file
 * Default settings.local.php.
 */

$databases['default']['default'] = [
  'database' => 'osd8dev',
  'username' => 'osd8dev',
  'password' => 'drupal',
  'host' => 'mariadb',
  'port' => '3306',
  'driver' => 'mysql',
  'prefix' => '',
  'collation' => 'utf8mb4_general_ci',
];

$settings['hash_salt'] = 'd41d8cd98f00b204e9800998ecf8427e';
$config['system.logging']['error_level'] = 'verbose';
// Disable this if you don't want to use local specific configurations,
// publications, block placements, etc.
$config['config_split.config_split.local']['status'] = TRUE;
// Enable this if you want to enable coding and development tools, for example,
// devel.
$config['config_split.config_split.development']['status'] = FALSE;

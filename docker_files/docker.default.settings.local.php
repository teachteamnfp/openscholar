<?php
// Database configuration.
$databases['default']['default'] = array(
  'driver' => 'mysql',
  'host' => '127.0.0.1',
  'username' => 'root',
  'password' => '',
  'port' => '3306',
  'database' => 'drupal',
  'prefix' => '',
);

// cache_debug
$conf['cache_backends'][] = 'sites/all/modules/cache_debug/cache_debug.inc';
$conf['cache_default_class'] = 'DrupalDebugCache';
$conf['cache_debug_log_set'] = TRUE;
$conf['cache_debug_log_clear'] = TRUE;

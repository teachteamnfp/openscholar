<?php

ini_set('memory_limit', '256M');

$settings['hash_salt'] = 'HASHSALT';

$databases['default']['default'] = array(
  'database' => 'DBNAME',
  'username' => 'DBUSER',
  'password' => 'DBPASSWORD',
  'prefix' => '',
  'host' => 'localhost',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
  'collation' => 'utf8mb4_general_ci',
);

$settings['install_profile'] = 'standard';

$settings['file_private_path'] = '../private-files';

// Trusted host patterns for development.
$settings['trusted_host_patterns'][] = '\.home\.jaybeaton\.com$';

$settings['skip_permissions_hardening'] = TRUE;

$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;

$config['system.logging']['error_level'] = 'all';
//$config['system.logging']['error_level'] = 'verbose';

// Uncomment to turn off local caching for development.
#$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yml';
#$settings['cache']['bins']['render'] = 'cache.backend.null';
#$settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
#$config['system.performance']['css']['preprocess'] = FALSE;
#$config['system.performance']['js']['preprocess'] = FALSE;


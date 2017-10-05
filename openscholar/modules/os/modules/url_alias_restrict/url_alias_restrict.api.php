<?php

/**
 * @file
 * Hooks provided by Url alias restrict.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Define reserved paths.
 *
 * Modules may specify a list of paths which are considered reserved and
 * inelegible for use as a URL alias.
 *
 * To change the reserve status of paths defined in another module's
 * hook_reserved_paths(), modules should implement hook_reserved_paths_alter().
 *
 * @return
 *   An associative array. For each item, the key is the path in question, in
 *   a format acceptable to drupal_match_path(). The value for each item should
 *   be TRUE (for paths considered reserved) or FALSE (for non-reserved paths).
 *
 * @see hook_menu()
 * @see drupal_match_path()
 * @see hook_reserved_paths_alter()
 */
function hook_reserved_paths() {
  $paths = array(
    'mymodule' => TRUE,
    'mymodule/*' => TRUE,
  );
  return $paths;
}
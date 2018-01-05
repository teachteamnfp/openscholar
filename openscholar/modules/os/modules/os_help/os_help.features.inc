<?php
/**
 * @file
 * os_help.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function os_help_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "strongarm" && $api == "strongarm") {
    return array("version" => "1");
  }
}

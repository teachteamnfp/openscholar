<?php
/**
 * @file
 * hwpi.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function hwpi_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "strongarm" && $api == "strongarm") {
    return array("version" => "1");
  }
   if ($module == "boxes" && $api == "box") {
    return array("version" => "1");
  }
   if ($module == "context" && $api == "context") {
    return array("version" => "3");
  }
}

<?php
/**
 * @file
 * os_cron_settings.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function os_cron_settings_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "elysia_cron" && $api == "default_elysia_cron_rules") {
    return array("version" => "1");
  }
}

<?php

/**
 * @file
 * Page Contexts update task functions.
 */

/**
 * A generic task which attempts to update page contexts.
 */
function vsite_preset_update_page_contexts_task($entity, $task, &$context) {
  $vsite = vsite_get_vsite($entity->nid, TRUE);
  if (!$vsite) {
    return FALSE;
  }
  // Save the current vsite.
  $current_vsite = vsite_get_vsite();
  // Temporary switch from the current vsite to created vsite.
  spaces_set_space($vsite);

  $preset = spaces_preset_load($entity->spaces_preset_og, 'og');
  foreach ($preset->value['context'] as $context => $value) {
    if (strpos($context, 'os_pages-page-') !== FALSE) {
      $alias = str_replace(['os_pages-page-', ':reaction:block'], '', $context);
      $purl = vsite_get_purl();
      $prefix = $purl ? $purl . '/' : '';
      $path = drupal_get_normal_path($prefix . $alias);
      if (strpos($path, 'node/') !== FALSE) {
        $parts = explode('/', $path);
        if (isset($parts[1]) && !empty($parts[1])) {
          $new_context = 'os_pages-page-' . $parts[1] . ':reaction:block';
          $vsite->controllers->context->set($new_context, $value);
        }
      }
    }
  }

  // Switch back the current vsite.
  spaces_set_space($current_vsite);

  return TRUE;
}
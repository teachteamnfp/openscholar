<?php

/**
 * @file
 * Widgets creation task functions.
 */

/**
 * A generic task which attempts to create vsite widgets.
 */
function vsite_preset_create_widgets_task($entity, $task, &$context) {
  $vsite = vsite_get_vsite($entity->nid, TRUE);
  if (!$vsite) {
    return FALSE;
  }
  // Save the current vsite.
  $current_vsite = vsite_get_vsite();
  // Temporary switch from the current vsite to created vsite.
  spaces_set_space($vsite);

  $preset = spaces_preset_load($entity->spaces_preset_og, 'og');
  $created = vsite_preset_create_widgets($preset, $vsite);

  // Switch back the current vsite.
  spaces_set_space($current_vsite);

  return $created;
}

/**
 * Attempts to create widgets for given preset.
 *
 * @param object $preset
 *   Preset object.
 *
 * @param object $vsite
 *   VSite object.
 *
 * @return bool $created
 *   Whether or not the vsite's widgets were created.
 */
function vsite_preset_create_widgets($preset, $vsite) {
  $created = FALSE;
  $widgets = array();

  $path = DRUPAL_ROOT . '/' . drupal_get_path('module', $preset->export_module);
  $path .= '/preset_assets/' . $preset->name . '/widgets/widgets.inc';
  if (is_file($path) && is_readable($path)) {
    require_once $path;
  }
  
  foreach ($widgets as $delta => $options) {
    $options['delta'] = $delta . '-' . $preset->name;
    $plugin = $options['plugin'];
    unset($options['plugin']);
    if (!$box = boxes_box::factory($plugin, $options)) {
      throw new Exception(sprintf('The box %s failed to saved', $options['title']));
    }
    $vsite->controllers->boxes->set($box->delta, $box);
    $created = TRUE;
  }
  return $created;
}

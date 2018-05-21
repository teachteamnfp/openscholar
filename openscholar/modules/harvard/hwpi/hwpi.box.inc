<?php
/**
 * @file
 * hwpi.box.inc
 */

/**
 * Implements hook_default_box().
 */
function hwpi_default_box() {
  $export = array();

  $box = new stdClass();
  $box->disabled = FALSE; /* Edit this to true to make a default box disabled initially */
  $box->api_version = 1;
  $box->delta = 'hwpi_shield';
  $box->plugin_key = 'os_shields_box';
  $box->title = '';
  $box->description = 'Shield';
  $box->options = array();
  $export['hwpi_shield'] = $box;

  return $export;
}

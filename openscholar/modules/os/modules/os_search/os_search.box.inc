<?php
/**
 * @file
 * os_search.box.inc
 */

/**
 * Implements hook_default_box().
 */

function os_search_default_box() {
  $export = array();

  $box = new stdClass();
  $box->disabled = FALSE; /* Edit this to true to make a default box disabled initially */
  $box->api_version = 1;
  $box->delta = 'os_search_box';
  $box->plugin_key = 'os_search_search_box';
  $box->title = '';
  $box->description = 'Search box';
  $box->options = array();
  $export['os_search_box'] = $box;

  return $export;
}

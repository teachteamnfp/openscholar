<?php
/**
 * @file
 * hwpi.strongarm.inc
 */

/**
 * Implements hook_strongarm().
 */
function hwpi_strongarm() {
  $export = array();
  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'university_name';
  $strongarm->value = 'Harvard';
  $export['university_name'] = $strongarm;
  return $export;
}
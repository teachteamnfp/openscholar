<?php
/**
 * @file
 * os_landing_page.strongarm.inc
 */

/**
 * Implements hook_strongarm().
 */
function os_landing_page_strongarm() {
  $export = array();

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'path_to_school_main_website';
  $strongarm->value = 'http://www.google.com';
  $export['path_to_school_main_website'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'path_to_logo';
  $strongarm->value = 'https://www.google.com/images/branding/googlelogo/1x/googlelogo_color_272x92dp.png';
  $export['path_to_logo'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'name_of_school';
  $strongarm->value = 'School Name';
  $export['name_of_school'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'path_to_schools_accessibility_policy';
  $strongarm->value = 'http://www.google.com';
  $export['path_to_schools_accessibility_policy'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'path_to_schools_reporting_copyright_infringements';
  $strongarm->value = 'http://www.google.com';
  $export['path_to_schools_reporting_copyright_infringements'] = $strongarm;

  return $export;
}
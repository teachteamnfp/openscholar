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

/**
 * List of Harvard documentation help link
 */

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'privacy_policy_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/global-site-settings#privacy-policy';
  $export['privacy_policy_url'] = $strongarm;

/**
 * Harvard sitewide name,URL
 */

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'university_base_url';
  $strongarm->value = 'http://www.harvard.edu/';
  $export['university_base_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'site_access';
  $strongarm->value = 'http://accessibility.harvard.edu/';
  $export['site_access'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'copyright_infring';
  $strongarm->value = 'http://www.harvard.edu/reporting-copyright-infringements';
  $export['copyright_infring'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'copyright_text';
  $strongarm->value = 'TTThe President and Fellows of Harvard College';
  $export['copyright_text'] = $strongarm;

/**
 * Hard-coded names.
 */
  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'highest_parent_item';
  $strongarm->value = 'HARVARD.EDU';
  $export['highest_parent_item'] = $strongarm;

  return $export;
}
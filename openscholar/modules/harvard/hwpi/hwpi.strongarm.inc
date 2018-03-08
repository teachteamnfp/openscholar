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
  $strongarm->name = 'training_signup_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/one-page-themes';
  $export['training_signup_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'dataverse_default_url';
  $strongarm->value = 'https://dataverse.harvard.edu/';
  $export['dataverse_default_url'] = $strongarm;

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
  $strongarm->name = 'sitewide_name';
  $strongarm->value = 'Harvard Web Publishing';
  $export['sitewide_name'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'university_base_url';
  $strongarm->value = 'http://www.harvard.edu/';
  $export['university_base_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'university_url';
  $strongarm->value = 'http://hwp.harvard.edu/';
  $export['university_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'contact_url';
  $strongarm->value = 'http://hwp.harvard.edu/contact';
  $export['contact_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'site_url';
  $strongarm->value = 'http://openscholar.harvard.edu';
  $export['site_url'] = $strongarm;

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
  $strongarm->value = 'http://www.harvard.edu/reporting-copyright-iiiinfringements';
  $export['copyright_infring'] = $strongarm;

/**
 * Harvard  upcoming events
 */
  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'upcoming_events';
  $strongarm->value = 'http://hwp.harvard.edu/calendar/upcoming';
  $export['upcoming_events'] = $strongarm;

/**
 * Hard-coded names.
 */
  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'highest_parent_item';
  $strongarm->value = 'HARVARD.EDU';
  $export['highest_parent_item'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'university_name';
  $strongarm->value = 'Harvard';
  $export['university_name'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'linking_account';
  $strongarm->value = 'https://harvard.service-now.com/ithelp?id=kb_article&sys_id=bcb7c5eadbba0fc0a914fff31d9619dc';
  $export['linking_account'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'contact_default_address';
  $strongarm->value = '<p>Harvard University<br> (617) 495-1000<br> Massachusetts Hall<br> Cambridge, MA 02138<br> your_name@harvard.edu</p>';
  $export['contact_default_address'] = $strongarm;

  return $export;
}
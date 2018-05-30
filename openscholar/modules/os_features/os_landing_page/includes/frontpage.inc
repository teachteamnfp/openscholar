<?php

/**
 * Callback function().
 */
function os_landing_page() {
  //Pass all values to theme
  $schoolDetails['path_to_school_main_website'] = variable_get('path_to_school_main_website');
  $schoolDetails['path_to_logo'] = variable_get('path_to_logo');
  $schoolDetails['name_of_school'] = variable_get('name_of_school');
  $schoolDetails['path_to_schools_accessibility_policy'] = variable_get('path_to_schools_accessibility_policy');
  $schoolDetails['path_to_schools_reporting_copyright_infringements'] = variable_get('path_to_schools_reporting_copyright_infringements');
  $schoolDetails['school_primary_color'] = variable_get('school_primary_color');
  
  
  //Call to custom theme
  return theme('os_landing_page_template', array('schoolDetails' => $schoolDetails));
}
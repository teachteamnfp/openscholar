<?php

/**
 * Callback function().
 */
function os_landing_page() {
  //Pass all values to theme
  $schoolDetails['path_to_logo'] = variable_get('path_to_logo');
  $schoolDetails['name_of_school'] = variable_get('name_of_school');
  $schoolDetails['school_primary_color'] = variable_get('school_primary_color');
  
  $university_base_url = variable_get('university_base_url');
  $copyright_infring = variable_get('copyright_infring');
  $site_access = variable_get('site_access');
  $university_name = variable_get('university_name');
  
  //Call to custom theme
  return theme('os_landing_page_template',
    array(
      'schoolDetails' => $schoolDetails, 
      'copyright_infring' => $copyright_infring,
      'site_access' => $site_access,
      'university_base_url' => $university_base_url,
      'university_name' => $university_name,
    )
  );
}
<?php

/**
 * Callback function().
 */
function os_landing_page() {
  //Pass all values to theme
  $path_to_logo         = variable_get('landing_page_path_to_logo');
  $school_primary_color = variable_get('landing_page_school_primary_color');
  $name_of_school       = variable_get('landing_page_name_of_school');
  $university_base_url  = variable_get('landing_page_university_base_url');
  $university_name      = variable_get('landing_page_university_name');
  $copyright_infring    = variable_get('landing_page_copyright_infring');
  $site_access          = variable_get('landing_page_site_access');
  $site_privacy         = variable_get('landing_page_site_privacy');
  
  //Call to custom theme
  return theme('os_landing_page_template',
    array(
      'path_to_logo'         => $path_to_logo,
      'school_primary_color' => $school_primary_color,
      'name_of_school'       => $name_of_school,
      'university_base_url'  => $university_base_url,
      'university_name'      => $university_name,
      'copyright_infring'    => $copyright_infring,
      'site_access'          => $site_access,
      'site_privacy'         => $site_privacy,
    )
  );
}
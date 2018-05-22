<?php

/**
 * Callback function().
 */
function os_landing_page() {
  //Change front page
  //variable_set('site_frontpage', $value);
  //Pass all values to theme
  $schoolDetails['path_to_school_main_website'] = "http://www.google.com";
  $schoolDetails['path_to_logo'] = "https://www.google.com/images/branding/googlelogo/1x/googlelogo_color_272x92dp.png";
  $schoolDetails['name_of_school'] = "Name of School";
  $schoolDetails['path_to_schools_accessibility_policy'] = "http://www.google.com";
  $schoolDetails['path_to_schools_reporting_copyright_infringements'] = "http://www.google.com";
  //Call to custom theme
  return theme('os_landing_page_template', array('schoolDetails' => $schoolDetails));
}
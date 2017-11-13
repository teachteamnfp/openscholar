<?php
/**
 * @file
 * vsite_preset.box.inc
 */

/**
 * Implements hook_default_box().
 */
function vsite_preset_default_box() {
  $export = array();

  $box = new stdClass();
  $box->disabled = FALSE; /* Edit this to true to make a default box disabled initially */
  $box->api_version = 1;
  $box->delta = 'hwp_personal_contact_html';
  $box->plugin_key = 'os_boxes_html';
  $box->title = 'Contact';
  $box->description = 'Contact';
  $box->options = array(
    'text' => array(
      'value' => '<div style="background-color:#efefef;padding:10px;">You have received this email because you have registered for an event.<br><br>Our mailing address is:<br>Harvard University<br>Massachusetts Hall<br>Cambridge, MA 02138<br>your_name@harvard.edu</div>',
      'format' => 'filtered_html',
    ),
    'format' => 1,
    'additional_classes' => '',
  );
  $export['hwp_personal_contact_html'] = $box;

  return $export;
}



<?php
/**
 * @file
 * Convert a field to UTF-8 format.
 */

$plugin = array(
  'form' => 'os_importer_utf_8_form',
  'callback' => 'os_importer_utf_8_convert',
  'name' => t('Convert to UTF-8'),
  'category' => 'Text',
);

/**
 * Tamper form.
 */
function os_importer_utf_8_form($importer, $element_key, $settings) {
  $form = array();
  $form['help'] = array(
    '#markup' => t('Convert the value of the field to UTF-8 format.'),
  );
  return $form;
}

/**
 * Tamper callback.
 */
function os_importer_utf_8_convert($result, $item_key, $element_key, &$field, $settings, $source) {
  if (!isset($source->config['FeedsFileFetcher']['encode'])) {
    // if there's no encoding set, do nothing.
    return;
  }
  if (is_array($field)) {
    foreach ($field as $key => $value) {
      $field[$key] = _os_importer_get_converted_string($source->config['FeedsFileFetcher']['encode'], $value);
    }
  }
  else {
    $field = _os_importer_get_converted_string($source->config['FeedsFileFetcher']['encode'], $field);
  }
}

function _os_importer_get_converted_string($encode, $field) {
  if ($encode == 'auto-detect') {
    foreach(_os_importer_get_encode_strings() as $encode_string) {
      $convertedField = iconv($encode_string, "UTF-8", $field);
      if ($convertedField !== false) {
        return $convertedField;
      }
    }
  }
  return iconv($encode, "UTF-8", $field);
}

function _os_importer_get_encode_strings() {
  $encode_options = _os_importer_get_available_encoding();
  $strings = array();
  foreach ($encode_options as $encode => $encode_option) {
    if (!is_array($encode_option)) {
      $strings[] = $encode;
      continue;
    }
    foreach ($encode_option as $subEncode => $value) {
      $strings[] = $subEncode;
    }
  }
  return $strings;
}

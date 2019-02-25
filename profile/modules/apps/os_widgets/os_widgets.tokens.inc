<?php

/**
 * @file
 * Os widgets tokens.
 */

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\Markup;

/**
 * Implements hook_token_info().
 */
function os_widgets_token_info() {
  $type = [
    'name' => t('Os Widgets'),
    'description' => t('Tokens for widgets.'),
  ];

  $settings['addthis_display_style_description'] = [
    'name' => t("Display style description"),
    'description' => t("Detailed description with images."),
  ];

  return [
    'types' => ['os_widgets' => $type],
    'tokens' => ['os_widgets' => $settings],
  ];
}

/**
 * Implements hook_tokens().
 */
function os_widgets_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {

  $replacements = [];

  if ($type == 'os_widgets') {
    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'addthis_display_style_description':
          $image_base_path = drupal_get_path('module', 'os_widgets') . '/images/addthis';
          $output = Markup::create('<p>Buttons</p><img src="/' . $image_base_path . '/addthis_button.png" />
          <p>Toolbox small</p><img src="/' . $image_base_path . '/addthis_sm.png" />
          <p>Toolbox large</p><img src="/' . $image_base_path . '/addthis_lg.png" />
          <p>Numeric</p><img src="/' . $image_base_path . '/addthis_numbers.gif" />
          <p>Counter</p><img src="/' . $image_base_path . '/addthis_counter.png" />');
          $replacements[$original] = $output;
          break;
      }
    }
  }

  return $replacements;
}

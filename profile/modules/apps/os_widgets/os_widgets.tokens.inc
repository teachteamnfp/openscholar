<?php

/**
 * @file
 * Os widgets tokens.
 */

use Drupal\Core\Render\BubbleableMetadata;

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
          $element = [
            '#theme' => 'os_widgets_addthis_field_description',
            '#image_base_path' => $image_base_path,
          ];
          $output = \Drupal::service('renderer')->render($element);
          $replacements[$original] = $output;
          break;
      }
    }
  }

  return $replacements;
}

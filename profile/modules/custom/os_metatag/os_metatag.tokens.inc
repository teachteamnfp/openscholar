<?php

/**
 * @file
 * OS metatag tokens.
 */

use Drupal\Component\Utility\Html;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function os_metatag_token_info() {
  $type = [
    'name' => t('OS metatag settings'),
    'description' => t('Tokens for OS metatags.'),
  ];

  $settings['site_title'] = [
    'name' => t("Site Title"),
    'description' => t("OS Metatag escaped Site title from settings."),
  ];
  $settings['meta_description'] = [
    'name' => t("Meta Description"),
    'description' => t("OS Metatag escaped Meta Description from settings."),
  ];

  return [
    'types' => ['os_metatag' => $type],
    'tokens' => ['os_metatag' => $settings],
  ];
}

/**
 * Implements hook_tokens().
 */
function os_metatag_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {

  $replacements = [];

  if ($type == 'os_metatag') {
    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'site_title':
          $config = \Drupal::config('os_metatag.setting');
          $bubbleable_metadata->addCacheableDependency($config);
          $site_title = $config->get('site_title');
          // If empty, load original site name.
          if (empty($site_title)) {
            $config_system = \Drupal::config('system.site');
            $site_title = $config_system->get('name');
          }
          $replacements[$original] = Html::escape($site_title);
          break;

        case 'meta_description':
          $config = \Drupal::config('os_metatag.setting');
          $bubbleable_metadata->addCacheableDependency($config);
          $meta_description = $config->get('meta_description');
          $replacements[$original] = !empty($meta_description) ? Html::escape($meta_description) : '';
          break;
      }
    }
  }

  return $replacements;
}

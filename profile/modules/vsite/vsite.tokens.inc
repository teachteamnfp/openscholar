<?php

/**
 * @file
 * Vsite tokens.
 */

use Drupal\Component\Utility\Html;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function vsite_token_info() {
  $type = [
    'name' => t('Vsite'),
    'description' => t('Tokens for active vsite.'),
  ];

  $settings['site_type'] = [
    'name' => t("Site type"),
    'description' => t("Field site type value of active personal group."),
  ];

  return [
    'types' => ['vsite' => $type],
    'tokens' => ['vsite' => $settings],
  ];
}

/**
 * Implements hook_tokens().
 */
function vsite_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {

  $replacements = [];

  if ($type == 'vsite') {
    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'site_type':
          /** @var \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsiteContext */
          $vsiteContext = \Drupal::service('vsite.context_manager');

          $replacements[$original] = '';
          /** @var \Drupal\group\Entity\GroupInterface $group */
          if ($group = $vsiteContext->getActiveVsite()) {
            $group_type = $group->bundle();
            switch ($group_type) {
              case 'personal':
                $replacements[$original] = 'personal';
                break;

              case 'department':
                $replacements[$original] = 'university';
                break;

              case 'project':
                $replacements[$original] = 'company';
                break;
            }
          }
          break;
      }
    }
  }

  return $replacements;
}

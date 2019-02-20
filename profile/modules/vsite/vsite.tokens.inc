<?php

/**
 * @file
 * Hook implementations for the vsite module.
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function vsite_token_info() {
  $tokens = [
    'types' => [
      'vsite' => [
        'name' => t('Vsite'),
        'description' => t('Tokens for vsite-specific information'),
      ],
    ],
    'tokens' => [
      'vsite' => [
        'current-vsite-path' => [
          'name' => t('Current Vsite Path'),
          'description' => t('The path component of the current vsite'),
        ],
      ],
    ],
  ];

  return $tokens;
}

/**
 * Implements hook_tokens().
 */
function vsite_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];
  if ($type == 'vsite') {
    /** @var \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsiteContext */
    $vsiteContext = \Drupal::service('vsite.context_manager');
    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'current-vsite-path':
          if ($vsiteContext->getActiveVsite()) {
            $replacements[$original] = $vsiteContext->getActivePurl();
            $bubbleable_metadata->addCacheContexts(['vsite:' . $vsiteContext->getActiveVsite()->id()]);
            $bubbleable_metadata->addCacheableDependency($vsiteContext->getActiveVsite());
          }
          else {
            $replacements[$original] = '';
            $bubbleable_metadata->addCacheContexts(['vsite:none']);
          }
          break;
      }
    }
  }

  return $replacements;
}

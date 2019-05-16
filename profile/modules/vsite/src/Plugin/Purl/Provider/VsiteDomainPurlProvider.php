<?php

namespace Drupal\vsite\Plugin\Purl\Provider;

use Drupal\group_purl\Plugin\Purl\Provider\GroupPurlProvider;

/**
 * Provider for domain processing.
 *
 * @PurlProvider(
 *   id = "vsite_domain_purl_provider",
 *   title = @Translation("A provider do pair with domain purl processing.")
 * )
 */
class VsiteDomainPurlProvider extends GroupPurlProvider {

  /**
   * Get the custom domains to be used ad modifier data.
   *
   * @inheritDoc
   */
  public function getModifierData() {

    $storage = $this->container->get('entity_type.manager')->getStorage('group');
    $query = \Drupal::entityQuery('group')
      ->exists('field_domain');
    $gids = $query->execute();
    $groups = $storage->loadMultiple($gids);

    $modifiers = [];

    foreach ($groups as $gid => $g) {
      $domain = $g->get('field_domain')->getValue();

      if (empty($domain)) {
        continue;
      }

      $modifiers[$domain[0]['value']] = $gid;
    };

    return $modifiers;
  }

}

<?php

namespace Drupal\os_publications\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;

/**
 * Defines dynamic local tasks.
 */
class CitationDistributeMenuLink extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $links = [];

    foreach (_citation_distribute_plugins() as $plugin) {
      if (isset($plugin['formclass'])) {
        $links[$plugin['id']] = [
          'route_name' => "os_publications.settings_" . $plugin['id'],
          'title' => $plugin['name'],
          'parent' => "os_publications.citation_distribute",
        ] + $base_plugin_definition;
      }
    }

    return $links;
  }

}

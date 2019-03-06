<?php

namespace Drupal\os_publications\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\os_publications\Plugin\CitationDistribution\CitationDistributePluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines dynamic local tasks.
 */
class CitationDistributeMenuLink extends DeriverBase implements ContainerDeriverInterface {

  /**
   * Citation distribute plugin manager.
   *
   * @var \Drupal\os_publications\Plugin\CitationDistribution\CitationDistributePluginManager
   */
  protected $citationDistributePluginManager;

  /**
   * CitationDistributeMenuLink constructor.
   *
   * @param \Drupal\os_publications\Plugin\CitationDistribution\CitationDistributePluginManager $citation_distribute_plugin_manager
   *   Citation distribute plugin manager.
   */
  public function __construct(CitationDistributePluginManager $citation_distribute_plugin_manager) {
    $this->citationDistributePluginManager = $citation_distribute_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('os_publications.manager_citation_distribute')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $links = [];

    foreach ($this->citationDistributePluginManager->getDefinitions() as $plugin) {
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

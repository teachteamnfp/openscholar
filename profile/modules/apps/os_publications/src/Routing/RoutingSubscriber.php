<?php

namespace Drupal\os_publications\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Drupal\os_publications\Plugin\CitationDistribution\CitationDistributePluginManager;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic trousers route events.
 */
class RoutingSubscriber extends RouteSubscriberBase {

  /**
   * Citation distribute plugin manager.
   *
   * @var \Drupal\os_publications\Plugin\CitationDistribution\CitationDistributePluginManager
   */
  protected $citationDistributePluginManager;

  /**
   * RoutingSubscriber constructor.
   *
   * @param \Drupal\os_publications\Plugin\CitationDistribution\CitationDistributePluginManager $citation_distribute_plugin_manager
   *   Citation distribute plugin manager.
   */
  public function __construct(CitationDistributePluginManager $citation_distribute_plugin_manager) {
    $this->citationDistributePluginManager = $citation_distribute_plugin_manager;
  }

  /**
   * Alters existing routes for a specific collection.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   The route collection for adding routes.
   */
  protected function alterRoutes(RouteCollection $collection) {
    foreach ($this->citationDistributePluginManager->getDefinitions() as $plugin) {
      if (isset($plugin['formclass'])) {
        // Route for bulk copying field display settings.
        $route = new Route(
          "/admin/config/openscholar/citation_distribute/" . $plugin['id'],
          [
            '_form' => '\Drupal\os_publications\Form\\' . $plugin["formclass"],
            '_title' => $plugin['name'] . ' configuration',
          ],
          ['_permission' => 'administer site configuration']
        );
        $collection->add("os_publications.settings_" . $plugin['id'], $route);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = parent::getSubscribedEvents();
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', -100];
    return $events;
  }

}

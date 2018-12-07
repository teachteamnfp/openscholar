<?php

namespace Drupal\vsite;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Alter the provider list.
 */
class VsiteServiceProvider extends ServiceProviderBase {

  /**
   * Changes the priority of purl's path processor.
   *
   * These processors need to be run in a certain order for them
   *   to work properly.
   */
  public function alter(ContainerBuilder $container) {
    parent::alter($container);

    if ($container->hasDefinition('purl.outbound_path_processor')) {
      $definition = $container->getDefinition('purl.outbound_path_processor');
      $tags = $definition->getTags();
      $tags['path_processor_outbound'][0]['priority'] = 290;
      $definition->setTags($tags);
    }
  }

}

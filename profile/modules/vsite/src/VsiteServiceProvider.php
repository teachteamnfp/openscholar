<?php

namespace Drupal\vsite;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 *
 */
class VsiteServiceProvider extends ServiceProviderBase {

  /**
   *
   */
  public function alter(ContainerBuilder $container) {
    parent::alter($container);

    $definition = $container->getDefinition('purl.outbound_path_processor');
    $tags = $definition->getTags();
    $tags['path_processor_outbound'][0]['priority'] = 290;
    $definition->setTags($tags);
  }

}

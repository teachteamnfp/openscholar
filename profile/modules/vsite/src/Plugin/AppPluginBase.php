<?php

namespace Drupal\vsite\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\vsite\AppInterface;

/**
 * Base class for app plugins.
 */
abstract class AppPluginBase extends PluginBase implements AppInterface {

  /**
   * {@inheritdoc}
   */
  public function getGroupContentTypes() {
    $definition = $this->getPluginDefinition();
    if (isset($definition['bundle'])) {
      return $definition['bundle'];
    }
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    $definition = $this->getPluginDefinition();
    if (isset($definition['title'])) {
      /** @var \Drupal\Core\StringTranslation\TranslatableMarkup $title */
      $title = $definition['title'];
      return $title->render();
    }
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getCreateLinks() {
    $definition = $this->getPluginDefinition();
    $links = [];

    foreach ($definition['bundle'] as $b) {
      $links[$b] = [
        'menu_name' => 'control-panel',
        'route_name' => 'node.add',
        'route_parameters' => ['node_type' => $b],
        'parent' => 'cp.content.add',
        'title' => $this->getTitle(),
      ];
    }

    return $links;
  }

}

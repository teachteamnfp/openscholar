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

}

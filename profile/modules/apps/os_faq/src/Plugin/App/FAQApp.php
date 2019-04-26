<?php

namespace Drupal\os_faq\Plugin\App;

use Drupal\Component\Plugin\PluginBase;
use Drupal\vsite\AppInterface;

/**
 * FAQ app.
 *
 * @App(
 *   title = @Translation("FAQ"),
 *   canDisable = true,
 *   entityType = "node",
 *   bundle = "faq",
 *   id = "faq"
 * )
 */
class FAQApp extends PluginBase implements AppInterface {

  /**
   * {@inheritdoc}
   */
  public function getGroupContentTypes() {
    return [
      'faq',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->pluginDefinition['title'];
  }

}

<?php

namespace Drupal\os_events;

use Drupal\Core\Entity\EntityInterface;

/**
 * Adds and enables a default signup email template.
 */
interface DefaultMailTemplateInterface {

  /**
   * Creates and enables default mail template when Event node is created.
   *
   * @param \Drupal\Core\Entity\EntityInterface $node
   *   Then Node object.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Exception
   */
  public function createDefaultTemplate(EntityInterface $node);

}

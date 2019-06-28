<?php

namespace Drupal\os_rest\Plugin\rest\resource;

use Drupal\Core\Entity\EntityInterface;

/**
 *
 */
class OsGroupResource extends OsEntityResource {

  /**
   * {@inheritdoc}
   */
  public function get(EntityInterface $entity) {
    return parent::get($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function post(EntityInterface $entity = null) {

    // Values on the entity get destroyed when the entity is saved.
    // We look for them here to preserve them.
    if (!is_null($entity) && isset($entity->_data_extra['theme'])) {
      $theme = $entity->_data_extra['theme'];
    }

    $response = parent::post($entity);

    // Set the theme.
    if (isset($theme)) {
      /** @var \Drupal\vsite\Config\HierarchicalStorageInterface $storage */
      $hierarchicalStorage = \Drupal::service('hierarchical.storage');
      $hierarchicalStorage->clearWriteOverride();
      $storage = $hierarchicalStorage->createCollection('vsite:' . $entity->id());
      $hierarchicalStorage->addStorage($storage, \Drupal\vsite\Config\VsiteStorageDefinition::VSITE_STORAGE);
      $config = \Drupal::configFactory()->getEditable('system.theme');
      $config->set('default', $theme);
      $config->save();
    }

    // Send the batch ID as a header so the client can handle it properly.
    if ($batch = batch_get()) {
      $response->headers->set('X-Drupal-Batch-Id', $batch['id']);
    }

    return $response;
  }

}

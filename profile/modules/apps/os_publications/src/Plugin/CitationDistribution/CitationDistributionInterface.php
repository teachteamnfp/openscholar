<?php

namespace Drupal\os_publications\Plugin\CitationDistribution;

use Drupal\Core\Entity\EntityInterface;
use Drupal\os_publications\GhostEntityInterface;

/**
 * Interface defining a server for citation distribution.
 */
interface CitationDistributionInterface {

  /**
   * Distributes a reference entity to chosen service.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to distribute.
   *
   * @return bool
   *   Status of save/push.
   *
   * @throws \Drupal\os_publications\CitationDistributionException
   */
  public function save(EntityInterface $entity) : bool;

  /**
   * Copies data from bibcite entity data into array labeled for this service.
   *
   * @param int $id
   *   Entity id.
   *
   * @return array
   *   Mapping of metadata keys and values to distribute.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function mapMetadata($id) : array;

  /**
   * Renders the entity into format appropriate for this service.
   *
   * @param int $id
   *   The entity id.
   *
   * @return array
   *   Render array.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function render($id) : array;

  /**
   * Removes a citation.
   *
   * @param \Drupal\os_publications\GhostEntityInterface $entity
   *   The citation entity.
   *   Since the actual entity might not be present at this point, therefore its
   *   ghost entity is going to be used.
   *
   * @throws \Drupal\os_publications\CitationDistributionException
   */
  public function delete(GhostEntityInterface $entity);

}

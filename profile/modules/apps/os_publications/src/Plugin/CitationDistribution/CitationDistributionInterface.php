<?php

namespace Drupal\os_publications\Plugin\CitationDistribution;

/**
 * Interface defining a server for citation distribution.
 */
interface CitationDistributionInterface {

  /**
   * Distributes a reference entity to chosen service.
   *
   * @param int $id
   *   Entity id to distribute.
   * @param array $plugin
   *   CD's definition of this plugin.
   *
   * @return bool
   *   Status of save/push.
   */
  public function save($id, array $plugin);

  /**
   * Copies data from bibcite entity data into array labeled for this service.
   *
   * @param int $id
   *   Entity id.
   *
   * @return array
   *   Mapping of metadata keys and values to distribute.
   */
  public function mapMetadata($id);

  /**
   * Themes data into format appropriate for this service.
   *
   * @param array $metadata
   *   Associative array of metadata keys and values to map.
   */
  public function render(array $metadata);

}

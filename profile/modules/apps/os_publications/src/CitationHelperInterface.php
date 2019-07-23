<?php

namespace Drupal\os_publications;

/**
 * Interface CitationHelperInterface.
 *
 * @package Drupal\os_publications
 */
interface CitationHelperInterface {

  /**
   * Returns citation download button for both publication and listing page.
   *
   * @param string $entity_id
   *   The entity in context.
   *
   * @return array|null
   *   The array to be rendered.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function getCitationDownloadButton($entity_id = NULL) : ?array;

}

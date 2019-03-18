<?php

namespace Drupal\os_publications\Plugin\CitationDistribution;

/**
 * Citation Distribute GoogleScholar service.
 *
 * @CitationDistribute(
 *   id = "citation_distribute_repec",
 *   title = @Translation("RePEc citation distribute service."),
 *   name = "RePEc",
 *   href = "https://repec.org",
 *   description = "Searchable index of citations in RePEc",
 * )
 */
class CitationDistributeRepec implements CitationDistributionInterface {

  /**
   * {@inheritdoc}
   */
  public function render($id): array {
    // Repec does not renders anything.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function mapMetadata($id): array {
    // The mapping is handled by the repec module.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function save($id, array $plugin): bool {
    // TODO: Not yet implemented.
    return TRUE;
  }

}

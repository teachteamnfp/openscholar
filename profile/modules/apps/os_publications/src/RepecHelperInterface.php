<?php

namespace Drupal\os_publications;

/**
 * Contract for RepecHelper.
 */
interface RepecHelperInterface {

  /**
   * Gets the contributors attache to a reference.
   *
   * @return \Drupal\bibcite_entity\Entity\ContributorInterface[]
   *   The contributor entities..
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getContributor() : array;

  /**
   * Gets the keywords attached to a reference.
   *
   * @return \Drupal\bibcite_entity\Entity\KeywordInterface[]|null
   *   The keyword entities if present. Otherwise NULL.
   */
  public function getKeywords() : ?array;

}

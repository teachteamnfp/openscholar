<?php

namespace Drupal\os_publications;

use Drupal\bibcite_entity\Entity\ContributorInterface;

/**
 * Contract for RepecHelper.
 */
interface RepecHelperInterface {

  /**
   * Gets the contributor of a reference.
   *
   * Only returns the first author, and ignores the rest.
   *
   * @return \Drupal\bibcite_entity\Entity\ContributorInterface|null
   *   The contributor entity if present. Otherwise NULL.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getContributor() : ?ContributorInterface;

  /**
   * Gets the keywords attached to a reference.
   *
   * @return \Drupal\bibcite_entity\Entity\KeywordInterface[]|null
   *   The keyword entities if present. Otherwise NULL.
   */
  public function getKeywords() : ?array;

}

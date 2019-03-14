<?php

namespace Drupal\Tests\os_publications\ExistingSite;

use Drupal\bibcite_entity\Entity\Contributor;
use Drupal\bibcite_entity\Entity\ContributorInterface;
use Drupal\bibcite_entity\Entity\Keyword;
use Drupal\bibcite_entity\Entity\KeywordInterface;
use Drupal\bibcite_entity\Entity\Reference;
use Drupal\bibcite_entity\Entity\ReferenceInterface;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * TestBase for bibcite customizations.
 */
abstract class TestBase extends ExistingSiteBase {

  /**
   * Creates a reference.
   *
   * @param array $values
   *   (Optional) Default values for the reference.
   *
   * @return \Drupal\bibcite_entity\Entity\ReferenceInterface
   *   The new reference entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createReference(array $values = []) : ReferenceInterface {
    $reference = Reference::create($values + [
      'title' => $this->randomString(),
      'type' => 'artwork',
      'bibcite_year' => [
        'value' => 1980,
      ],
    ]);

    $reference->save();

    $this->markEntityForCleanup($reference);

    return $reference;
  }

  /**
   * Creates a contributor.
   *
   * @param array $values
   *   (Optional) Default values for the contributor.
   *
   * @return \Drupal\bibcite_entity\Entity\ContributorInterface
   *   The new contributor entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createContributor(array $values = []) : ContributorInterface {
    $contributor = Contributor::create($values + [
      'first_name' => $this->randomString(),
      'middle_name' => $this->randomString(),
      'last_name' => $this->randomString(),
    ]);

    $contributor->save();

    $this->markEntityForCleanup($contributor);

    return $contributor;
  }

  /**
   * Creates a keyword.
   *
   * @param array $values
   *   (Optional) Default values for the keyword.
   *
   * @return \Drupal\bibcite_entity\Entity\KeywordInterface
   *   The new keyword entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createKeyword(array $values = []) : KeywordInterface {
    $keyword = Keyword::create($values + [
      'name' => $this->randomString(),
    ]);
    $keyword->save();
    $this->markEntityForCleanup($keyword);
    return $keyword;
  }

}

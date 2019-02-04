<?php

namespace Drupal\Tests\os_publications\ExistingSite;

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

}

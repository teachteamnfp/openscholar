<?php

namespace Drupal\Tests\openscholar\ExistingSite;

use Drupal\bibcite_entity\Entity\Reference;
use Drupal\bibcite_entity\Entity\ReferenceInterface;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Test base for running profile tests.
 */
class TestBase extends ExistingSiteBase {

  /**
   * Create a new reference.
   *
   * @param array $values
   *   Values used to construct the new reference.
   *
   * @return \Drupal\bibcite_entity\Entity\ReferenceInterface
   *   New reference entity.
   */
  public function createReference(array $values = []) : ReferenceInterface {
    /** @var \Drupal\Core\Session\AccountProxyInterface $current_user */
    $current_user = $this->container->get('current_user');

    /** @var \Drupal\bibcite_entity\Entity\ReferenceInterface $reference */
    $reference = Reference::create($values + [
      'title' => $this->randomMachineName(8),
      'type' => 'artwork',
      'uid' => $current_user->id(),
    ]);

    $this->markEntityForCleanup($reference);

    return $reference;
  }

}

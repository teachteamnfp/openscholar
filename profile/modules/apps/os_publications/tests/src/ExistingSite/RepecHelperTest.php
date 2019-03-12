<?php

namespace Drupal\Tests\os_publications\ExistingSite;

use Drupal\os_publications\RepecHelper;

/**
 * Class RepecHelperTest.
 *
 * @group kernel
 * @coversDefaultClass \Drupal\os_publications\RepecHelper
 */
class RepecHelperTest extends TestBase {

  /**
   * Tests getContributor.
   *
   * @covers ::getContributor
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function testGetContributor() {
    $contributor = $this->createContributor();
    $reference_wo_contributor = $this->createReference();
    $reference_with_contributor = $this->createReference([
      'author' => [
        [
          'target_id' => $contributor->id(),
        ],
      ],
    ]);

    $repec_helper = new RepecHelper($reference_wo_contributor);
    $this->assertNull($repec_helper->getContributor());

    $repec_helper = new RepecHelper($reference_with_contributor);
    $this->assertEquals($contributor->id(), $repec_helper->getContributor()->id());
  }

}

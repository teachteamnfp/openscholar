<?php

namespace Drupal\Tests\os_publications\ExistingSite;

use Drupal\bibcite_entity\Entity\KeywordInterface;
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

  /**
   * Tests getKeywords.
   *
   * @covers ::getKeywords
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testGetKeywords() {
    $keyword1 = $this->createKeyword();
    $keyword2 = $this->createKeyword();

    $reference_wo_keywords = $this->createReference();
    $reference_with_keywords = $this->createReference([
      'keywords' => [
        [
          'target_id' => $keyword1->id(),
        ],
        [
          'target_id' => $keyword2->id(),
        ],
      ],
    ]);

    $repec_helper = new RepecHelper($reference_wo_keywords);
    $this->assertNull($repec_helper->getKeywords());

    $repec_helper = new RepecHelper($reference_with_keywords);
    $this->assertCount(2, $repec_helper->getKeywords());

    $keyword_ids = array_map(function (KeywordInterface $keyword) {
      return $keyword->id();
    }, $repec_helper->getKeywords());
    $this->assertTrue(in_array($keyword1->id(), $keyword_ids));
    $this->assertTrue(in_array($keyword2->id(), $keyword_ids));
  }

}

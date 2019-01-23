<?php

namespace Drupal\Tests\openscholar\ExistingSite;

/**
 * Bibcite customizations test.
 */
class BibciteTest extends TestBase {

  /**
   * Tests whether reference sticky API is working.
   */
  public function testReferenceSticky() {
    /** @var \Drupal\bibcite_entity\Entity\ReferenceInterface $reference */
    $reference = $this->createReference();

    $this->assertFalse($reference->isSticky());

    /** @var \Drupal\bibcite_entity\Entity\ReferenceInterface $reference2 */
    $reference2 = $this->createReference([
      'sticky' => TRUE,
    ]);

    $this->assertTrue($reference2->isSticky());

    $reference->setSticky(TRUE);

    $this->assertTrue($reference->isSticky());
  }

}

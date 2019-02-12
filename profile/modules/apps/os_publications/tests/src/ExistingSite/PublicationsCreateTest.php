<?php

namespace Drupal\Tests\os_publications\ExistingSite;

/**
 * PublicationsCreateTest.
 *
 * @group kernel
 */
class PublicationsCreateTest extends TestBase {

  /**
   * Tests whether custom title is automatically set.
   *
   * @covers ::os_publications_bibcite_reference_presave
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function testSetTitleFirstLetterExclPrep() {
    $reference = $this->createReference([
      'title' => 'The Velvet Underground',
    ]);

    $this->assertEquals('V', $reference->get('field_title_first_char_excl_prep')->first()->getValue()['value']);
  }

}

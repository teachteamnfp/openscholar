<?php

namespace Drupal\Tests\os_publications\ExistingSite;

use Drupal\os_publications\LabelHelper;

/**
 * Tests publication views.
 *
 * @group vsite
 * @group kernel
 */
class PublicationsViewsTest extends TestBase {

  /**
   * Tests type display of publications.
   */
  public function testType() {
    $this->createReference([
      'title' => 'Mona Lisa',
    ]);

    $this->createReference([
      'title' => 'The Rust Programming Language',
      'type' => 'journal',
      'bibcite_year' => [
        'value' => 2010,
      ],
    ]);

    /** @var array $result */
    $result = views_get_view_result('publications', 'page_1');

    $this->assertCount(2, $result);

    $grouped_result = [];

    foreach ($result as $item) {
      $grouped_result[$item->_entity->bundle()][] = $item->_entity->label();
    }

    $this->assertCount(1, $grouped_result['artwork']);
    $this->assertCount(1, $grouped_result['journal']);
  }

  /**
   * Tests title display of publications.
   */
  public function testTitle() {
    $this->createReference([
      'title' => 'The Last Supper',
    ]);

    $this->createReference([
      'title' => 'Girl with a Pearl Earring',
    ]);

    $this->createReference([
      'title' => 'Mona Lisa',
    ]);

    $this->createReference([
      'title' => 'Las Meninas',
    ]);

    /** @var array $result */
    $result = views_get_view_result('publications', 'page_2');

    $this->assertCount(4, $result);

    $grouped_result = [];
    /** @var \Drupal\os_publications\LabelHelperInterface $label_helper */
    $label_helper = new LabelHelper();

    foreach ($result as $item) {
      $grouped_result[$label_helper->convertToPublicationsListingLabel($item->_entity->label())][] = $item->_entity->id();
    }

    $this->assertCount(1, $grouped_result['G']);
    $this->assertCount(1, $grouped_result['M']);
    $this->assertCount(2, $grouped_result['L']);
  }

}

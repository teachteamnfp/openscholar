<?php

namespace Drupal\Tests\os_publications\ExistingSite;

/**
 * Tests publication views.
 *
 * @group vsite
 * @group kernel
 */
class PublicationsViewsTest extends TestBase {

  /**
   * Tests title display of publications.
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

}

<?php

namespace Drupal\Tests\os_publications\ExistingSite;

use Drupal\os_publications\PublicationsListingHelper;

/**
 * Tests publication views.
 *
 * @group vsite
 * @group kernel
 */
class PublicationsViewsTest extends TestBase {

  /**
   * Tests type display.
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
   * Tests title display.
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
    /** @var \Drupal\os_publications\PublicationsListingHelperInterface $publications_listing_helper */
    $publications_listing_helper = new PublicationsListingHelper();

    foreach ($result as $item) {
      $grouped_result[$publications_listing_helper->convertLabel($item->_entity->label())][] = $item->_entity->id();
    }

    $this->assertCount(1, $grouped_result['G']);
    $this->assertCount(1, $grouped_result['M']);
    $this->assertCount(2, $grouped_result['L']);
  }

  /**
   * Tests author display.
   */
  public function testAuthor() {
    $contributor1 = $this->createContributor([
      'first_name' => 'Leonardo',
      'middle_name' => 'Da',
      'last_name' => 'Vinci',
    ]);

    $contributor2 = $this->createContributor([
      'first_name' => 'Joanne',
      'middle_name' => 'Kathleen',
      'last_name' => 'Rowling',
    ]);

    $this->createReference([
      'title' => 'Mona Lisa',
      'author' => [
        'target_id' => $contributor1->id(),
        'category' => 'primary',
        'role' => 'author',
      ],
    ]);

    $this->createReference([
      'title' => 'The Last Supper',
      'author' => [
        'target_id' => $contributor1->id(),
        'category' => 'primary',
        'role' => 'author',
      ],
    ]);

    $this->createReference([
      'title' => 'Harry Potter and the Deathly Hallows',
      'type' => 'book',
      'author' => [
        'target_id' => $contributor2->id(),
        'category' => 'primary',
        'role' => 'author',
      ],
      'bibcite_publisher' => [
        'value' => 'Bloomsbury',
      ],
    ]);

    /** @var array $result */
    $result = views_get_view_result('publications', 'page_3');

    $this->assertCount(3, $result);

    $grouped_result = [];

    /** @var \Drupal\os_publications\PublicationsListingHelperInterface $publications_listing_helper */
    $publications_listing_helper = new PublicationsListingHelper();

    foreach ($result as $item) {
      /** @var \Drupal\bibcite_entity\Entity\ReferenceInterface $bibcite_reference */
      $bibcite_reference = $item->_entity;
      /** @var \Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem $entity_reference_item */
      $entity_reference_item = $bibcite_reference->get('author')->first();
      /** @var \Drupal\Core\Entity\Plugin\DataType\EntityAdapter $entity_adapter */
      $entity_adapter = $entity_reference_item->get('entity')->getTarget();
      /** @var \Drupal\bibcite_entity\Entity\ContributorInterface $bibcite_contributor */
      $bibcite_contributor = $entity_adapter->getValue();

      $grouped_result[$publications_listing_helper->convertAuthorName($bibcite_contributor->getLastName())][] = $bibcite_reference->id();
    }

    $this->assertCount(2, $grouped_result['V']);
    $this->assertCount(1, $grouped_result['R']);
  }

}

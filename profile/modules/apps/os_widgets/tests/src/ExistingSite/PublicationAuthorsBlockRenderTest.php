<?php

namespace Drupal\Tests\os_widgets\ExistingSite;

use Drupal\bibcite_entity\Entity\Contributor;
use Drupal\bibcite_entity\Entity\ContributorInterface;
use Drupal\bibcite_entity\Entity\Reference;
use Drupal\bibcite_entity\Entity\ReferenceInterface;
use Drupal\Core\Cache\Cache;

/**
 * Class PublicationAuthorsWidget.
 *
 * @group kernel
 * @group widgets
 * @covers \Drupal\os_widgets\Plugin\OsWidgets\PublicationAuthorsWidget
 */
class PublicationAuthorsBlockRenderTest extends OsWidgetsExistingSiteTestBase {

  /**
   * The object we're testing.
   *
   * @var \Drupal\os_widgets\Plugin\OsWidgets\PublicationAuthorsWidget
   */
  protected $publicationAuthorsWidget;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    Cache::invalidateTags(['config:views.view.publication_contributors']);
    $this->publicationAuthorsWidget = $this->osWidgets->createInstance('publication_authors_widget');
  }

  /**
   * Test basic listing test without count.
   */
  public function testBuildListingContributorsWithoutCount() {
    $contributor1 = $this->createContributor([
      'first_name' => 'Lorem1',
      'middle_name' => 'Ipsum1',
      'last_name' => 'Dolor1',
    ]);
    $contributor2 = $this->createContributor([
      'first_name' => 'Lorem2',
      'middle_name' => 'Ipsum2',
      'last_name' => 'Dolor2',
    ]);

    $block_content = $this->createBlockContent([
      'type' => 'publication_authors',
      'field_display_count' => [
        FALSE,
      ],
    ]);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);
    $renderer = $this->container->get('renderer');

    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $renderer->renderRoot($render);
    $this->assertContains('<a href="/publications/author/' . $contributor1->id() . '">Lorem1 Ipsum1 Dolor1</a>', $markup->__toString());
    $this->assertContains('<a href="/publications/author/' . $contributor2->id() . '">Lorem2 Ipsum2 Dolor2</a>', $markup->__toString());
    $this->assertNotContains('views-field-display-count', $markup->__toString());
  }

  /**
   * Test basic listing test with count.
   */
  public function testBuildListingContributorsWithCount() {
    $contributor1 = $this->createContributor([
      'first_name' => 'Lorem1',
      'middle_name' => 'Ipsum1',
      'last_name' => 'Dolor1',
    ]);
    $contributor2 = $this->createContributor([
      'first_name' => 'Lorem2',
      'middle_name' => 'Ipsum2',
      'last_name' => 'Dolor2',
    ]);
    $this->createReference([
      'author' => [
        [
          'target_id' => $contributor1->id(),
          'category' => 'primary',
          'role' => 'author',
        ],
        [
          'target_id' => $contributor2->id(),
          'category' => 'primary',
          'role' => 'author',
        ],
      ],
    ]);
    $this->createReference([
      'author' => [
        [
          'target_id' => $contributor2->id(),
          'category' => 'primary',
          'role' => 'author',
        ],
      ],
    ]);

    $block_content = $this->createBlockContent([
      'type' => 'publication_authors',
      'field_display_count' => [
        TRUE,
      ],
    ]);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);
    $renderer = $this->container->get('renderer');

    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $renderer->renderRoot($render);
    $this->assertContains('<div class="views-row"><div class="views-field views-field-nothing"><span class="field-content"><a href="/publications/author/' . $contributor1->id() . '">Lorem1 Ipsum1 Dolor1</a></span></div><div class="views-field views-field-id-1"><span class="field-content views-field-display-count">(1)</span></div></div>', $markup->__toString());
    $this->assertContains('<div class="views-row"><div class="views-field views-field-nothing"><span class="field-content"><a href="/publications/author/' . $contributor2->id() . '">Lorem2 Ipsum2 Dolor2</a></span></div><div class="views-field views-field-id-1"><span class="field-content views-field-display-count">(2)</span></div></div>', $markup->__toString());
  }

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
      'distribution' => [
          [
            'value' => 'citation_distribute_repec',
          ],
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

}

<?php

namespace Drupal\Tests\os_widgets\ExistingSite;

use Drupal\Core\Cache\Cache;
use Drupal\Tests\openscholar\Traits\ExistingSiteTestTrait;

/**
 * Class PublicationYearsWidget.
 *
 * @group kernel
 * @group widgets
 * @covers \Drupal\os_widgets\Plugin\OsWidgets\PublicationYearsWidget
 */
class PublicationYearsBlockRenderTest extends OsWidgetsExistingSiteTestBase {
  use ExistingSiteTestTrait;

  /**
   * The object we're testing.
   *
   * @var \Drupal\os_widgets\Plugin\OsWidgets\PublicationYearsWidget
   */
  protected $publicationYearsWidget;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    Cache::invalidateTags(['config:views.view.publication_years']);
    $this->publicationYearsWidget = $this->osWidgets->createInstance('publication_years_widget');

    $this->group = $this->createGroup([
      'path' => [
        'alias' => '/test-menu',
      ],
    ]);
    $this->groupAdmin = $this->createUser();
    $this->addGroupAdmin($this->groupAdmin, $this->group);
    $this->drupalLogin($this->groupAdmin);
  }

  /**
   * Test basic listing test without count.
   */
  public function testBuildListingYearsWithoutCount() {

    $ref1 = $this->createReference([
      'title' => 'Ref 2001',
      'bibcite_year' => [
        'value' => 2001,
      ],
    ]);
    $ref2 = $this->createReference([
      'title' => 'Ref 2010',
      'bibcite_year' => [
        'value' => 2010,
      ],
    ]);

    $this->group->addContent($ref1, 'group_entity:bibcite_reference');
    $this->group->addContent($ref2, 'group_entity:bibcite_reference');

    $block_content = $this->createBlockContent([
      'type' => 'publication_years',
      'field_display_count' => [
        FALSE,
      ],
    ]);
    $this->group->addContent($block_content, 'group_entity:block_content');
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);
    $renderer = $this->container->get('renderer');

    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $renderer->renderRoot($render);
    $this->assertContains('<a href="/publications/year/2001">2001</a>', $markup->__toString());
    $this->assertContains('<a href="/publications/year/2010">2010</a>', $markup->__toString());
    $this->assertNotContains('views-field-display-count', $markup->__toString());
  }

  /**
   * Test basic listing test with count.
   */
  public function testBuildListingYearsWithCount() {
    $ref1 = $this->createReference([
      'title' => 'Ref 2001',
      'bibcite_year' => [
        'value' => 2001,
      ],
    ]);
    $ref2 = $this->createReference([
      'title' => 'Ref 2 2001',
      'bibcite_year' => [
        'value' => 2001,
      ],
    ]);
    $ref3 = $this->createReference([
      'title' => 'Ref 2010',
      'bibcite_year' => [
        'value' => 2010,
      ],
    ]);

    $this->group->addContent($ref1, 'group_entity:bibcite_reference');
    $this->group->addContent($ref2, 'group_entity:bibcite_reference');
    $this->group->addContent($ref3, 'group_entity:bibcite_reference');

    $block_content = $this->createBlockContent([
      'type' => 'publication_years',
      'field_display_count' => [
        TRUE,
      ],
    ]);
    $this->group->addContent($block_content, 'group_entity:block_content');
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);
    $renderer = $this->container->get('renderer');

    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $renderer->renderRoot($render);
    $this->assertContains('<div class="views-row"><span class="views-field views-field-bibcite-year"><span class="field-content"><a href="/publications/year/2010">2010</a></span></span><span class="views-field views-field-id"><span class="field-content views-field-display-count">(1)</span></span></div>', $markup->__toString());
    $this->assertContains('<div class="views-row"><span class="views-field views-field-bibcite-year"><span class="field-content"><a href="/publications/year/2001">2001</a></span></span><span class="views-field views-field-id"><span class="field-content views-field-display-count">(2)</span></span></div>', $markup->__toString());
  }

}

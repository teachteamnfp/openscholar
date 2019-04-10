<?php

namespace Drupal\Tests\os_widgets\ExistingSite;

use Drupal\Core\Cache\Cache;

/**
 * Class PublicationYearsWidget.
 *
 * @group kernel
 * @group widgets
 * @covers \Drupal\os_widgets\Plugin\OsWidgets\PublicationYearsWidget
 */
class PublicationYearsBlockRenderTest extends OsWidgetsExistingSiteTestBase {

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
  }

  /**
   * Test basic listing test without count.
   */
  public function testBuildListingYearsWithoutCount() {
    $this->createReference([
      'title' => 'Ref 2001',
      'bibcite_year' => [
        'value' => 2001,
      ],
    ]);
    $this->createReference([
      'title' => 'Ref 2010',
      'bibcite_year' => [
        'value' => 2010,
      ],
    ]);

    $block_content = $this->createBlockContent([
      'type' => 'publication_years',
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
    $this->assertContains('<a href="/publications/year/2001">2001</a>', $markup->__toString());
    $this->assertContains('<a href="/publications/year/2010">2010</a>', $markup->__toString());
    $this->assertNotContains('views-field-display-count', $markup->__toString());
  }

  /**
   * Test basic listing test with count.
   */
  public function testBuildListingYearsWithCount() {
    $this->createReference([
      'title' => 'Ref 2001',
      'bibcite_year' => [
        'value' => 2001,
      ],
    ]);
    $this->createReference([
      'title' => 'Ref 2 2001',
      'bibcite_year' => [
        'value' => 2001,
      ],
    ]);
    $this->createReference([
      'title' => 'Ref 2010',
      'bibcite_year' => [
        'value' => 2010,
      ],
    ]);

    $block_content = $this->createBlockContent([
      'type' => 'publication_years',
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
    $this->assertContains('<div class="views-row"><div class="views-field views-field-bibcite-year"><span class="field-content"><a href="/publications/year/2001">2001</a></span></div><div class="views-field views-field-id"><span class="field-content views-field-display-count">(2)</span></div></div>', $markup->__toString());
    $this->assertContains('<div class="views-row"><div class="views-field views-field-bibcite-year"><span class="field-content"><a href="/publications/year/2010">2010</a></span></div><div class="views-field views-field-id"><span class="field-content views-field-display-count">(1)</span></div></div>', $markup->__toString());
  }

}

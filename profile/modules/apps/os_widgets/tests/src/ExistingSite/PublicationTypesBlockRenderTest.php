<?php

namespace Drupal\Tests\os_widgets\ExistingSite;

/**
 * Class PublicationTypesWidget.
 *
 * @group kernel
 * @group widgets
 * @covers \Drupal\os_widgets\Plugin\OsWidgets\PublicationTypesWidget
 */
class PublicationTypesBlockRenderTest extends OsWidgetsExistingSiteTestBase {

  /**
   * The object we're testing.
   *
   * @var \Drupal\os_widgets\Plugin\OsWidgets\PublicationTypesWidget
   */
  protected $publicationTypesWidget;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->publicationTypesWidget = $this->osWidgets->createInstance('publication_types_widget');
  }

  /**
   * Test basic listing test without count.
   */
  public function testBuildListingContributorsWithoutCount() {
    $block_content = $this->createBlockContent([
      'type' => 'publication_types',
      'field_types_whitelist' => [
        'artwork',
        'book',
        'bill',
      ],
      'field_display_count' => [
        FALSE,
      ],
    ]);
    $this->createReference([
      'type' => 'artwork',
    ]);
    $this->createReference([
      'type' => 'book',
    ]);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);
    $renderer = $this->container->get('renderer');

    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $renderer->renderRoot($render);
    $this->assertContains('<a href="/publications?type=artwork">Artwork
                </a>', $markup->__toString());
    $this->assertContains('<a href="/publications?type=book">Book
                </a>', $markup->__toString());
    $this->assertNotContains('?type=bill', $markup->__toString());
  }

  /**
   * Test basic listing test with count.
   */
  public function testBuildListingContributorsWithCount() {
    $ref1 = $this->createReference([
      'title' => 'Lorem Ipsum art 1',
      'type' => 'artwork',
    ]);
    $ref2 = $this->createReference([
      'title' => 'Lorem Ipsum art 2',
      'type' => 'artwork',
    ]);
    $this->group->addContent($ref1, 'group_entity:bibcite_reference');
    $this->group->addContent($ref2, 'group_entity:bibcite_reference');

    $block_content = $this->createBlockContent([
      'type' => 'publication_types',
      'field_types_whitelist' => [
        'artwork',
        'book',
      ],
      'field_display_count' => [
        TRUE,
      ],
    ]);
    $this->vsiteContextManager->activateVsite($this->group);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);
    $renderer = $this->container->get('renderer');

    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $renderer->renderRoot($render);
    $this->assertContains('<a href="/publications?type=artwork">Artwork
                      <span class="count">(2)</span>
                </a>', $markup->__toString());
  }

  /**
   * Test basic listing test with count and vsite content.
   */
  public function testBuildListingVsiteContributorsWithCount() {
    $ref1 = $this->createReference([
      'type' => 'artwork',
    ]);
    $this->group->addContent($ref1, 'group_entity:bibcite_reference');
    // Create reference out of group.
    $this->createReference([
      'type' => 'artwork',
    ]);

    $block_content = $this->createBlockContent([
      'type' => 'publication_types',
      'field_types_whitelist' => [
        'artwork',
      ],
      'field_display_count' => [
        TRUE,
      ],
    ]);
    $this->vsiteContextManager->activateVsite($this->group);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);
    $renderer = $this->container->get('renderer');

    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $renderer->renderRoot($render);
    $this->assertContains('<a href="/publications?type=artwork">Artwork
                      <span class="count">(1)</span>
                </a>', $markup->__toString());
  }

}

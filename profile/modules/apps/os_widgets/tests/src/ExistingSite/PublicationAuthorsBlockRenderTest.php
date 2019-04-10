<?php

namespace Drupal\Tests\os_widgets\ExistingSite;

use Drupal\Core\Cache\Cache;
use Drupal\group\Entity\Group;

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
   * Vsite Context Manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    Cache::invalidateTags(['config:views.view.publication_contributors']);
    $this->publicationAuthorsWidget = $this->osWidgets->createInstance('publication_authors_widget');
    $this->vsiteContextManager = $this->container->get('vsite.context_manager');
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
   * Test caching vsite tags none.
   */
  public function testBlockContentVsiteCacheTagsNone() {

    $block_content = $this->createBlockContent([
      'type' => 'publication_authors',
      'field_display_count' => [
        TRUE,
      ],
    ]);

    $tag = $block_content->getVsiteCacheTag();
    $this->assertSame('block_content_entity_vsite:none', $tag);
  }

  /**
   * Test caching vsite tags with group.
   */
  public function testBlockContentVsiteCacheTagsWithGroup() {

    $block_content = $this->createBlockContent([
      'type' => 'publication_authors',
      'field_display_count' => [
        TRUE,
      ],
    ]);

    // Set the current user so group creation can rely on it.
    $this->container->get('current_user')->setAccount($this->createUser());
    // Enable the user_as_content plugin on the default group type.
    /** @var \Drupal\group\Entity\Storage\GroupContentTypeStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage('group_content_type');
    /** @var \Drupal\group\Entity\GroupContentTypeInterface[] $plugin */
    $plugins = $storage->loadByContentPluginId('group_entity:block_content');
    /** @var \Drupal\group\Entity\GroupContentTypeInterface $plugin */
    $plugin = reset($plugins);

    $group = Group::create([
      'type' => 'personal',
      'title' => 'Site01',
    ]);
    $group->save();
    $this->markEntityForCleanup($group);
    $group->addContent($block_content, $plugin->getContentPluginId());

    $this->vsiteContextManager->activateVsite($group);
    $tag = $block_content->getVsiteCacheTag();
    $this->assertSame('block_content_entity_vsite:' . $group->id(), $tag);
  }

}

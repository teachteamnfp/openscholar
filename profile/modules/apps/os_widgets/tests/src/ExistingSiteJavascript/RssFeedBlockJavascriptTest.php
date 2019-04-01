<?php

namespace Drupal\Tests\os_widgets\ExistingSiteJavascript;

use Drupal\block\Entity\Block;
use Drupal\block_content\Entity\BlockContent;
use weitzman\DrupalTestTraits\ExistingSiteWebDriverTestBase;

/**
 * Tests os_widgets module.
 *
 * @group widgets
 * @group functional-javascript
 */
class RssFeedBlockJavascriptTest extends ExistingSiteWebDriverTestBase {
  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->entityTypeManager = $this->container->get('entity_type.manager');
  }

  /**
   * Tests os_widgets rss feed block copy link functionality.
   */
  public function testRssFeedCopyFeedUrl() {
    $web_assert = $this->assertSession();

    /** @var \Drupal\block_content\Entity\BlockContent $block_content */
    $block_content = $this->createBlockContent([
      'type' => 'rss_feed',
      'field_content_types' => [],
      'field_is_show_all_content' => [
        TRUE,
      ],
    ]);

    $this->placeBlockContentToContentRegion($block_content);

    $this->visit("/node");
    $web_assert->statusCodeEquals(200);

    $page = $this->getCurrentPage();
    $check_html_value = $page->hasContent('RSS feed link!');
    $this->assertTrue($check_html_value, 'RSS link is not visible.');

    $this->clickLink('RSS feed link!');
    $result = $web_assert->waitForElementVisible('named', ['link', 'Feed URL copied to clipboard']);
    $this->assertNotNull($result, 'Changed link is not visible, link is not copied.');
  }

  /**
   * Creates a block content.
   *
   * @param array $values
   *   (optional) The values used to create the entity.
   *
   * @return \Drupal\block_content\Entity\BlockContent
   *   The created block content entity.
   */
  protected function createBlockContent(array $values = []) {
    $block_content = $this->entityTypeManager->getStorage('block_content')->create($values + [
      'type' => 'basic',
    ]);
    $block_content->enforceIsNew();
    $block_content->save();

    $this->markEntityForCleanup($block_content);

    return $block_content;
  }

  /**
   * Place a block content entity to content region.
   *
   * @param \Drupal\block_content\Entity\BlockContent $block_content
   *   Block content entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function placeBlockContentToContentRegion(BlockContent $block_content): void {
    $values = [
      'id' => 'block_content_rss_feed_test',
      'plugin' => 'block_content:' . $block_content->uuid(),
      'region' => 'content',
      'settings' => [
        'id' => 'block_content:' . $block_content->uuid(),
        'label' => 'Test block for rss feed link',
        'provider' => 'block_content',
        'label_display' => 'visible',
        'status' => TRUE,
        'info' => '',
        'view_mode' => 'full',
      ],
      'theme' => 'os_base',
      'visibility' => [],
      'weight' => 0,
    ];
    $block = Block::create($values);
    $block->save();
    $this->markEntityForCleanup($block);
  }

}

<?php

namespace Drupal\Tests\os_widgets\ExistingSiteJavascript;

use Drupal\block\Entity\Block;
use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Config\FileStorage;
use Drupal\os_widgets\Entity\LayoutContext;
use weitzman\DrupalTestTraits\ExistingSiteWebDriverTestBase;

/**
 * Tests os_widgets module.
 *
 * @group functional-javascript
 * @group widgets
 */
class RssFeedBlockJavascriptTest extends ExistingSiteWebDriverTestBase {
  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The theme handler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * The default theme.
   *
   * @var string
   */
  protected $defaultTheme;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->configFactory = $this->container->get('config.factory');
    $this->themeHandler = $this->container->get('theme_handler');
    $this->defaultTheme = $this->themeHandler->getDefault();
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
    $this->assertNotNull($page->find('css', '.view-empty'), 'Missing .view-empty element');

    $this->assertNotNull($page->find('css', '#block-block-content-rss-feed-test'), 'Block is missing from the page');
    $check_html_value = $page->hasContent('Subscribe');
    $this->assertTrue($check_html_value, 'Subscribe is not visible.');
    $this->clickLink('Subscribe');
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
    // This test relies on a test block that is only enabled for os_base.
    /** @var \Drupal\Core\Config\Config $theme_setting */
    $theme_setting = $this->configFactory->getEditable('system.theme');
    $theme_setting->set('default', 'os_base');
    $theme_setting->save();

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
    $layoutContext = LayoutContext::load('all_pages');
    $blocks = $layoutContext->getBlockPlacements();
    $blocks[$block->id()] = [
      'id' => $block->id(),
      'region' => 'content',
      'weight' => 0,
    ];
    $layoutContext->setBlockPlacements($blocks);
    $layoutContext->save();
    $this->markEntityForCleanup($block);
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    parent::tearDown();

    /** @var \Drupal\Core\Config\Config $theme_setting */
    $theme_setting = $this->configFactory->getEditable('system.theme');
    $theme_setting->set('default', $this->defaultTheme);
    $theme_setting->save();

    $config_path = drupal_get_path('profile', 'openscholar') . '/config/sync';
    $source = new FileStorage($config_path);
    $config_storage = \Drupal::service('config.storage');
    $config_storage->write('os_widgets.layout_context.all_pages', $source->read('os_widgets.layout_context.all_pages'));
  }

}

<?php

namespace Drupal\Tests\os_widgets\Unit;

use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Field\FieldItemList;
use Drupal\os_widgets\Plugin\OsWidgets\AddThisWidget;
use Drupal\Tests\UnitTestCase;

/**
 * Class AddThisMediaWidget.
 *
 * @group unit
 * @covers \Drupal\os_widgets\Plugin\OsWidgets\AddThisWidget
 */
class AddThisBlockRenderTest extends UnitTestCase {

  /**
   * The object we're testing.
   *
   * @var \Drupal\os_widgets\Plugin\OsWidgets\AddThisWidget
   */
  protected $addThisMediaWidget;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->addThisMediaWidget = $this->getMockBuilder(AddThisWidget::class)
      ->disableOriginalConstructor()
      ->setMethods(['getModulePath'])
      ->getMock();
    $this->addThisMediaWidget->method('getModulePath')
      ->willReturn('modules/os_widgets/test');
  }

  /**
   * Test build function display style buttons.
   */
  public function testBuildDisplayButtons() {
    $field_values = [
      'field_addthis_display_type' => [
        [
          'value' => 'buttons',
        ],
      ],
    ];
    $block_content = $this->createBlockContentMock($field_values);
    $variables = $this->addThisMediaWidget->buildBlock([], $block_content);
    $this->assertSame('os_widgets/addthis', $variables['#attached']['library'][0]);
    $this->assertContains('images/addthis/addthis_smallbar.png', $variables['content']['addthis']['#markup']);
  }

  /**
   * Test build function display style toolbox_small.
   */
  public function testBuildDisplayToolboxSmall() {
    $field_values = [
      'field_addthis_display_type' => [
        [
          'value' => 'toolbox_small',
        ],
      ],
    ];
    $block_content = $this->createBlockContentMock($field_values);
    $variables = $this->addThisMediaWidget->buildBlock([], $block_content);
    $this->assertSame('os_widgets/addthis', $variables['#attached']['library'][0]);
    $this->assertContains('<div class="addthis_toolbox addthis_default_style">', $variables['content']['addthis']['#markup']);
  }

  /**
   * Test build function display style toolbox_large.
   */
  public function testBuildDisplayToolboxLarge() {
    $field_values = [
      'field_addthis_display_type' => [
        [
          'value' => 'toolbox_large',
        ],
      ],
    ];
    $block_content = $this->createBlockContentMock($field_values);
    $variables = $this->addThisMediaWidget->buildBlock([], $block_content);
    $this->assertSame('os_widgets/addthis', $variables['#attached']['library'][0]);
    $this->assertContains('<div class="addthis_toolbox addthis_default_style addthis_32x32_style">', $variables['content']['addthis']['#markup']);
  }

  /**
   * Test build function display style numeric.
   */
  public function testBuildDisplayNumeric() {
    $field_values = [
      'field_addthis_display_type' => [
        [
          'value' => 'numeric',
        ],
      ],
    ];
    $block_content = $this->createBlockContentMock($field_values);
    $variables = $this->addThisMediaWidget->buildBlock([], $block_content);
    $this->assertSame('os_widgets/addthis', $variables['#attached']['library'][0]);
    $this->assertContains('<a class="addthis_button_facebook_like" fb:like:layout="button_count"></a>', $variables['content']['addthis']['#markup'], 'Facebook like not found');
    $this->assertContains('<a class="addthis_button_tweet"></a>', $variables['content']['addthis']['#markup'], 'Tweet button not found');
    $this->assertContains('<a class="addthis_button_google_plusone" g:plusone:size="medium"></a>', $variables['content']['addthis']['#markup'], 'Google plus one button not found');
    $this->assertContains('<a class="addthis_counter addthis_pill_style"></a></div>', $variables['content']['addthis']['#markup'], 'Pill style not found');
  }

  /**
   * Test build function display style counter.
   */
  public function testBuildDisplayCounter() {
    $field_values = [
      'field_addthis_display_type' => [
        [
          'value' => 'counter',
        ],
      ],
    ];
    $block_content = $this->createBlockContentMock($field_values);
    $variables = $this->addThisMediaWidget->buildBlock([], $block_content);
    $this->assertSame('os_widgets/addthis', $variables['#attached']['library'][0]);
    $this->assertContains('<a class="addthis_counter"></a>', $variables['content']['addthis']['#markup'], 'Counter not found');
  }

  /**
   * Create a block content mock for testing.
   */
  protected function createBlockContentMock(array $field_values) {
    $block_content = $this->createMock(BlockContent::class);

    // field_addthis_display_style Mock.
    $field_addthis_display_style = $this->createMock(FieldItemList::class);
    $field_addthis_display_style->method('getValue')
      ->willReturn($field_values['field_addthis_display_type']);

    $block_content->expects($this->at(0))
      ->method('get')
      ->willReturn($field_addthis_display_style);

    return $block_content;
  }

}

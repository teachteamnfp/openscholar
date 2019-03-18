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
    $build = [];
    $this->addThisMediaWidget->buildBlock($build, $block_content);
    $this->assertSame('os_widgets/addthis', $build['addthis']['#attached']['library'][0]);
    $this->assertEquals('os_widgets_addthis_buttons', $build['addthis']['#theme']);
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
    $build = [];
    $this->addThisMediaWidget->buildBlock($build, $block_content);
    $this->assertEquals('os_widgets_addthis_toolbox_small', $build['addthis']['#theme']);
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
    $build = [];
    $this->addThisMediaWidget->buildBlock($build, $block_content);
    $this->assertEquals('os_widgets_addthis_toolbox_large', $build['addthis']['#theme']);
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
    $build = [];
    $this->addThisMediaWidget->buildBlock($build, $block_content);
    $this->assertEquals('os_widgets_addthis_numeric', $build['addthis']['#theme']);
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
    $build = [];
    $this->addThisMediaWidget->buildBlock($build, $block_content);
    $this->assertEquals('os_widgets_addthis_counter', $build['addthis']['#theme']);
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

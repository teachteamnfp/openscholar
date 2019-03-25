<?php

namespace Drupal\Tests\os_widgets\ExistingSite;

/**
 * Class AddThisBlockRenderTest.
 *
 * @group kernel
 * @covers \Drupal\os_widgets\Plugin\OsWidgets\AddThisWidget
 */
class AddThisBlockRenderTest extends OsWidgetsExistingSiteTestBase {

  /**
   * Test build function display style buttons.
   */
  public function testBuildDisplayButtons() {
    /** @var \Drupal\block_content\Entity\BlockContent $block_content */
    $block_content = $this->createBlockContent([
      'type' => 'addthis',
      'field_addthis_display_style' => [
        'buttons',
      ],
    ]);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);

    $this->assertSame('os_widgets/addthis', $render['addthis']['#attached']['library'][0]);
    $this->assertEquals('os_widgets_addthis_buttons', $render['addthis']['#theme']);
  }

  /**
   * Test build function display style toolbox_small.
   */
  public function testBuildDisplayToolboxSmall() {
    /** @var \Drupal\block_content\Entity\BlockContent $block_content */
    $block_content = $this->createBlockContent([
      'type' => 'addthis',
      'field_addthis_display_style' => [
        'toolbox_small',
      ],
    ]);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);

    $this->assertEquals('os_widgets_addthis_toolbox_small', $render['addthis']['#theme']);
  }

  /**
   * Test build function display style toolbox_large.
   */
  public function testBuildDisplayToolboxLarge() {
    /** @var \Drupal\block_content\Entity\BlockContent $block_content */
    $block_content = $this->createBlockContent([
      'type' => 'addthis',
      'field_addthis_display_style' => [
        'toolbox_large',
      ],
    ]);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);

    $this->assertEquals('os_widgets_addthis_toolbox_large', $render['addthis']['#theme']);
  }

  /**
   * Test build function display style numeric.
   */
  public function testBuildDisplayNumeric() {
    /** @var \Drupal\block_content\Entity\BlockContent $block_content */
    $block_content = $this->createBlockContent([
      'type' => 'addthis',
      'field_addthis_display_style' => [
        'numeric',
      ],
    ]);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);

    $this->assertEquals('os_widgets_addthis_numeric', $render['addthis']['#theme']);
  }

  /**
   * Test build function display style counter.
   */
  public function testBuildDisplayCounter() {
    /** @var \Drupal\block_content\Entity\BlockContent $block_content */
    $block_content = $this->createBlockContent([
      'type' => 'addthis',
      'field_addthis_display_style' => [
        'counter',
      ],
    ]);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);

    $this->assertEquals('os_widgets_addthis_counter', $render['addthis']['#theme']);
  }

}

<?php

namespace Drupal\Tests\os_widgets\Unit;

use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Field\EntityReferenceFieldItemList;
use Drupal\Core\Field\FieldItemList;
use Drupal\media\Entity\Media;
use Drupal\os_widgets\BlockContentType\EmbedMediaWidget;
use Drupal\Tests\UnitTestCase;

/**
 * Class EmbedMediaWidget.
 *
 * @package Drupal\Tests\vsite\Kernel\
 * @group unit
 * @covers \Drupal\os_widgets\BlockContentType\EmbedMediaWidget
 */
class EmbedMediaBlockRenderTest extends UnitTestCase {

  /**
   * The object we're testing.
   *
   * @var \Drupal\os_widgets\BlockContentType\EmbedMediaWidget
   */
  protected $embedMediaWidget;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->embedMediaWidget = new EmbedMediaWidget();
  }

  /**
   * TODO.
   */
  public function testBuildLogic1() {
    $field_values = [
      'field_max_width' => [
        [
          'value' => 333,
        ],
      ],
      'field_media_select' => [
        [
          'alt' => 'Alt test',
        ],
      ],
    ];
    $block_content = $this->createBlockContentMock('image', $field_values['field_max_width'], $field_values['field_media_select']);
    $this->embedMediaWidget->buildBlock([], $block_content);
  }

  /**
   * Create a block content mock for testing.
   */
  protected function createBlockContentMock(string $mediaBundle, array $field_max_width_values, array $field_media_select_values) {
    $block_content = $this->createMock(BlockContent::class);

    // field_max_width Mock.
    $field_max_width = $this->createMock(FieldItemList::class);
    $field_max_width->method('getValue')
      ->willReturn($field_max_width_values);

    // field_media_select Mock.
    $field_media_select = $this->createMock(EntityReferenceFieldItemList::class);
    $field_media_select->method('getValue')
      ->willReturn($field_media_select_values);
    $media = $this->createMock(Media::class);
    $media->method('bundle')
      ->willReturn($mediaBundle);
    $field_media_select->method('referencedEntities')
      ->willReturn([$media]);

    $block_content->method('get')
      ->with('field_max_width')
      ->willReturn($field_max_width);
    $block_content->method('get')
      ->with('field_media_select')
      ->willReturn($field_media_select);

    return $block_content;
  }

}

<?php

namespace Drupal\Tests\os_wysiwyg\ExistingSite;

use Drupal\editor\Entity\Editor;
use Drupal\file\Entity\File;
use Drupal\filter\Entity\FilterFormat;
use Drupal\media\Entity\Media;
use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;
use Drupal\Tests\openscholar\Traits\ExistingSiteTestTrait;

/**
 * Class OsWysiwygLinkFilterTest.
 *
 * @package Drupal\Tests\os_wysiwyg\ExistingSite
 * @group kernel
 * @group wysiwyg
 */
class OsWysiwygLinkFilterTest extends OsExistingSiteTestBase {

  use ExistingSiteTestTrait;

  protected $adminUser;

  /**
   * A set up for all tests.
   */
  public function setUp() {
    parent::setUp();

    // Create a text format and enable the os_link_filter filter.
    $format = FilterFormat::create([
      'format' => 'custom_format',
      'name' => 'Custom format',
      'filters' => [
        'os_link_filter' => [
          'status' => 1,
        ],
      ],
    ]);
    $format->save();
    $this->markEntityForCleanup($format);

    $editor_group = [
      'name' => 'Os Link',
      'items' => [
        'url',
      ],
    ];
    $editor = Editor::create([
      'format' => 'custom_format',
      'editor' => 'ckeditor',
      'settings' => [
        'toolbar' => [
          'rows' => [[$editor_group]],
        ],
      ],
    ]);
    $editor->save();
    $this->markEntityForCleanup($editor);

    // Create a admin user.
    $this->adminUser = $this->createAdminUser();
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Test for simple data url.
   */
  public function testDataUrlProcess() {
    /** @var \Drupal\Core\Entity\EntityTypeManager $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');
    /** @var \Drupal\Core\Render\Renderer $renderer_service */
    $renderer_service = \Drupal::service('renderer');

    $content = '<a data-url="http://example.com" title="Test">Simple url</a>';
    $settings = [];
    $settings['type'] = 'page';
    $settings['title'] = 'Test data url';
    $settings['body'] = [['value' => $content, 'format' => 'custom_format']];
    $node = $this->createNode($settings);
    $build = $entity_type_manager->getViewBuilder('node')->view($node);
    $output = $renderer_service->renderRoot($build);
    $this->assertContains('title="Test" href="http://example.com"', $output->__toString());
  }

  /**
   * Test for simple data mid.
   */
  public function testDataMidProcess() {
    $file = File::create([
      'filename' => 'example.jpg',
      'uri' => 'public://photos/example.jpg',
      'filemime' => 'image/jpeg',
      'status' => 1,
    ]);
    $file->save();
    $this->markEntityForCleanup($file);
    $media_image = Media::create([
      'bundle' => 'image',
      'name' => $this->randomMachineName(8),
      'status' => 1,
      'field_media_image' => [
        'target_id' => $file->id(),
      ],
    ]);
    $media_image->save();
    $this->markEntityForCleanup($media_image);
    $media_document = Media::create([
      'bundle' => 'document',
      'name' => $this->randomMachineName(8),
      'status' => 1,
      'field_media_file' => [
        'target_id' => $file->id(),
      ],
    ]);
    $media_document->save();
    $this->markEntityForCleanup($media_document);

    /** @var \Drupal\Core\Entity\EntityTypeManager $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');
    /** @var \Drupal\Core\Render\Renderer $renderer_service */
    $renderer_service = \Drupal::service('renderer');

    $content = '<a data-mid="' . $media_image->id() . '" title="Test">Simple image mid</a>';
    $settings = [];
    $settings['type'] = 'page';
    $settings['title'] = 'Test image data mid';
    $settings['body'] = [['value' => $content, 'format' => 'custom_format']];
    $node = $this->createNode($settings);
    $build = $entity_type_manager->getViewBuilder('node')->view($node);
    $output = $renderer_service->renderRoot($build);
    $this->assertContains('title="Test" href="http://apache/sites/default/files/photos/example.jpg"', $output->__toString());

    $content = '<a data-mid="' . $media_document->id() . '" title="Test">Simple document mid</a>';
    $settings['title'] = 'Test document data mid';
    $settings['body'] = [['value' => $content, 'format' => 'custom_format']];
    $node = $this->createNode($settings);
    $build = $entity_type_manager->getViewBuilder('node')->view($node);
    $output = $renderer_service->renderRoot($build);
    $this->assertContains('title="Test" href="http://apache/sites/default/files/photos/example.jpg"', $output->__toString());
  }

}

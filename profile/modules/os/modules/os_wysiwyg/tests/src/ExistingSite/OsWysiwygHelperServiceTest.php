<?php

namespace Drupal\Tests\os_wysiwyg\ExistingSite;

use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;
use Drupal\Tests\openscholar\Traits\ExistingSiteTestTrait;

/**
 * Class OsWysiwygHelperServiceTest.
 *
 * @package Drupal\Tests\os_wysiwyg\ExistingSite
 * @group kernel
 * @group wysiwyg
 */
class OsWysiwygHelperServiceTest extends OsExistingSiteTestBase {

  use ExistingSiteTestTrait;

  /**
   * Os Link Helper.
   *
   * @var \Drupal\os_wysiwyg\OsLinkHelperInterface
   */
  protected $osLinkHelper;

  /**
   * A set up for all tests.
   */
  public function setUp() {
    parent::setUp();
    $this->osLinkHelper = $this->container->get('os_wysiwyg.os_link_helper');
  }

  /**
   * Test for media image mid conversion.
   */
  public function testMediaImageMidConvert() {
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

    $url = $this->osLinkHelper->getFileUrlFromMedia($media_image->id());
    $this->assertEquals('http://apache/sites/default/files/photos/example.jpg', $url);
  }

  /**
   * Test for media document mid conversion.
   */
  public function testMediaDocumentMidConvert() {
    $file = File::create([
      'filename' => 'document.doc',
      'uri' => 'public://docs/document.doc',
      'filemime' => 'application/msword',
      'status' => 1,
    ]);
    $file->save();
    $this->markEntityForCleanup($file);
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

    $url = $this->osLinkHelper->getFileUrlFromMedia($media_document->id());
    $this->assertEquals('http://apache/sites/default/files/docs/document.doc', $url);
  }

  /**
   * Test for empty media image mid conversion.
   */
  public function testMediaEmptyImageMidConvert() {
    $url = $this->osLinkHelper->getFileUrlFromMedia(999);
    $this->assertEquals('', $url);
  }

}

<?php

namespace Drupal\Tests\os_rest\ExistingSite;

use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\media\Entity\MediaType;
use Drupal\Tests\media\Functional\Rest\MediaResourceTestBase;
use Drupal\Tests\rest\Functional\CookieResourceTestTrait;

/**
 * Class MediaEntityResourceTest.
 *
 * @package Drupal\Tests\os_rest\ExistingSite
 * @group media_browser
 * @group functional
 */
class MediaEntityResourceTest extends MediaResourceTestBase {

  use CookieResourceTestTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['media', 'os_rest'];

  /**
   * {@inheritdoc}
   */
  protected static $format = 'json';

  /**
   * {@inheritdoc}
   */
  protected static $mimeType = 'application/json';

  /**
   * {@inheritdoc}
   */
  protected static $auth = 'cookie';

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->markTestSkipped("This test is incomplete");
  }

  /**
   * {@inheritdoc}
   */
  public function installDrupal() {
    $this->originalProfile = 'openscholar';
    parent::installDrupal();
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareSettings() {
    parent::prepareSettings();

    $settings['config']['simpletest.settings']['parent_profile'] = (object) [
      'value' => $this->originalProfile,
      'required' => TRUE,
    ];

    $this->writeSettings($settings);
  }

  /**
   * {@inheritdoc}
   */
  protected function createEntity() {
    if (!MediaType::load('image')) {
      // Create a "Image" media type.
      $media_type = MediaType::create([
        'name' => 'Image',
        'id' => 'image',
        'description' => 'Images',
        'source' => 'image',
      ]);
      $media_type->save();
      // Create the source field.
      $source_field = $media_type->getSource()->createSourceField($media_type);
      $source_field->getFieldStorageDefinition()->save();
      $source_field->save();
      $media_type
        ->set('source_configuration', [
          'source_field' => $source_field->getName(),
        ])
        ->save();
    }

    // Create a file to upload.
    $file = File::create([
      'uri' => 'public://llama.jpg',
    ]);
    $file->setPermanent();
    $file->save();

    // Create a "Llama" media item.
    $media = Media::create([
      'bundle' => 'image',
      'field_media_image' => [
        'target_id' => $file->id(),
      ],
    ]);
    $media
      ->setName('Llama')
      ->setPublished()
      ->setCreatedTime(123456789)
      ->setOwnerId(static::$auth ? $this->account->id() : 0)
      ->setRevisionUserId(static::$auth ? $this->account->id() : 0)
      ->save();

    return $media;
  }

  /**
   * {@inheritdoc}
   */
  protected function getExpectedNormalizedEntity() {
    $file = File::load(1);

    $real = [
      'alt' => NULL,
    // ISO whatever. dateTtime+timezoneOffset.
      'changed' => $this->formatExpectedTimestampItemValues($this->entity->getChangedTime())['value'],
      'created' => 123456789,
      'description' => '',
      'fid' => $file->id(),
      'filename' => $file->getFilename(),
      'mid' => 1,
      'name' => 'Llama',
      'thumbnail' => file_create_url($file->getFileUri()),
      'schema' => 'public',
      'size' => $file->getSize(),
      'title' => NULL,
      'type' => 'image',
      'url' => file_create_url($file->getFileUri())
    ];

    return $real;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityResourceUrl() {
    return Url::fromUri('/api/media/' . $this->entity->id());
  }

  /**
   * {@inheritdoc}
   */
  public function testGet() {
    parent::testGet();

    // Add filename checking.
  }

}

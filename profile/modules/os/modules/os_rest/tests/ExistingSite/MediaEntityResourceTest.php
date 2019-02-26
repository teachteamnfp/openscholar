<?php

namespace Drupal\Tests\os_rest\ExistingSite;

use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\media\Entity\MediaType;
use Drupal\Tests\media\Functional\Rest\MediaResourceTestBase;
use Drupal\Tests\rest\Functional\CookieResourceTestTrait;

/**
 * Class MediaEntityResourceTest
 * @package Drupal\Tests\os_rest\ExistingSite
 * @group media_browser
 * @group functional
 */
class MediaEntityResourceTest extends MediaResourceTestBase {

  use CookieResourceTestTrait;

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

  protected function createEntity() {

    // Create a file to upload.
    $file = File::create([
      'uri' => 'public://llama.jpg',
    ]);
    $file->setPermanent();
    $file->save();

    // Create a "Llama" media item.
    $media = Media::create([
      'bundle' => 'image',
      'field_media_file' => [
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

  protected function getExpectedNormalizedEntity() {
    $file = File::load(1);
    $thumbnail = File::load(2);

    $real = [
      'alt' => null,
      'changed' => $this->formatExpectedTimestampItemValues($this->entity->getChangedTime())['value'],  //ISO whatever. dateTtime+timezoneOffset
      'created' => 123456789,
      'description' => '',
      'fid' => $file->id(),
      'filename' => $file->getFilename(),
      'mid' => 1,
      'name' => '',
      'thumbnail' => file_create_url($thumbnail->getFileUri()),
      'schema' => 'public',
      'size' => $file->getSize(),
      'title' => null,
      'type' => 'image',
      'url' => file_create_url($file->getFileUri())
    ];

    return $real;
  }

  public function testGet() {
    if (!MediaType::load('image')) {
      echo 'IMAGE MEDIA TYPE NOT FOUND';
    }
    parent::testGet();

    // add filename checking
  }
}

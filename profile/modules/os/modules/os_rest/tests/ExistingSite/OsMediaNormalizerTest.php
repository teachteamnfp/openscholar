<?php

namespace Drupal\Tests\os_rest\ExistingSite;

use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Tests for the OsMediaNormalizer class.
 *
 * @group media_browser
 * @coversDefaultClass Drupal\os_rest\Normalizer\OsMediaNormalizer
 */
class OsMediaRequestTest extends ExistingSiteBase {

  public function setUp() {
    parent::setUp();

  }

  public function testReadRestfulResponse() {

    $file = File::create([
      'uid' => 1,
      'filename' => 'test.jpg',
      'uri' => 'public://test.jpg',
      'status' => 1
    ]);

    $media = Media::create([
      'type' => 'image',
      'name' => 'test',
      'field_media_image' => $file->id()
    ]);

    $data = [
      'name' => 'test2',
      'changed' => '1519589042',
      'alt' => 'alt test',
      'title' => 'title test',
      'description' => 'A description'
    ];

    $response = $this->drupalGet('media/1?_format=json');
    print_r($response);
    //$this->assertEquals($data['name'], $entity->label());
  }
}
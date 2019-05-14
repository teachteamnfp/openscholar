<?php

namespace Drupal\Tests\os_metatag\Traits;

use Drupal\file\Entity\File;

/**
 * OsMetatagTest helpers.
 */
trait OsMetatagTestTrait {

  /**
   * Creates a file.
   *
   * @param array $values
   *   (optional) The values used to create the entity.
   *
   * @return \Drupal\file\Entity\File
   *   The created file entity.
   */
  protected function createFile(array $values = []) : File {
    $file_name = $this->randomMachineName();
    $entity_type_manager = $this->container->get('entity_type.manager');
    $file = $entity_type_manager->getStorage('file')->create($values + [
      'uid' => 1,
      'filename' => $file_name,
      'uri' => 'public://' . $file_name,
      'filemime' => 'image/jpeg',
      'filesize' => 90000,
      'status' => 1,
    ]);
    $file->enforceIsNew();
    $file->save();

    $this->markEntityForCleanup($file);

    return $file;
  }

}

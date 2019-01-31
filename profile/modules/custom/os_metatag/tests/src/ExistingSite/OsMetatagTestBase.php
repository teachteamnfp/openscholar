<?php

namespace Drupal\Tests\os_metatag\ExistingSite;

use Drupal\file\Entity\File;
use Drupal\group\Entity\GroupInterface;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Test base for event tests.
 */
class OsMetatagTestBase extends ExistingSiteBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->entityTypeManager = $this->container->get('entity_type.manager');
  }

  /**
   * Creates a group.
   *
   * @param array $values
   *   (optional) The values used to create the entity.
   *
   * @return \Drupal\group\Entity\GroupInterface
   *   The created group entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function createGroup(array $values = []) : GroupInterface {
    $group = $this->entityTypeManager->getStorage('group')->create($values + [
      'type' => 'personal',
      'label' => $this->randomMachineName(),
    ]);
    $group->enforceIsNew();
    $group->save();

    $this->markEntityForCleanup($group);

    return $group;
  }

  /**
   * Creates a file.
   *
   * @param array $values
   *   (optional) The values used to create the entity.
   *
   * @return \Drupal\file\Entity\File
   *   The created file entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function createFile(array $values = []) : File {
    $file = $this->entityTypeManager->getStorage('file')->create($values + [
      'uid' => 1,
      'filename' => $this->randomMachineName(),
      'uri' => 'public://' . $this->randomMachineName(),
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

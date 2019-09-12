<?php

namespace Drupal\Tests\openscholar\Traits;

use Drupal\bibcite_entity\Entity\Reference;
use Drupal\bibcite_entity\Entity\ReferenceInterface;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\media\MediaInterface;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\user\UserInterface;
use weitzman\DrupalTestTraits\Entity\UserCreationTrait;

/**
 * Provides a trait for openscholar tests.
 */
trait ExistingSiteTestTrait {

  use UserCreationTrait;

  /**
   * Configurations to clean up.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityInterface[]
   */
  protected $cleanUpConfigs = [];

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
   */
  protected function createGroup(array $values = []): GroupInterface {
    $owner = $this->createUser();
    /** @var \Drupal\Core\Entity\EntityStorageInterface $storage */
    $storage = $this->container->get('entity_type.manager')->getStorage('group');
    /** @var \Drupal\group\Entity\GroupInterface $group */
    $group = $storage->create($values + [
      'type' => 'personal',
      'label' => $this->randomMachineName(),
      'path' => [
        'alias' => "/{$this->randomMachineName()}",
      ],
      'field_privacy_level' => [
        'value' => 'public',
      ],
      'uid' => [
        'target_id' => $owner->id(),
      ],
    ]);
    $group->enforceIsNew();
    $group->save();

    $this->markEntityForCleanup($group);

    return $group;
  }

  /**
   * Creates a private group.
   *
   * @param array $values
   *   (optional) The values used to create the entity.
   *
   * @return \Drupal\group\Entity\GroupInterface
   *   The created group entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createPrivateGroup(array $values = []): GroupInterface {
    return $this->createGroup([
      'field_privacy_level' => [
        'value' => 'private',
      ],
    ] + $values);
  }

  /**
   * Creates a user and tracks it for automatic cleanup.
   *
   * @param array $permissions
   *   Array of permission names to assign to user. Note that the user always
   *   has the default permissions derived from the "authenticated users" role.
   * @param string $name
   *   The user name.
   *
   * @return \Drupal\user\Entity\User|false
   *   A fully loaded user object with pass_raw property, or FALSE if account
   *   creation fails.
   */
  protected function createAdminUser(array $permissions = [], $name = NULL) {
    return $this->createUser($permissions, $name, TRUE);
  }

  /**
   * Adds a user to group as admin.
   *
   * @param \Drupal\user\UserInterface $admin
   *   The user.
   * @param \Drupal\group\Entity\GroupInterface $group
   *   The user.
   */
  protected function addGroupAdmin(UserInterface $admin, GroupInterface $group): void {
    $group->addMember($admin, [
      'group_roles' => [
        'personal-administrator',
      ],
    ]);
  }

  /**
   * Creates a media entity.
   *
   * @param array $values
   *   (optional) The values used to create the entity.
   * @param string $type
   *   (optional) The file type to attach to the entity.
   *
   * @return \Drupal\media\MediaInterface
   *   The new media entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createMedia(array $values = [], $type = 'text'): MediaInterface {
    $file = $this->createFile($type);
    /** @var \Drupal\media\MediaStorage $storage */
    $storage = $this->container->get('entity_type.manager')->getStorage('media');
    $media = $storage->create($values + [
      'name' => [
        'value' => $this->randomMachineName(),
      ],
      'bundle' => [
        'target_id' => 'document',
      ],
      'field_media_file' => [
        'target_id' => $file->id(),
        'display' => 1,
      ],
    ]);
    $media->enforceIsNew();
    $media->save();

    $this->markEntityForCleanup($media);

    return $media;
  }

  /**
   * Creates a file entity.
   *
   * @param string $type
   *   (optional) The file type.
   * @param int $index
   *   The index of the test files which is going to be used to create the file.
   *
   * @return \Drupal\file\FileInterface
   *   The new file entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createFile($type = 'text', $index = 0): FileInterface {
    /** @var array $test_files */
    $test_files = $this->getTestFiles($type);
    $file = File::create((array) $test_files[$index]);
    $file->save();

    $this->markEntityForCleanup($file);

    return $file;
  }

  /**
   * Creates a reference.
   *
   * @param array $values
   *   (Optional) Default values for the reference.
   *
   * @return \Drupal\bibcite_entity\Entity\ReferenceInterface
   *   The new reference entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createReference(array $values = []) : ReferenceInterface {
    $reference = Reference::create($values + [
      'html_title' => $this->randomMachineName(),
      'type' => 'artwork',
      'bibcite_year' => [
        'value' => 1980,
      ],
      'distribution' => [
        [
          'value' => 'citation_distribute_repec',
        ],
      ],
      'status' => [
        'value' => 1,
      ],
    ]);

    $reference->save();

    $this->markEntityForCleanup($reference);

    return $reference;
  }

  /**
   * Mark an config for deletion.
   *
   * Any configurations you create when running against an installed site should
   * be flagged for deletion to ensure isolation between tests.
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityInterface $config_entity
   *   The configuration to delete.
   */
  protected function markConfigForCleanUp(ConfigEntityInterface $config_entity): void {
    $this->cleanUpConfigs[] = $config_entity;
  }

  /**
   * Triggers a URL visit via a vsite.
   *
   * @param string $url
   *   The URL to visit.
   * @param \Drupal\group\Entity\GroupInterface $vsite
   *   The vsite to be used.
   */
  protected function visitViaVsite(string $url, GroupInterface $vsite): void {
    $this->visit("{$vsite->get('path')->getValue()[0]['alias']}/$url");
  }

  /**
   * Adds a content entity as a group content entity.
   *
   * This is a wrapper of GroupInterface::addContent.
   * Only difference is that, you do not have to pass plugin_id, and this method
   * will decide the plugin_id for you.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The content entity to add to the group.
   * @param \Drupal\group\Entity\GroupInterface $group
   *   The group where the content will be added.
   * @param array $values
   *   (optional) Extra values to add to the group content relationship. You
   *   cannot overwrite the group ID (gid) or entity ID (entity_id).
   */
  protected function addGroupContent(EntityInterface $entity, GroupInterface $group, array $values = []): void {
    $plugin_id = "group_entity:{$entity->getEntityTypeId()}";

    if ($entity->getEntityTypeId() === 'node') {
      $plugin_id = "group_node:{$entity->bundle()}";
    }

    $group->addContent($entity, $plugin_id, $values);
  }

  /**
   * Creates a menu link for a vsite.
   *
   * @param \Drupal\bibcite_entity\Entity\ReferenceInterface $reference
   *   Publication in context.
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Vsite in context.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createMenuLinkContent(ReferenceInterface $reference, GroupInterface $group): void {
    $menuLink = MenuLinkContent::create([
      'link' => ['uri' => 'entity:bibcite_reference/' . $reference->id()],
      'langcode' => $reference->language()->getId(),
      'enabled' => TRUE,
      'title' => 'Test Title Menu',
      'description' => 'This is a test',
      'menu_name' => 'menu-primary-' . $group->id(),
    ]);
    $menuLink->save();
    $this->markEntityForCleanup($menuLink);
  }

  /**
   * Loads node by title.
   *
   * If there are multiple nodes by same title then this will return only the
   * first one. So, make sure the title is unique when you are creating it in a
   * test.
   *
   * @param string $title
   *   The title.
   *
   * @return \Drupal\node\NodeInterface|false
   *   Returns the node if found, otherwise FALSE.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function loadNodeByTitle(string $title) {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');
    $nodes = $entity_type_manager
      ->getStorage('node')
      ->loadByProperties(['title' => $title]);

    return reset($nodes);
  }

}

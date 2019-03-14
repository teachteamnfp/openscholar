<?php

namespace Drupal\Tests\os_redirect\ExistingSite;

use Drupal\Core\Entity\EntityInterface;
use Drupal\group\Entity\GroupInterface;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Test base for redirect tests.
 */
class OsRedirectTestBase extends ExistingSiteBase {

  /**
   * Test group.
   *
   * @var \Drupal\group\Entity\Group
   */
  protected $group;

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
    $this->group = $this->createGroup([
      'path' => '/test-group',
    ]);
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
   * Creates a redirect.
   *
   * @param array $values
   *   (optional) The values used to create the entity.
   *
   * @return \Drupal\redirect\Entity\Redirect
   *   The created redirect entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function createRedirect(array $values = []) : EntityInterface {
    $redirect = $this->entityTypeManager->getStorage('redirect')->create($values + [
      'type' => 'redirect',
    ]);
    $redirect->enforceIsNew();
    $redirect->save();

    $this->markEntityForCleanup($redirect);

    return $redirect;
  }

}

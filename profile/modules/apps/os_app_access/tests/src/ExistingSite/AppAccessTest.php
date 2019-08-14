<?php

namespace Drupal\Tests\os_app_access\ExistingSite;

use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Access\AccessResultNeutral;
use Drupal\os_app_access\AppAccessLevels;

/**
 * AppAccessTest.
 *
 * @covers \Drupal\os_app_access\AppAccessLevels
 * @coversDefaultClass \Drupal\os_app_access\Access\AppAccess
 * @group kernel
 * @group os
 */
class AppAccessTest extends AppAccessTestBase {

  /**
   * @covers ::access
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function test(): void {
    // Setup.
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $this->container->get('config.factory');
    /** @var \Drupal\Core\Config\Config $mut_app_access_config */
    $mut_app_access_config = $config_factory->getEditable('os_app_access.access');
    /** @var \Drupal\os_app_access\Access\AppAccess $os_app_access_service */
    $os_app_access_service = $this->container->get('os_app_access.app_access');
    /** @var \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager */
    $vsite_context_manager = $this->container->get('vsite.context_manager');
    $group_admin = $this->createUser();
    $this->addGroupAdmin($group_admin, $this->group);
    $group_member = $this->createUser();
    $this->group->addMember($group_member);

    // Tests.
    // Public access level test.
    $mut_app_access_config->set('blog', AppAccessLevels::PUBLIC)->save();
    /** @var \Drupal\Core\Access\AccessResult $result */
    $result = $os_app_access_service->access($group_admin, 'blog');
    $this->assertInstanceOf(AccessResultNeutral::class, $result);
    $this->assertContains('app:access_changed', $result->getCacheTags());
    $this->assertContains('vsite', $result->getCacheContexts());

    // Private access level test.
    $mut_app_access_config->set('blog', AppAccessLevels::PRIVATE)->save();
    /** @var \Drupal\Core\Access\AccessResult $result */
    $result = $os_app_access_service->access($group_admin, 'blog');
    $this->assertInstanceOf(AccessResultNeutral::class, $result);

    $vsite_context_manager->activateVsite($this->group);
    /** @var \Drupal\Core\Access\AccessResult $result */
    $result = $os_app_access_service->access($group_admin, 'blog');
    $this->assertInstanceOf(AccessResultNeutral::class, $result);

    /** @var \Drupal\Core\Access\AccessResult $result */
    $result = $os_app_access_service->access($group_member, 'blog');
    $this->assertInstanceOf(AccessResultForbidden::class, $result);

    // Disabled access level test.
    $mut_app_access_config->set('blog', AppAccessLevels::DISABLED)->save();
    /** @var \Drupal\Core\Access\AccessResult $result */
    $result = $os_app_access_service->access($group_admin, 'blog');
    $this->assertInstanceOf(AccessResultForbidden::class, $result);
  }

}

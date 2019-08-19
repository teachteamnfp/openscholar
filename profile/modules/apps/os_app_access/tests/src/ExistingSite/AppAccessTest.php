<?php

namespace Drupal\Tests\os_app_access\ExistingSite;

use Drupal\Core\Access\AccessResultAllowed;
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
   * @covers ::cacheAccessResult
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testAccess(): void {
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
    $non_group_member = $this->createUser();

    // Tests.
    // Public access level test.
    $mut_app_access_config->set('blog', AppAccessLevels::PUBLIC)->save();
    $this->assertInstanceOf(AccessResultNeutral::class, $os_app_access_service->access($group_admin, 'blog'));

    // Private access level test.
    $mut_app_access_config->set('blog', AppAccessLevels::PRIVATE)->save();
    $this->assertInstanceOf(AccessResultNeutral::class, $os_app_access_service->access($group_admin, 'blog'));

    $vsite_context_manager->activateVsite($this->group);
    $this->assertInstanceOf(AccessResultAllowed::class, $os_app_access_service->access($group_admin, 'blog'));

    $this->assertInstanceOf(AccessResultForbidden::class, $os_app_access_service->access($non_group_member, 'blog'));

    // Disabled access level test.
    $mut_app_access_config->set('blog', AppAccessLevels::DISABLED)->save();
    $this->assertInstanceOf(AccessResultForbidden::class, $os_app_access_service->access($group_admin, 'blog'));

    // Test whether access result is cached.
    $this->assertContains('app:access_changed', $os_app_access_service->access($group_admin, 'blog')->getCacheTags());
    $this->assertContains('vsite', $os_app_access_service->access($group_admin, 'blog')->getCacheContexts());
  }

  /**
   * @covers ::accessFromRouteMatch
   * @covers ::cacheAccessResult
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testAccessFromRouteMatch(): void {
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
    $non_group_member = $this->createUser();

    // Tests.
    // Test inactive vsite.
    $this->assertInstanceOf(AccessResultNeutral::class, $os_app_access_service->accessFromRouteMatch($group_admin, 'blog'));

    $vsite_context_manager->activateVsite($this->group);

    // Test disabled access level.
    $mut_app_access_config->set('blog', AppAccessLevels::DISABLED)->save();
    $this->assertInstanceOf(AccessResultForbidden::class, $os_app_access_service->accessFromRouteMatch($group_admin, 'blog'));

    // Test public access level.
    $mut_app_access_config->set('blog', AppAccessLevels::PUBLIC)->save();
    $this->assertInstanceOf(AccessResultAllowed::class, $os_app_access_service->accessFromRouteMatch($group_member, 'blog'));

    // Test private access level.
    $mut_app_access_config->set('blog', AppAccessLevels::PRIVATE)->save();
    $this->assertInstanceOf(AccessResultAllowed::class, $os_app_access_service->accessFromRouteMatch($group_admin, 'blog'));
    $this->assertInstanceOf(AccessResultAllowed::class, $os_app_access_service->accessFromRouteMatch($group_member, 'blog'));
    $this->assertInstanceOf(AccessResultForbidden::class, $os_app_access_service->accessFromRouteMatch($non_group_member, 'blog'));

    // Test whether access result is cached.
    $this->assertContains('app:access_changed', $os_app_access_service->accessFromRouteMatch($group_member, 'blog')->getCacheTags());
    $this->assertContains('vsite', $os_app_access_service->accessFromRouteMatch($group_member, 'blog')->getCacheContexts());
  }

}

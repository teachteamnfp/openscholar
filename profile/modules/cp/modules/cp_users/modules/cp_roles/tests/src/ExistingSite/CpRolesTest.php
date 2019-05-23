<?php

namespace Drupal\Tests\cp_roles\ExistingSite;

/**
 * CpRolesTest.
 *
 * @group kernel
 * @group cp
 */
class CpRolesTest extends CpRolesExistingSiteTestBase {

  /**
   * Tests - Role created for a vsite should not be available for other vsite.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testVsiteSpecificRole(): void {
    $vsite1 = $this->group;
    $vsite2 = $this->createGroup();

    /** @var \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager */
    $vsite_context_manager = $this->container->get('vsite.context_manager');
    $vsite_context_manager->activateVsite($vsite1);

    $this->createGroupRole([
      'id' => 'vsite1role',
    ]);

    $vsite_context_manager->activateVsite($vsite2);

    /** @var \Drupal\vsite\Config\HierarchicalStorageInterface $hierarchical_storage */
    $hierarchical_storage = $this->container->get('hierarchical.storage');
    $vsite1_configs = $hierarchical_storage->listAllFromLevel("group.role.personal-{$vsite1->id()}", 0);
    $vsite2_configs = $hierarchical_storage->listAllFromLevel("group.role.personal-{$vsite2->id()}", 0);

    $this->assertContains("group.role.personal-{$vsite1->id()}-vsite1role", $vsite1_configs);
    $this->assertNotContains("group.role.personal-{$vsite1->id()}-vsite1role", $vsite2_configs);
  }

}

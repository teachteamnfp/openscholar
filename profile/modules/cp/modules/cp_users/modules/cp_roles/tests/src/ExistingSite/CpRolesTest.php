<?php

namespace Drupal\Tests\cp_roles\ExistingSite;

use Drupal\group\Entity\GroupRole;

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

    $this->createRoleForGroup($vsite1, [
      'id' => 'vsite1role',
    ]);

    $vsite_context_manager->activateVsite($vsite2);

    /** @var \Drupal\vsite\Config\HierarchicalStorageInterface $hierarchical_storage */
    $hierarchical_storage = $this->container->get('hierarchical.storage');
    $vsite1_configs = $hierarchical_storage->listAllFromLevel("group.role.personal-{$vsite1->id()}", 0);
    $vsite2_configs = $hierarchical_storage->listAllFromLevel("group.role.personal-{$vsite2->id()}", 0);

    $this->assertContains("group.role.personal-{$vsite1->id()}_vsite1role", $vsite1_configs);
    $this->assertNotContains("group.role.personal-{$vsite1->id()}_vsite1role", $vsite2_configs);
  }

  /**
   * Tests the customizations made in member group role.
   *
   * @covers ::cp_roles_group_type_insert
   */
  public function testMemberRoleCustomization(): void {
    $this->createGroupType([
      'id' => 'cyberpunk',
    ]);

    $member_role = GroupRole::load('cyberpunk-member');

    $this->assertFalse($member_role->get('internal'));
    $this->assertEquals('Basic member', $member_role->get('label'));
  }

}

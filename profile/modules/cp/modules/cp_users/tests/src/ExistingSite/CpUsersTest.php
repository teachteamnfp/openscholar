<?php

namespace Drupal\Tests\cp_users\ExistingSite;

use Drupal\group\Entity\GroupRole;

/**
 * CpUsersTest.
 *
 * @group kernel
 * @group cp
 */
class CpUsersTest extends CpUsersExistingSiteTestBase {

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
   * @covers ::cp_users_group_type_insert
   */
  public function testMemberRoleCustomization(): void {
    $this->createGroupType([
      'id' => 'cyberpunk',
    ]);

    $member_role = GroupRole::load('cyberpunk-member');

    $this->assertFalse($member_role->get('internal'));
    $this->assertEquals('Basic member', $member_role->get('label'));
  }

  /**
   * Tests cache invalidation on group role save.
   *
   * @covers ::cp_users_group_role_update
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testCacheInvalidation(): void {
    $role = $this->createGroupRole();
    /** @var \Drupal\Core\Cache\CacheTagsChecksumInterface $cache_tags_invalidator_checksum */
    $cache_tags_invalidator_checksum = $this->container->get('cache_tags.invalidator.checksum');
    $checksum_before_save = $cache_tags_invalidator_checksum->getCurrentChecksum(['config:system.menu.control-panel']);

    // Positive test.
    $role->grantPermission('manage cp appearance')->save();
    $this->assertNotEquals($checksum_before_save, $cache_tags_invalidator_checksum->getCurrentChecksum(['config:system.menu.control-panel']));
    $this->assertGreaterThan($checksum_before_save, $cache_tags_invalidator_checksum->getCurrentChecksum(['config:system.menu.control-panel']));

    // Negative test.
    $checksum_before_save = $cache_tags_invalidator_checksum->getCurrentChecksum(['config:system.menu.control-panel']);
    $role->save();
    $this->assertEquals($checksum_before_save, $cache_tags_invalidator_checksum->getCurrentChecksum(['config:system.menu.control-panel']));
  }

}

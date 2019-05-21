<?php

namespace Drupal\Tests\cp_roles\ExistingSite;

use Drupal\group\Entity\GroupRole;
use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Class CpRoleTest.
 *
 * @group kernel
 * @group cp
 */
class CpRoleTest extends OsExistingSiteTestBase {

  /**
   * Tests custom role save.
   *
   * @covers ::cp_roles_entity_presave
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testSave(): void {
    /** @var \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager */
    $vsite_context_manager = $this->container->get('vsite.context_manager');
    $vsite_context_manager->activateVsite($this->group);

    $group_role = GroupRole::create([
      'id' => 'cprole',
      'label' => $this->randomMachineName(),
      'group_type' => 'personal',
    ]);
    $group_role->save();

    $this->assertEquals("personal-{$this->group->id()}-cprole", $group_role->id());

    $group_role->delete();
  }

}

<?php

namespace Drupal\Tests\cp_roles\ExistingSite;

/**
 * Class CpRoleTest.
 *
 * @group kernel
 * @group cp
 */
class CpRolesTest extends CpRolesTestBase {

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

    $group_role = $this->createGroupRole([
      'id' => 'cprole',
    ]);

    $this->assertEquals("personal-{$this->group->id()}-cprole", $group_role->id());
  }

}

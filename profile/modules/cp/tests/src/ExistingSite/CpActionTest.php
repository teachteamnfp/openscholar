<?php

namespace Drupal\Tests\cp\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * CpActionTest.
 *
 * @group cp
 * @group kernel
 */
class CpActionTest extends OsExistingSiteTestBase {

  /**
   * Test modified action plugin confirm_form_route_name value.
   */
  public function testActionPluginParameters() {
    /** @var \Drupal\Component\Plugin\PluginManagerInterface $manager */
    $action = $this->container->get('plugin.manager.action');

    $plugin = $action->getDefinition('cp_entity:delete_action:node');
    $this->assertEquals('cp.node.multiple_delete_confirm', $plugin['confirm_form_route_name']);

    $plugin = $action->getDefinition('cp_entity:delete_action:bibcite_reference');
    $this->assertEquals('cp.bibcite_reference.delete_multiple_form', $plugin['confirm_form_route_name']);
  }

}

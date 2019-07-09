<?php

namespace Drupal\Tests\os\ExistingSite;

use Drupal\Core\Url;
use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Tests whether menu local tasks for certain routes are disabled.
 *
 * @group functional
 * @group os
 */
class DisableLocalTasksTest extends OsExistingSiteTestBase {

  /**
   * @covers ::os_menu_local_tasks_alter
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function test(): void {
    $node = $this->createNode();
    $this->visit("/node/{$node->id()}");

    $edit_url = Url::fromRoute('entity.node.edit_form', [
      'node' => $node->id(),
    ]);
    $delete_url = Url::fromRoute('entity.node.delete_form', [
      'node' => $node->id(),
    ]);

    $this->assertSession()->linkByHrefNotExists($edit_url->toString());
    $this->assertSession()->linkByHrefNotExists($delete_url->toString());
  }

}

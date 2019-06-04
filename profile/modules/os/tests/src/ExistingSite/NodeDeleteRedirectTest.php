<?php

namespace Drupal\Tests\os\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Test node post delete redirect behavior.
 *
 * @group functional
 * @group os
 */
class NodeDeleteRedirectTest extends OsExistingSiteTestBase {

  /**
   * @covers ::os_form_node_confirm_form_alter
   * @covers ::os_alter_post_node_delete_redirect
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  public function test(): void {
    $group_admin = $this->createUser();
    $this->addGroupAdmin($group_admin, $this->group);
    $blog = $this->createNode([
      'type' => 'blog',
    ]);
    $this->group->addContent($blog, 'group_node:blog');
    $this->drupalLogin($group_admin);

    $this->visitViaVsite("/node/{$blog->id()}/delete", $this->group);
    $this->getSession()->getPage()->pressButton('Delete');

    $this->assertContains('/blog', $this->getSession()->getCurrentUrl());
  }

}

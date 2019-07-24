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
    $news = $this->createNode([
      'type' => 'news',
      'field_date' => [
        'value' => '2020-04-16',
      ],
    ]);
    $this->group->addContent($news, 'group_node:news');
    $this->drupalLogin($group_admin);

    $this->visitViaVsite("/node/{$news->id()}/delete", $this->group);
    $this->getSession()->getPage()->pressButton('Delete');

    $this->assertContains('/news', $this->getSession()->getCurrentUrl());
  }

}

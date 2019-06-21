<?php

namespace Drupal\Tests\vsite\ExistingSiteJavascript;

use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * Class VsiteNodePathAlias.
 *
 * @package Drupal\Tests\vsite\ExistingSite
 * @group functional-javascript
 * @group vsite
 */
class VsiteNodePathAlias extends OsExistingSiteJavascriptTestBase {

  /**
   * Test to modify news content type path alias.
   */
  public function testNodeNewsModifyGenerateAlias() {
    $web_assert = $this->assertSession();
    $node = $this->createNode([
      'type' => 'news',
      'field_date' => '2018-11-30',
    ]);
    $this->group->addContent($node, 'group_node:news');
    $this->drupalLogin($this->createAdminUser());
    $this->visitViaVsite('node/' . $node->id() . '/edit', $this->group);
    $web_assert->statusCodeEquals(200);
    $page = $this->getSession()->getPage();
    // Path alias #field_prefix should contains group alias.
    $web_assert->pageTextContains($this->groupAlias . '/');
    $page->findField('path[0][pathauto]')->press();
    $page->fillField('path[0][alias]', '/news/new-alias-value');
    $page->findButton('Save')->press();
    // Visit node at new alias.
    $this->visitViaVsite('news/new-alias-value', $this->group);
    $web_assert->statusCodeEquals(200);
    $web_assert->pageTextContains($node->label());
  }

}

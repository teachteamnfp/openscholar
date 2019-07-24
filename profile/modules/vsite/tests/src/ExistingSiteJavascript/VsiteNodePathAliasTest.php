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
class VsiteNodePathAliasTest extends OsExistingSiteJavascriptTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->drupalLogin($this->createAdminUser());
  }

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
    $this->visitViaVsite('node/' . $node->id() . '/edit', $this->group);
    $web_assert->statusCodeEquals(200);
    $page = $this->getSession()->getPage();
    // Path alias #field_prefix should contains group alias.
    $url_alias_markup = $page->findById('edit-path-0')->getHtml();
    $this->assertContains($this->groupAlias, $url_alias_markup);
    $page->findField('path[0][pathauto]')->press();
    $page->fillField('path[0][alias]', '/news/new-alias-value');
    $page->findButton('Save')->press();
    // Visit node at new alias.
    $this->visitViaVsite('news/new-alias-value', $this->group);
    $web_assert->statusCodeEquals(200);
    $web_assert->pageTextContains($node->label());
  }

  /**
   * Test all path where should be visible the alias.
   *
   * @dataProvider collectionOfVisiblePath
   */
  public function testPrintingGroupAliasAtPath($path) {
    $web_assert = $this->assertSession();
    $this->visitViaVsite($path, $this->group);
    $web_assert->statusCodeEquals(200);
    $page = $this->getSession()->getPage();
    // Path alias #field_prefix should contains group alias.
    $path_alias_content = $page->find('css', '.form-item-path-0-alias')->getHtml();
    $this->assertContains($this->groupAlias . '/', $path_alias_content);
  }

  /**
   * Collection of available field prefix value.
   */
  public function collectionOfVisiblePath() {
    return [
      [
        'node/add/blog',
        'node/add/class',
        'node/add/events',
        'node/add/faq',
        'node/add/link',
        'node/add/news',
        'node/add/page',
        'node/add/person',
        'node/add/presentation',
        'node/add/software_project',
        'node/add/software_release',
      ],
    ];
  }

}

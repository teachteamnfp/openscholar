<?php

namespace Drupal\Tests\vsite\ExistingSiteJavascript;

use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * Tests contextual link alterations for vsites.
 *
 * @group functional-javascript
 * @group vsite
 */
class VsiteContextualLinksTest extends OsExistingSiteJavascriptTestBase {

  /**
   * Tests whether the destination parameter is valid.
   *
   * @covers ::vsite_js_settings_alter
   */
  public function testDestinationParameter(): void {
    // Setup.
    $blog = $this->createNode([
      'type' => 'blog',
    ]);
    $this->group->addContent($blog, 'group_node:blog');
    $admin = $this->createUser([
      'access contextual links',
    ], NULL, TRUE);
    $this->group->addMember($admin);
    $this->drupalLogin($admin);

    $this->visitViaVsite('blog', $this->group);
    $this->assertSession()->waitForElement('css', '.contextual button');

    // Tests.
    /** @var \Behat\Mink\Element\NodeElement|null $edit_contextual_link */
    $edit_contextual_link = $this->getSession()->getPage()->find('css', '.contextual-links .entitynodeedit-form a');
    $this->assertNotNull($edit_contextual_link);

    // Retrieve the destination parameter value from the contextual link.
    $href = $edit_contextual_link->getAttribute('href');
    list(, $query) = explode('?', $href);
    list(, $destination) = explode('=', $query);

    $this->assertEquals("{$this->groupAlias}/blog", $destination);
  }

}

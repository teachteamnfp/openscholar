<?php

namespace Drupal\Tests\os_pages\ExistingSiteJavascript;

/**
 * PagesFormTest.
 *
 * @group functional-javascript
 * @group pages
 */
class PagesFormTest extends TestBase {

  /**
   * Tests the custom code written for node add page form.
   *
   * @covers ::os_pages_node_prepare_form
   * @covers ::os_pages_form_node_page_form_alter
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\UnsupportedDriverActionException
   * @throws \Behat\Mink\Exception\DriverException
   */
  public function testPageAddForm(): void {
    $group_member = $this->createUser();
    $this->group->addMember($group_member);
    $this->drupalLogin($group_member);

    // Test top-page creation.
    $title = $this->randomMachineName();
    $this->visitViaVsite('node/add/page', $this->group);

    $page_add_option = $this->getSession()->getPage()->find('css', '#edit-book summary');
    $this->assertNotNull($page_add_option);
    $page_add_option->click();

    $this->getSession()->getPage()->fillField('title[0][value]', $title);
    $this->getSession()->getPage()->selectFieldOption('book[bid]', 'new');
    $this->waitForAjaxToFinish();
    $this->getSession()->getPage()->pressButton('Save');

    $book = $this->getNodeByTitle($title);

    // Test sub-page level 1 creation.
    $title = $this->randomMachineName();
    $this->visitViaVsite("node/add/page?parent={$book->id()}", $this->group);

    $page_add_option = $this->getSession()->getPage()->find('css', '#edit-book summary');
    $this->assertNotNull($page_add_option);
    $page_add_option->click();

    $this->getSession()->getPage()->fillField('title[0][value]', $title);
    $this->getSession()->getPage()->pressButton('Save');
    $this->assertSession()->statusCodeEquals(200);

    $page = $this->getNodeByTitle($title, TRUE);

    $this->assertEquals($book->id(), $page->book['bid']);

    // Test sub-page level 2 creation.
    $title = $this->randomMachineName();
    $this->visitViaVsite("node/add/page?parent={$page->id()}", $this->group);

    $page_add_option = $this->getSession()->getPage()->find('css', '#edit-book summary');
    $this->assertNotNull($page_add_option);
    $page_add_option->click();

    $this->getSession()->getPage()->fillField('title[0][value]', $title);
    $this->getSession()->getPage()->pressButton('Save');

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->getNodeByTitle($title);

    $this->assertEquals($book->id(), $node->book['bid']);
    $this->assertEquals($page->id(), $node->book['pid']);

    // Clean up.
    $node->delete();
    $page->delete();
    $book->delete();
  }

}

<?php

namespace Drupal\Tests\os_pages\ExistingSite;

/**
 * PagesFormTest.
 *
 * @group vsite
 * @group functional
 * @group pages
 */
class PagesFormTest extends TestBase {

  /**
   * Tests the custom code written for node add page form.
   *
   * @covers ::os_pages_form_node_page_form_alter
   * @covers ::os_pages_node_page_form_set_book
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testPageAddForm() {
    $this->drupalLogin($this->createUser([], [], TRUE));

    $title = $this->randomMachineName();
    $this->drupalPostForm('/node/add/page', [
      'title[0][value]' => $title,
      'book[bid]' => 'new',
    ], 'Save');
    $book = $this->getNodeByTitle($title);

    $title = $this->randomMachineName();
    $edit = [
      'title[0][value]' => $title,
      'book[bid]' => $book->id(),
    ];
    $this->drupalPostForm('/node/add/page', $edit, 'Save');
    $page = $this->getNodeByTitle($title);

    $this->drupalLogin($this->createUser());

    $title = $this->randomMachineName();
    $this->drupalPostForm('/node/add/page', [
      'title[0][value]' => $title,
    ], 'Save', [
      'query' => [
        'pid' => $page->id(),
      ],
    ]);

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->getNodeByTitle($title);

    $this->assertEquals($book->id(), $node->book['bid']);
    $this->assertEquals($page->id(), $node->book['pid']);

    $node->delete();
    $page->delete();
    $book->delete();
  }

}

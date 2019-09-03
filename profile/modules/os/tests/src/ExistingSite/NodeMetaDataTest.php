<?php

namespace Drupal\Tests\os\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * NodeMetaDataTest test.
 *
 * @group functional
 * @group os
 */
class NodeMetaDataTest extends OsExistingSiteTestBase {

  /**
   * Group administrator.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $groupAdmin;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->group = $this->createGroup();
    $this->groupAdmin = $this->createUser();
    $this->addGroupAdmin($this->groupAdmin, $this->group);
    $this->drupalLogin($this->groupAdmin);
  }

  /**
   * Test noindex setting has effect on nodes.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testNodeNoIndex(): void {
    // Test positive case for node blog.
    $node1 = $this->createNode([
      'type' => 'blog',
      'noindex' => TRUE,
    ]);
    $this->group->addContent($node1, 'group_node:blog');
    $this->visitViaVsite('node/' . $node1->id(), $this->group);
    $this->assertSession()->responseContains('NOINDEX');
    $this->assertSession()->responseContains('NOFOLLOW');

    // Test negative case for node blog.
    $node2 = $this->createNode([
      'type' => 'blog',
    ]);
    $this->group->addContent($node2, 'group_node:blog');
    $this->visitViaVsite('node/' . $node2->id(), $this->group);
    $this->assertSession()->responseNotContains('NOINDEX');
    $this->assertSession()->responseNotContains('NOFOLLOW');

    // Test positive case for node class.
    $node3 = $this->createNode([
      'type' => 'class',
      'noindex' => TRUE,
    ]);
    $this->group->addContent($node3, 'group_node:class');
    $this->visitViaVsite('node/' . $node3->id(), $this->group);
    $this->assertSession()->responseContains('NOINDEX');
    $this->assertSession()->responseContains('NOFOLLOW');

    // Test negative case for node class.
    $node4 = $this->createNode([
      'type' => 'class',
    ]);
    $this->group->addContent($node4, 'group_node:class');
    $this->visitViaVsite('node/' . $node4->id(), $this->group);
    $this->assertSession()->responseNotContains('NOINDEX');
    $this->assertSession()->responseNotContains('NOFOLLOW');
  }

}

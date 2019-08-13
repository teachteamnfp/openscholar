<?php

namespace Drupal\Tests\vsite\ExistingSite;

/**
 * Test the FormDeleteDestination.
 *
 * @group vsite
 * @group functional
 */
class FormDeleteDestinationTest extends VsiteExistingSiteTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $group_admin = $this->createUser();
    $this->addGroupAdmin($group_admin, $this->group);
    $this->drupalLogin($group_admin);
  }

  /**
   * Test node edit page with destination.
   */
  public function testNodeEditDeleteWithDestination() {
    $node = $this->createNode([
      'type' => 'blog',
    ]);
    $this->group->addContent($node, 'group_node:blog');
    $this->visitViaVsite('node/' . $node->id() . '/edit?destination=/hello', $this->group);
    $this->assertSession()->linkByHrefExists($this->groupAlias . '/node/' . $node->id() . '/delete?destination=' . $this->groupAlias . '/blog');
  }

  /**
   * Test node edit page without destination.
   */
  public function testNodeEditDeleteWithoutDestination() {
    $node = $this->createNode([
      'type' => 'blog',
    ]);
    $this->group->addContent($node, 'group_node:blog');
    $this->visitViaVsite('node/' . $node->id() . '/edit', $this->group);
    $this->assertSession()->linkByHrefExists($this->groupAlias . '/node/' . $node->id() . '/delete?destination=' . $this->groupAlias . '/blog');
  }

  /**
   * Test reference edit page with destination.
   */
  public function testReferenceEditDeleteWithDestination() {
    $publication = $this->createReference();
    $this->group->addContent($publication, 'group_entity:bibcite_reference');
    $this->visitViaVsite('bibcite/reference/' . $publication->id() . '/edit?destination=/hello', $this->group);
    $this->assertSession()->linkByHrefExists($this->groupAlias . '/bibcite/reference/' . $publication->id() . '/delete?destination=' . $this->groupAlias . '/publications');
  }

  /**
   * Test reference edit page without destination.
   */
  public function testReferenceEditDeleteWithoutDestination() {
    $publication = $this->createReference();
    $this->group->addContent($publication, 'group_entity:bibcite_reference');
    $this->visitViaVsite('bibcite/reference/' . $publication->id() . '/edit', $this->group);
    $this->assertSession()->linkByHrefExists($this->groupAlias . '/bibcite/reference/' . $publication->id() . '/delete?destination=' . $this->groupAlias . '/publications');
  }

}

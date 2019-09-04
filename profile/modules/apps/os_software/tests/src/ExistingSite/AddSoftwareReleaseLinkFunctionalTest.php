<?php

namespace Drupal\Tests\os_software\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Class AddSoftwareReleaseLinkFunctionalTest.
 *
 * @group os
 * @group functional
 *
 * @package Drupal\Tests\os_software\ExistingSite
 */
class AddSoftwareReleaseLinkFunctionalTest extends OsExistingSiteTestBase {

  protected $projectNode;
  protected $media;
  protected $groupAdmin;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->projectNode = $this->createNode([
      'type' => 'software_project',
    ]);
    $this->group->addContent($this->projectNode, 'group_node:software_project');
    $this->groupAdmin = $this->createUser();
    $this->addGroupAdmin($this->groupAdmin, $this->group);
  }

  /**
   * Test add software release link appear.
   */
  public function testAddSoftwareReleaseLinkAppear() {
    $web_assert = $this->assertSession();
    $this->visitViaVsite('node/' . $this->projectNode->id(), $this->group);
    $web_assert->linkExists('Add new software release');
    $web_assert->linkByHrefExists('node/add/software_release?field_software_project=' . $this->projectNode->id());
  }

  /**
   * Test add software release link not visible on other node types.
   */
  public function testAddSoftwareReleaseLinkHidden() {
    $web_assert = $this->assertSession();
    $node = $this->createNode([
      'type' => 'software_release',
    ]);
    $this->group->addContent($node, 'group_node:software_release');
    $this->visitViaVsite('node/' . $node->id(), $this->group);
    $web_assert->linkNotExists('Add new software release');
  }

  /**
   * Test add software release form page pre-populate valid id.
   */
  public function testAddSoftwareReleaseFormPageValidId() {
    $this->drupalLogin($this->groupAdmin);
    $web_assert = $this->assertSession();
    $this->visitViaVsite('node/add/software_release?field_software_project=' . $this->projectNode->id(), $this->group);
    $web_assert->pageTextContains('Create Software Release');
    $web_assert->fieldValueEquals('field_software_project[0][target_id]', $this->projectNode->label() . ' (' . $this->projectNode->id() . ')');
  }

  /**
   * Test add software release form page pre-populate invalid id.
   */
  public function testAddSoftwareReleaseFormPageInvalidId() {
    // Not exists project.
    $this->drupalLogin($this->groupAdmin);
    $web_assert = $this->assertSession();
    $this->visitViaVsite('node/add/software_release?field_software_project=99999', $this->group);
    $web_assert->fieldValueEquals('field_software_project[0][target_id]', '');

    // Not related project.
    $projectNodeNotGrouped = $this->createNode([
      'type' => 'software_project',
    ]);
    $this->visitViaVsite('node/add/software_release?field_software_project=' . $projectNodeNotGrouped->id(), $this->group);
    $web_assert->fieldValueEquals('field_software_project[0][target_id]', '');

    // Empty field.
    $this->visitViaVsite('node/add/software_release?field_software_project=', $this->group);
    $web_assert->fieldValueEquals('field_software_project[0][target_id]', '');

    // Not project node.
    $nodeGrouped = $this->createNode([
      'type' => 'news',
    ]);
    $this->group->addContent($nodeGrouped, 'group_node:news');
    $this->visitViaVsite('node/add/software_release?field_software_project=' . $nodeGrouped->id(), $this->group);
    $web_assert->fieldValueEquals('field_software_project[0][target_id]', '');
  }

}

<?php

namespace Drupal\Tests\vsite\ExistingSite;

/**
 * Tests VsiteMemberCreationTest.
 *
 * @group functional
 * @group vsite
 */
class VsiteMemberCreationTest extends VsiteExistingSiteTestBase {


  /**
   * Admin User entity.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $admin;

  /**
   * Vsite Manager service.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManager
   */
  protected $vsiteManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->admin = $this->createUser([], [], TRUE);
    $this->drupalLogin($this->admin);

    $this->vsiteManager = $this->container->get('vsite.context_manager');
    $this->entityTypeManager = $this->container->get('entity_type.manager');
  }

  /**
   * Tests membership while group creation using wizard.
   */
  public function testMembershipUsingGroupWizard(): void {

    // Test group creation via two step wizard because it is noticed duplicate
    // membership issue occurs via this form only.
    $this->drupalGet('group/add/personal');
    $edit = [
      'label[0][value]' => 'WizardTestGroup',
      'path[0][alias]' => '/test-alias',
    ];
    $this->submitForm($edit, 'edit-submit');
    $this->submitForm([], 'edit-submit');

    $group_storage = $this->entityTypeManager->getStorage('group');
    $group_array = $group_storage->loadByProperties(['label' => 'WizardTestGroup']);
    $this->assertCount(1, $group_array);
    /** @var \Drupal\group\Entity\Group $group */
    $group = array_shift($group_array);
    $members = $group->getContent('group_membership');
    $this->assertCount(1, $members, 'No More than one member should exist.');

  }

}

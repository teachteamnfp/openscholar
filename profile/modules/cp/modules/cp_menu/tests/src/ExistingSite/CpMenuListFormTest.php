<?php

namespace Drupal\Tests\cp_menu\ExistingSite;

use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * CpMenuListFormTest.
 *
 * @group functional
 * @group cp-menu
 */
class CpMenuListFormTest extends OsExistingSiteJavascriptTestBase {

  /**
   * Test group.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $group;

  /**
   * Group Id.
   *
   * @var string
   */
  protected $id;

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

    $this->group = $this->createGroup([
      'path' => [
        'alias' => '/test-menu',
      ],
    ]);
    $this->groupAdmin = $this->createUser();
    $this->addGroupAdmin($this->groupAdmin, $this->group);
    $this->id = $this->group->id();
  }

  /**
   * Tests Menu List form Access.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testMenuListFormAccess() {

    // Test as anon.
    $this->visit('/test-menu/cp/build/menu');
    $session = $this->assertSession();
    $session->statusCodeEquals(403);

    // Test as groupAdmin.
    $this->drupalLogin($this->groupAdmin);
    $this->visit('/test-menu/cp/build/menu');
    $session->statusCodeEquals(200);
  }

  /**
   * Tests Menu List form display.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testMenuListFormDisplay() {
    // Test menu list page as group admin.
    $this->drupalLogin($this->groupAdmin);
    $this->visit('/test-menu/cp/build/menu');
    $session = $this->assertSession();
    $session->elementExists('css', '#cp-menu-build');
    $session->elementExists('css', '#cp-build-menu-table');
    $session->elementAttributeContains('css', '.menu-name', 'name', "links[menu-primary-$this->id][title][menu-name]");
    $session->elementExists('css', '.section-heading');
    $session->elementExists('css', ".section-menu-primary-$this->id-message");
    $session->elementExists('css', ".section-menu-secondary-$this->id-message");
    $session->linkByHrefExists('/test-menu/cp/build/add-menu');
  }

}

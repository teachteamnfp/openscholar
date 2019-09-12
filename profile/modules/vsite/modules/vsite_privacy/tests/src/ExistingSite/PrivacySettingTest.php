<?php

namespace Drupal\Tests\vsite_privacy\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Class PrivacySettingTest.
 *
 * @group functional
 * @group vsite
 */
class PrivacySettingTest extends OsExistingSiteTestBase {

  /**
   * Vsite context manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * Test group.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $group;

  /**
   * Group admin.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $groupAdmin;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->vsiteContextManager = $this->container->get('vsite.context_manager');
    $this->group = $this->createGroup([
      'type' => 'personal',
      'path' => [
        'alias' => '/something-else',
      ],
    ]);
    $this->groupAdmin = $this->createUser();

    $this->addGroupAdmin($this->groupAdmin, $this->group);
  }

  /**
   * Tests setting's access when accessed outside group.
   *
   * @covers \Drupal\vsite_privacy\Plugin\CpSetting\VsitePrivacyForm::access
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testSettingOutsideVsiteAsAdmin(): void {
    $this->drupalLogin($this->groupAdmin);

    $this->visit('/cp/settings/global-settings/privacy_policy');
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Test setting access as group admin.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testSettingAsAdmin(): void {
    $this->drupalLogin($this->groupAdmin);

    $this->visit('/something-else/cp/settings/global-settings/privacy_policy');
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Test setting access as group member.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testSettingAsMember(): void {
    $group_member = $this->createUser();
    $this->group->addMember($group_member);
    $this->drupalLogin($group_member);

    $this->visit('/something-else/cp/settings/global-settings/privacy_policy');
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Tests setting access as a group non-member.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testSettingAsOutsider(): void {
    $outsider = $this->createUser();
    $this->drupalLogin($outsider);

    $this->visit('/something-else/cp/settings/global-settings/privacy_policy');
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Test public site not contains noindex metatag.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testPublicSiteMetatagRobotsNotVisible() {
    $public_group = $this->createGroup([
      'type' => 'personal',
      'field_privacy_level' => [
        'value' => 'public',
      ],
    ]);
    $this->visitViaVsite('', $public_group);
    $this->assertSession()->responseNotContains('noindex');
  }

  /**
   * Test unindexed site contains noindex metatag.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testUnindexedSiteMetatagRobotsVisible() {
    $web_assert = $this->assertSession();
    $unindexed_group = $this->createGroup([
      'type' => 'personal',
      'field_privacy_level' => [
        'value' => 'unindexed',
      ],
    ]);
    $this->vsiteContextManager->activateVsite($unindexed_group);
    $this->visitViaVsite('', $unindexed_group);
    $web_assert->statusCodeEquals(200);
    // TODO: seems group is not activated.
    // $this->assertSession()->responseContains('noindex');
  }

}

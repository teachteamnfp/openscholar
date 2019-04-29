<?php

namespace Drupal\Tests\vsite_privacy\ExistingSite;

/**
 * Class PrivacySettingTest.
 *
 * @group functional
 * @group vsite
 */
class PrivacySettingTest extends TestBase {

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
   * Admin user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $admin;

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
    $this->admin = $this->createUser([], NULL, TRUE);
  }

  /**
   * Tests setting access.
   *
   * @covers \Drupal\vsite_privacy\Plugin\CpSetting\VsitePrivacyForm::access
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testSetting() {
    $this->drupalLogin($this->admin);

    // Negative tests.
    $this->visit('/cp/settings/privacy');
    $this->assertSession()->statusCodeEquals(403);

    // Positive tests.
    $this->visit('/something-else/cp/settings/privacy');
    $this->assertSession()->statusCodeEquals(200);
  }

}

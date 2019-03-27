<?php

namespace Drupal\Tests\os_twitter_pull\ExistingSite;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Tests os_twitter_pull module.
 *
 * @group twitter
 * @group kernel
 * @group other
 *
 * @coversDefaultClass \Drupal\os_twitter_pull\Form\OsTwitterPullAdminSettingsForm
 */
class OsTwitterAdminSettingsTest extends ExistingSiteBase {

  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->adminUser = $this->createUser([
      'administer twitter pull settings',
    ]);
  }

  /**
   * Test set config.
   */
  public function testSetConfigValues() {
    $web_assert = $this->assertSession();

    $this->drupalLogin($this->adminUser);

    $this->visit("/admin/config/services/os-twitter-pull");
    $web_assert->statusCodeEquals(200);

    $settings = [
      'consumer_key' => '12345',
      'consumer_secret' => '6789qwer',
      'oauth_access_token' => 'asdfgh',
      'oauth_access_token_secret' => 'yxcvbn',
    ];
    $this->drupalPostForm(NULL, $settings, 'Save configuration');
    $this->assertContains('The configuration options have been saved.', $this->getCurrentPageContent());

    // Check saved values.
    $configFactory = $this->container->get('config.factory');
    $config = $configFactory->getEditable('os_twitter_pull.settings');
    $this->assertSame('12345', $config->get('consumer_key'));
    $this->assertSame('6789qwer', $config->get('consumer_secret'));
    $this->assertSame('asdfgh', $config->get('oauth_access_token'));
    $this->assertSame('yxcvbn', $config->get('oauth_access_token_secret'));
  }

  /**
   * Test admin access denied.
   */
  public function testSetConfigAccessDenied() {
    $web_assert = $this->assertSession();

    $this->drupalLogin($this->createUser());

    $this->visit("/admin/config/services/os-twitter-pull");
    $web_assert->statusCodeEquals(403);
  }

}

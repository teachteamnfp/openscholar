<?php

namespace Drupal\Tests\os_privacy_policy\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Tests os_privacy_policy module render footer.
 *
 * @group functional
 * @group os
 */
class PrivacyPolicyRegionRenderTest extends OsExistingSiteTestBase {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    /** @var \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager */
    $vsite_context_manager = $this->container->get('vsite.context_manager');
    $vsite_context_manager->activateVsite($this->group);
    $this->config = $this->container->get('config.factory');
  }

  /**
   * Test block render.
   */
  public function testBlockFooterRender() {
    $web_assert = $this->assertSession();
    $config = $this->config->getEditable('os_privacy_policy.settings');
    $config->set('os_privacy_policy_text', 'Privacy<script>');
    $config->set('os_privacy_policy_url', 'https://maps.google.com\'"+!%/=()$ß*>;~');
    $config->save(TRUE);

    $this->visitViaVsite('', $this->group);
    $web_assert->statusCodeEquals(200);
    $web_assert->pageTextContains('Privacy&lt;script&gt;');
    $page_content = $this->getCurrentPageContent();
    $this->assertContains('https://maps.google.com&amp;#039;&amp;quot;+!%/=()$ß*&amp;gt;;~', $page_content);
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    $this->config->getEditable('os_privacy_policy.settings')->delete();
    parent::tearDown();
  }

}

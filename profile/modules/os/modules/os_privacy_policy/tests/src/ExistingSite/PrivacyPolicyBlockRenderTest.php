<?php

namespace Drupal\Tests\os_privacy_policy\ExistingSite;

use Drupal\block\BlockViewBuilder;
use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Tests os_privacy_policy module render footer.
 *
 * @group kernel
 * @group os
 */
class PrivacyPolicyBlockRenderTest extends OsExistingSiteTestBase {

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
   * Test block render with values.
   */
  public function testBlockRenderWithValues() {
    $config = $this->config->getEditable('os_privacy_policy.settings');
    $config->set('os_privacy_policy_text', 'Privacy link');
    $config->set('os_privacy_policy_url', 'https://theopenscholar.com/');
    $config->save(TRUE);

    $build = BlockViewBuilder::lazyBuilder('copyright', 'full');
    $html = $this->container->get('renderer')->renderRoot($build);
    $this->assertContains('Privacy link', $html->__toString());
    $this->assertContains('https://theopenscholar.com/', $html->__toString());
  }

  /**
   * Test block render without values.
   */
  public function testBlockRenderWithoutValues() {
    $config = $this->config->getEditable('os_privacy_policy.settings');
    $config->set('os_privacy_policy_text', 'Privacy link');
    $config->set('os_privacy_policy_url', '');
    $config->save(TRUE);

    $build = BlockViewBuilder::lazyBuilder('copyright', 'full');
    $html = $this->container->get('renderer')->renderRoot($build);
    $this->assertNotContains('Privacy link', $html->__toString());
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    $this->config->getEditable('os_privacy_policy.settings')->delete();
    parent::tearDown();
  }

}

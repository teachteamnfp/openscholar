<?php

namespace Drupal\Tests\os_privacy_policy\ExistingSite;

use Drupal\Core\Form\FormState;
use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Tests os_privacy_policy module cp settings form.
 *
 * @group kernel
 * @group os
 */
class PrivacyPolicySettingsFormTest extends OsExistingSiteTestBase {

  /**
   * Cp setting form.
   *
   * @var \Drupal\os_privacy_policy\Plugin\CpSetting\OsPrivacyPolicy
   */
  protected $settingPlugin;

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
    /** @var \Drupal\cp_settings\Plugin\CpSettingsManagerInterface $cp_settings_manager */
    $cp_settings_manager = $this->container->get('cp_settings.manager');
    /** @var \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager */
    $vsite_context_manager = $this->container->get('vsite.context_manager');
    $vsite_context_manager->activateVsite($this->group);
    $this->settingPlugin = $cp_settings_manager->createInstance('os_privacy_policy_setting');
    $this->config = $this->container->get('config.factory');
  }

  /**
   * Test form render.
   */
  public function testFormRender() {
    $form = [];
    $this->settingPlugin->getForm($form, $this->config);
    $this->assertArrayHasKey('os_privacy_policy_text', $form);
    $this->assertArrayHasKey('os_privacy_policy_url', $form);
    // Check default values.
    $this->assertEquals('Privacy', $form['os_privacy_policy_text']['#default_value']);
    $this->assertEquals('', $form['os_privacy_policy_url']['#default_value']);
  }

  /**
   * Test form submit file.
   */
  public function testFormSubmit() {
    $form_state = (new FormState())
      ->setValues([
        'os_privacy_policy_text' => 'My Privacy text',
        'os_privacy_policy_url' => 'http://example.com/my-privacy-url',
      ]);
    $this->settingPlugin->submitForm($form_state, $this->config);
    $this->assertEquals(count($form_state->getErrors()), 0);
    $config = $this->config->get('os_privacy_policy.settings');
    $this->assertEquals('My Privacy text', $config->get('os_privacy_policy_text'));
    $this->assertEquals('http://example.com/my-privacy-url', $config->get('os_privacy_policy_url'));
  }

}

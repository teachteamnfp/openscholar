<?php

namespace Drupal\Tests\os_profiles\ExistingSite;

use Drupal\Core\Form\FormState;
use Drupal\cp_settings\Form\CpSettingsForm;
use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Tests os_profiles module cp settings form.
 *
 * @group kernel
 * @group profiles
 */
class ProfilesSettingsFormBuildTest extends OsExistingSiteTestBase {

  /**
   * Cp setting form.
   *
   * @var \Drupal\os_profiles\Plugin\CpSetting\ProfilesSetting
   */
  protected $profileSettings;

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
    /** @var \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsiteContextManager */
    $vsiteContextManager = $this->container->get('vsite.context_manager');
    $vsiteContextManager->activateVsite($this->group);
    $this->profileSettings = $cp_settings_manager->createInstance('profiles_setting');
    $this->config = $this->container->get('config.factory');
  }

  /**
   * Test form render.
   */
  public function testFormRender() {
    $form = [];
    $this->profileSettings->getForm($form, $this->config);
    $this->assertArrayHasKey('display_type', $form);
    $this->assertArrayHasKey('default_image', $form);
    // Check display_type options.
    $this->assertArrayHasKey('no_image_teaser', $form['display_type']['#options']);
    $this->assertArrayHasKey('sidebar_teaser', $form['display_type']['#options']);
    $this->assertArrayHasKey('slide_teaser', $form['display_type']['#options']);
    $this->assertArrayHasKey('teaser', $form['display_type']['#options']);
    $this->assertArrayHasKey('title', $form['display_type']['#options']);
    // Check default_image container.
    $this->assertNotEmpty($form['default_image']['disable_default_image']);
    $this->assertNotEmpty($form['default_image']['default_image_fid']);
    $this->assertEquals('public://' . $this->group->id() . '/files', $form['default_image']['default_image_fid']['#upload_location']);

  }

  /**
   * Test form render with file.
   */
  public function testFormRenderWithFile() {
    $file = $this->createFile('image');
    $profiles_config = $this->config->getEditable('os_profiles.settings');
    $profiles_config->set('default_image_fid', $file->id());
    $profiles_config->save();
    $form = [];
    $this->profileSettings->getForm($form, $this->config);
    $this->assertArrayHasKey('default_image', $form);
    $this->assertEquals($file->id(), $form['default_image']['default_image_fid']['#default_value'][0]);
    $this->assertNotEmpty($form['default_image']['image_crop']);
    $this->assertEquals('image_crop', $form['default_image']['image_crop']['#type']);
    $this->assertEquals($file->id(), $form['default_image']['image_crop']['#file']->id());
  }

  /**
   * Test form submit file.
   */
  public function testFormSubmitFile() {
    $file = $this->createFile('image');
    $form_state = (new FormState())
      ->setValues([
        'display_type' => 'sidebar_teaser',
        'disable_default_image' => TRUE,
        'default_image_fid' => [
          $file->id(),
        ],
      ]);
    $form = [];
    $settings_form = new CpSettingsForm($this->config, $this->container->get('cp_settings.manager'));
    $settings_form->setSettingGroup('profiles');
    $form_state->setFormObject($settings_form);
    $form = $settings_form->buildForm($form, $form_state);
    $settings_form->submitForm($form, $form_state);
    $profiles_config = $this->config->get('os_profiles.settings');
    $this->assertEquals(count($form_state->getErrors()), 0);
    $this->assertEquals($file->id(), $profiles_config->get('default_image_fid'));
    $this->assertEquals('sidebar_teaser', $profiles_config->get('display_type'));
    $this->assertTrue($profiles_config->get('disable_default_image'));
  }

}

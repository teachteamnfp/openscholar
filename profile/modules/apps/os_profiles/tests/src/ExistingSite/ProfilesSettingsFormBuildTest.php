<?php

namespace Drupal\Tests\os_profiles\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Tests os_profiles module cp settings form.
 *
 * @group kernel
 * @group profiles
 */
class ProfilesSettingsFormBuildTest extends OsExistingSiteTestBase {

  /**
   * Reference Preview Form.
   *
   * @var \Drupal\os_profiles\Plugin\CpSetting\ProfilesSetting
   */
  protected $profileSettings;

  /**
   * Form Builder Interface.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface*/
  protected $formBuilder;

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
    $this->formBuilder = $this->container->get('form_builder');
  }

  /**
   * Test form render.
   */
  public function testFormRender() {
    $form = [];
    $this->profileSettings->getForm($form, $this->container->get('config.factory'));
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

}

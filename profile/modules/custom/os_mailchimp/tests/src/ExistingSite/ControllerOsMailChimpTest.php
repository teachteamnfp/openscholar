<?php

namespace Drupal\Tests\os_mailchimp\ExistingSite;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Tests os_mailchimp module.
 *
 * @group mailchimp
 * @group kernel
 */
class ControllerOsMailChimpTest extends ExistingSiteBase {

  /**
   * Logged in user.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $config = \Drupal::configFactory()->getEditable('mailchimp.settings');
    $config->set('api_key', 'test1234');
    $config->save(TRUE);

    $cache = \Drupal::cache('mailchimp');
    $module_path = drupal_get_path('module', 'os_mailchimp');
    // Cache lists data.
    $cache_data = file_get_contents($module_path . '/tests/src/ExistingSite/data/test-lists-data.cache');
    $cache_array = unserialize($cache_data);
    $cache->set('lists', $cache_array);

    // Cache mergevars data.
    $cache_data = file_get_contents($module_path . '/tests/src/ExistingSite/data/test-71c9946c74-mergevars-data.cache');
    $cache_array = unserialize($cache_data);
    $cache->set('71c9946c74-mergevars', $cache_array);

    $this->user = $this->createUser();
  }

  /**
   * Tests os_mailchimp modal popup form elements.
   */
  public function testModalPopupFormElements() {
    $source = $this->drupalGet('/os-mailchimp/subscribe/71c9946c74');
    $this->assertSession()->statusCodeEquals(200);
    $rendered_array = json_decode($source);
    $form_data_output = $rendered_array[3]->data;
    $this->assertContains('os_mailchimp_modal_signup_form', $form_data_output, 'Form id not found.');
    $this->assertContains('Email Address', $form_data_output, 'Email address label not found.');
    $this->assertContains('First Name', $form_data_output, 'First Name label not found.');
    $this->assertContains('Last Name', $form_data_output, 'Last Name label not found.');
  }

  /**
   * Tests os_mailchimp subscribe default value.
   */
  public function testModalPopupFormDefaultValue() {
    $this->drupalLogin($this->user);
    $source = $this->drupalGet('/os-mailchimp/subscribe/71c9946c74');
    $this->assertSession()->statusCodeEquals(200);
    $rendered_array = json_decode($source);
    $form_data_output = $rendered_array[3]->data;
    $email = $this->user->getEmail();
    $this->assertContains($email, $form_data_output, 'Users email not found.');
  }

}

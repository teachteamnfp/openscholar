<?php

namespace Drupal\os_google_analytics\Plugin\CpSetting;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\cp_settings\CpSettingInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * CP setting.
 *
 * @CpSetting(
 *   id = "google_analytics",
 *   title = @Translation("Google Analytics Setting Form"),
 *   group = {
 *    "id" = "analytics",
 *    "title" = @Translation("Google Analytics"),
 *    "parent" = "cp.settings.global"
 *   }
 * )
 */
class GaSettingForm extends PluginBase implements CpSettingInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() : array {
    return [
      'os_ga.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$form, ConfigFactoryInterface $configFactory) {
    $config_ga = $configFactory->get('os_ga.settings');

    $description = $this->t('This ID is unique to each site you want 
       to track separately, and is in the form of UA-xxxxxxx-yy. To get a Web Property 
       ID, <a href="@analytics">register your site with Google Analytics</a>, or if you
       already have registered your site, go to your Google Analytics Settings page to
       see the ID next to every site profile. <a href="@webpropertyid">Find more 
       information in the documentation</a>.', [
         '@analytics' => 'https://marketingplatform.google.com/about/analytics/'
         , '@webpropertyid' => Url::fromUri('https://developers.google.com/analytics/resources/concepts/gaConceptsAccounts', ['fragment' => 'webProperty'])->toString(),
       ]);

    $form['title'] = [
      '#type' => 'page_title',
      '#title' => $this->t('Google Analytics'),
    ];

    $form['web_property_id'] = [
      '#default_value' => $config_ga->get('web_property_id'),
      '#description' => $description,
      '#maxlength' => 20,
      '#placeholder' => 'UA-',
      '#required' => TRUE,
      '#size' => 20,
      '#title' => $this->t('Web Property ID'),
      '#type' => 'textfield',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    // Replace all type of dashes (n-dash, m-dash, minus) with normal dashes.
    $form_state->setValue('web_property_id', str_replace([
      '–',
      '—',
      '−',
    ], '-', $form_state->getValue('web_property_id')));

    if (!preg_match('/^UA-\d+-\d+$/', $form_state->getValue('web_property_id'))) {
      $form_state->setErrorByName('web_property_id', $this->t('A valid Google Analytics Web Property ID is case sensitive and formatted like UA-xxxxxxx-yy.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(FormStateInterface $formState, ConfigFactoryInterface $configFactory) {
    $config_ga = $configFactory->getEditable('os_ga.settings');
    $config_ga
      ->set('web_property_id', $formState->getValue('web_property_id'))
      ->save();
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account): AccessResultInterface {
    if (!$account->hasPermission('access control panel')) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }

}

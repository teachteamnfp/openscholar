<?php

namespace Drupal\os_privacy_policy\Plugin\CpSetting;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cp_settings\CpSettingBase;

/**
 * CP setting for privacy policy.
 *
 * @CpSetting(
 *   id = "os_privacy_policy_setting",
 *   title = @Translation("Vsite Privacy Setting Form"),
 *   group = {
 *    "id" = "privacy_policy",
 *    "title" = @Translation("Privacy Policy"),
 *    "parent" = "cp.settings.global"
 *   }
 * )
 */
class OsPrivacyPolicy extends CpSettingBase {

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() : array {
    return ['os_privacy_policy.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$form, ConfigFactoryInterface $configFactory) {
    $config = $configFactory->get('os_privacy_policy.settings');
    $form['os_privacy_policy_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Privacy policy text'),
      '#default_value' => $config->get('os_privacy_policy_text'),
    ];
    $form['os_privacy_policy_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Privacy policy url'),
      '#default_value' => $config->get('os_privacy_policy_url'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(FormStateInterface $form_state, ConfigFactoryInterface $config_factory) {
    $config = $config_factory->getEditable('os_privacy_policy.settings');
    $config->set('os_privacy_policy_text', $form_state->getValue('os_privacy_policy_text'));
    $config->set('os_privacy_policy_url', $form_state->getValue('os_privacy_policy_url'));
    $config->save(TRUE);
  }

}

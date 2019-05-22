<?php

namespace Drupal\vsite\Plugin\CpSetting;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cp_settings\CpSettingBase;

/**
 * CP domain settings.
 *
 * @CpSetting(
 *   id = "os_domain_setting",
 *   title = @Translation("Domain Setting"),
 *   group = {
 *    "id" = "vsite_domain",
 *    "title" = @Translation("Custom Domain"),
 *    "parent" = "cp.settings.global"
 *   }
 * )
 */
class DomainSetting extends CpSettingBase {

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames(): array {
    // Not stored in config.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$form, ConfigFactoryInterface $configFactory) {
    if ($group = \Drupal::service('vsite.context_manager')->getActiveVsite()) {

      $domain = $group->get('field_domain')->getValue();
      $form['domain'] = [
        '#type' => 'textfield',
        '#title' => t('Domain Name'),
        '#default_value' => empty($domain[0]['value']) ? '' : $domain[0]['value'],
      ];
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(FormStateInterface $formState, ConfigFactoryInterface $configFactory) {
    $group = \Drupal::service('vsite.context_manager')->getActiveVsite();
    $domain = [
      ['value' => $formState->getValue('domain')],
    ];

    $group->set('field_domain', $domain);
    $group->save();

  }

}

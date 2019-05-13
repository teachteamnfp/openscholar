<?php

namespace Drupal\os_redirect\Plugin\CpSetting;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\cp_settings\CpSettingBase;

/**
 * CP redirect setting.
 *
 * @CpSetting(
 *   id = "os_redirect_maximum_setting",
 *   title = @Translation("OS redirect maximum"),
 *   group = {
 *    "id" = "redirect_maximum",
 *    "title" = @Translation("Maximum Redirects"),
 *    "parent" = "cp.settings.global"
 *   }
 * )
 */
class OsRedirectMaximumSetting extends CpSettingBase {

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames(): array {
    return ['os_redirect.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$form, ConfigFactoryInterface $configFactory) {
    $config = $configFactory->get('os_redirect.settings');
    $form['maximum_number'] = [
      '#type' => 'textfield',
      '#size' => 10,
      '#title' => t('Maximum number of redirects'),
      '#default_value' => $config->get('maximum_number'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(FormStateInterface $formState, ConfigFactoryInterface $configFactory) {
    $config = $configFactory->getEditable('os_redirect.settings');
    $config->set('maximum_number', $formState->getValue('maximum_number'));
    $config->save(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account): AccessResultInterface {
    /** @var \Drupal\Core\Access\AccessResultInterface $access_result */
    $access_result = parent::access($account);

    if ($access_result->isForbidden()) {
      return $access_result;
    }

    if (!$this->activeVsite->hasPermission('administer control panel redirect_maximum', $account)) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }

}

<?php

namespace Drupal\vsite_domain\Plugin\CpSetting;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\cp_settings\CpSettingBase;

/**
 * Vsite domain setting.
 *
 * @CpSetting(
 *   id = "domain",
 *   title = @Translation("Vsite Domain Setting"),
 *   group = {
 *    "id" = "domain",
 *    "title" = @Translation("Custom Domain"),
 *    "parent" = "cp.settings.global"
 *   }
 * )
 */
class VsiteDomainSetting extends CpSettingBase {

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames(): array {
    // Not yet implemented.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$form, ConfigFactoryInterface $configFactory) {
    $form['todo'] = [
      '#markup' => 'this is not yet implemented',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(FormStateInterface $formState, ConfigFactoryInterface $configFactory) {}

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account): AccessResultInterface {
    /** @var \Drupal\Core\Access\AccessResultInterface $parent_access */
    $parent_access = parent::access($account);
    $setting_access = AccessResult::neutral();

    if (!$this->activeVsite->hasPermission('change vsite domain', $account)) {
      $setting_access = AccessResult::forbidden();
    }

    return $parent_access->orIf($setting_access);
  }

}

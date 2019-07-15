<?php

namespace Drupal\os_search_solr\Plugin\CpSetting;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\cp_settings\CpSettingBase;

/**
 * OpenScholar: Solr Search setting.
 *
 * @CpSetting(
 *   id = "solr_search",
 *   title = @Translation("OpenScholar Solr Search Setting"),
 *   group = {
 *    "id" = "solr_search",
 *    "title" = @Translation("Cache / Reindex"),
 *    "parent" = "cp.settings.global"
 *   }
 * )
 */
class OsSearchSetting extends CpSettingBase {

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames(): array {
    // This is not yet implemented.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$form, ConfigFactoryInterface $configFactory): void {
    $form['label'] = [
      '#markup' => $this->t('Re-index this site'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(FormStateInterface $formState, ConfigFactoryInterface $configFactory): void {
    // Not yet implemented.
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account): AccessResultInterface {
    /** @var \Drupal\Core\Access\AccessResultInterface $parent_access */
    $parent_access = parent::access($account);
    $setting_access = AccessResult::neutral();

    if (!$this->activeVsite->hasPermission('manage vsite solr search', $account)) {
      $setting_access = AccessResult::forbidden();
    }

    return $parent_access->orIf($setting_access);
  }

}

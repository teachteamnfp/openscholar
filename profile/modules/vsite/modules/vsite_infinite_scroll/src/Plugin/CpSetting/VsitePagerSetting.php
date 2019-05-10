<?php

namespace Drupal\vsite_infinite_scroll\Plugin\CpSetting;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\cp_settings\CpSettingInterface;

/**
 * CP metatag setting.
 *
 * @CpSetting(
 *   id = "vsite_pager_setting",
 *   title = @Translation("Vsite pager"),
 *   group = {
 *    "id" = "vsite",
 *    "title" = @Translation("Vsite"),
 *    "parent" = "cp.settings.global"
 *   }
 * )
 */
class VsitePagerSetting extends PluginBase implements CpSettingInterface {

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames(): array {
    return ['vsite_infinite_scroll.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$form, ConfigFactoryInterface $configFactory) {
    $config = $configFactory->get('vsite_infinite_scroll.settings');

    $form['long_list_content_pagination'] = [
      '#type' => 'radios',
      '#title' => t('Choose how long lists of content will display'),
      '#options' => [
        'infinite_scroll' => 'Infinite scrolling',
        'pager' => 'Pagination',
      ],
      '#default_value' => $config->get('long_list_content_pagination'),
      '#description' => t('Pagination applies only to Blog, Links, News, FAQs, Publications and Profiles.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $formState) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(FormStateInterface $formState, ConfigFactoryInterface $configFactory) {
    $config = $configFactory->getEditable('vsite_infinite_scroll.settings');
    $config->set('long_list_content_pagination', $formState->getValue('long_list_content_pagination'));
    $config->save(TRUE);

    Cache::invalidateTags([
      _vsite_infinite_scroll_get_cache_tag(),
    ]);
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

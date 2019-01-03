<?php

namespace Drupal\vsite\Plugin\CpSetting;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\cp_settings\CpSettingInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * CP setting.
 *
 * @CpSetting(
 *   id = "test_setting",
 *   title = @Translation("Test"),
 *   group = {
 *    "id" = "test",
 *    "title" = @Translation("Testbed"),
 *    "parent" = "cp.settings"
 *   }
 * )
 */
class VsiteTestSetting extends PluginBase implements CpSettingInterface {

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames(): array {
    return ['vsite.example'];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$form, ConfigFactoryInterface $configFactory) {
    $config = $configFactory->get('vsite.example');
    $form['vsite_example_text'] = [
      '#type' => 'textfield',
      '#title' => t('Example CP Setting'),
      '#default_value' => $config->get('text'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(FormStateInterface $formState, ConfigFactoryInterface $configFactory) {
    $config = $configFactory->getEditable('vsite.example');
    $config->set('text', $formState->getValue('vsite_example_text'));
    $config->save(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account): AccessResultInterface {
    return AccessResult::allowed();
  }

}

<?php

namespace Drupal\os_breadcrumb\Plugin\CpSetting;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cp_settings\CpSettingBase;

/**
 * CP breadcrumb setting.
 *
 * @CpSetting(
 *   id = "os_breadcrumb_setting",
 *   title = @Translation("OS Breadcrumb"),
 *   group = {
 *    "id" = "breadcrumb",
 *    "title" = @Translation("Breadcrumbs"),
 *    "parent" = "cp.appearance"
 *   }
 * )
 */
class OsBreadcrumbSetting extends CpSettingBase {

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames(): array {
    return ['os_breadcrumb.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$form, ConfigFactoryInterface $configFactory) {
    $config = $configFactory->get('os_breadcrumb.settings');
    $form['#theme'] = 'os_breadcrumb_settings_form';
    $form['show_breadcrumbs'] = [
      '#type' => 'checkbox',
      '#title' => t('Show breadcrumbs on my site'),
      '#default_value' => $config->get('show_breadcrumbs'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(FormStateInterface $formState, ConfigFactoryInterface $configFactory) {
    $config = $configFactory->getEditable('os_breadcrumb.settings');
    $config->set('show_breadcrumbs', $formState->getValue('show_breadcrumbs'));
    $config->save(TRUE);
  }

}

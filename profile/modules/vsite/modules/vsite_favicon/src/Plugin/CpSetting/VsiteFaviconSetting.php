<?php

namespace Drupal\vsite_favicon\Plugin\CpSetting;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cp_settings\CpSettingBase;
use Drupal\file\Entity\File;

/**
 * CP Vsite favicon setting.
 *
 * @CpSetting(
 *   id = "vsite_favicon_setting",
 *   title = @Translation("Vsite favicon"),
 *   group = {
 *    "id" = "favicon",
 *    "title" = @Translation("Favicon"),
 *    "parent" = "cp.appearance"
 *   }
 * )
 */
class VsiteFaviconSetting extends CpSettingBase {

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames(): array {
    return ['vsite.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$form, ConfigFactoryInterface $configFactory) {
    $config = $configFactory->get('vsite.settings');
    $upload_location = 'public://' . $this->activeVsite->id() . '/favicon';
    $form['favicon_fid'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Favicon'),
      '#description' => $this->t('A 16x16 .png file to be displayed in browser shortcut icons and tabs for any page on your site. For help generating a favicon file, try <a href="http://www.favicon.cc/" target="_blank">http://www.favicon.cc/</a>.'),
      '#upload_location' => $upload_location,
      '#upload_validators' => [
        'file_validate_extensions' => ['png'],
        'file_validate_size' => [2 * 1024],
        'file_validate_image_resolution' => ['16x16'],
      ],
    ];
    if ($default_fid = $config->get('favicon_fid')) {
      $form['favicon_fid']['#default_value'] = [$default_fid];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(FormStateInterface $form_state, ConfigFactoryInterface $configFactory) {
    $config = $configFactory->getEditable('vsite.settings');
    $form_file = $form_state->getValue('favicon_fid', '0');
    if (!empty($form_file[0])) {
      $file = File::load($form_file[0]);
      $file->setPermanent();
      $file->save();
      $config->set('favicon_fid', $file->id());
      $config->save(TRUE);
    }
    else {
      // Checking is there any exists file and delete.
      if ($exists_fid = $config->get('favicon_fid')) {
        File::load($exists_fid)->delete();
      }
      $config->set('favicon_fid', NULL);
      $config->save(TRUE);
    }
  }

}

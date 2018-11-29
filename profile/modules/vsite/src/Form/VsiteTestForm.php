<?php

namespace Drupal\vsite\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 *  Manually test vsite-level config storage functions
 */
class VsiteTestForm extends ConfigFormBase {

  /**
   * @inheritdoc
   */
  public function getFormId() {
    return 'vsite_test';
  }

  /**
   * @inheritdoc
   */
  protected function getEditableConfigNames() {
    return ['vsite.test_settings'];
  }

  /**
   * @inheritdoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('vsite.test_settings');
    $form['checkbox'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Just testing'),
      '#description' => $this->t("I just wanna see if this works"),
      '#default_value' => $config->get('checkbox'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('vsite.test_settings')
      // Remove unchecked types.
      ->set('checkbox', $form_state->getValue('checkbox'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}

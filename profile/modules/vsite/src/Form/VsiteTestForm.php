<?php
/**
 * Created by PhpStorm.
 * User: New User
 * Date: 8/23/2018
 * Time: 3:23 PM
 */

namespace Drupal\vsite\Form;


use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class VsiteTestForm extends ConfigFormBase {

  /**
   * @inheritDoc
   */
  public function getFormId () {
    return 'vsite_test';
  }

  /**
   * @inheritDoc
   */
  protected function getEditableConfigNames () {
    return [ 'vsite.test_settings' ];
  }

  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm ($form, $form_state);

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
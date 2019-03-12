<?php

namespace Drupal\os_publications\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * API Configuration form.
 */
class CitationDashConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['dash.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'citation_dash_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('dash.settings');

    $form['dash'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Configure DASH'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];

    $form['dash']['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('DASH username'),
      '#description' => $this->t('Desposits to DASH will be made via this user account.'),
      '#default_value' => $config->get('dash_username'),
    ];

    $form['dash']['password'] = [
      '#type' => 'textfield',
      '#title' => $this->t('DASH password'),
      '#default_value' => $config->get('dash_password'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->config('dash.settings')
      ->set('dash_username', $form_state->getValue('username'))
      ->set('dash_password', $form_state->getValue('password'))
      ->save();
  }

}

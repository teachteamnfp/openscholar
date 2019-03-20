<?php

namespace Drupal\os_twitter_pull\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * {@inheritdoc}
 */
class OsTwitterPullAdminSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['os_twitter_pull.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'os_twitter_pull_admin_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('os_twitter_pull.settings');

    $form['consumer_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Consumer key'),
      '#required' => TRUE,
      '#default_value' => $config->get('consumer_key'),
    ];

    $form['consumer_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Consumer secret'),
      '#required' => TRUE,
      '#default_value' => $config->get('consumer_secret'),
    ];

    $form['oauth_access_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Oauth access token'),
      '#required' => TRUE,
      '#default_value' => $config->get('oauth_access_token'),
    ];

    $form['oauth_access_token_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Oauth access token secret'),
      '#required' => TRUE,
      '#default_value' => $config->get('oauth_access_token_secret'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = $this->config('os_twitter_pull.settings');

    $form_state->cleanValues();
    foreach ($form_state->getValues() as $key => $value) {
      $config->set($key, $value);
    }
    $config->save();

    parent::submitForm($form, $form_state);
  }

}

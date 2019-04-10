<?php

namespace Drupal\cp_appearance\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form to select the theme.
 *
 * @internal
 */
class ThemeForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cp_appearance_theme_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['system.theme'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, array $theme_options = NULL) {
    // Administration theme settings.
    $form['cp_theme'] = [
      '#type' => 'details',
      '#title' => $this->t('Set theme'),
      '#open' => TRUE,
    ];
    $form['cp_theme']['theme'] = [
      '#type' => 'select',
      '#options' => $theme_options,
      '#title' => $this->t('Select theme'),
      '#description' => $this->t('Choose the theme to use for this vsite.'),
      '#default_value' => $this->config('system.theme')->get('default'),
    ];
    $form['cp_theme']['actions'] = ['#type' => 'actions'];
    $form['cp_theme']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save Theme'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->config('system.theme')->set('default', $form_state->getValue('theme'))->save();
  }

}

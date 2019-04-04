<?php

namespace Drupal\os_theme_preview\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Preview action form.
 */
class PreviewAction extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'os_theme_preview_preview_action';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['name'] = [
      '#markup' => 'it is working',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // TODO: Implement submitForm() method.
  }

}

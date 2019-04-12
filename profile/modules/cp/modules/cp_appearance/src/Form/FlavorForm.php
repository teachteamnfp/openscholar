<?php

namespace Drupal\cp_appearance\Form;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Flavor selection form.
 */
class FlavorForm implements FormInterface {

  use StringTranslationTrait;

  /**
   * The theme for which the form will be created.
   *
   * @var string
   */
  protected $theme;

  /**
   * Creates a new FlavorForm object.
   *
   * @param string $theme
   *   The theme for which the form will be created.
   */
  public function __construct($theme) {
    $this->theme = $theme;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return "cp_appearance_{$this->theme}_flavor_form";
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['options'] = [
      '#type' => 'select',
      '#title' => $this->t('Flavors'),
      '#options' => [
        'golden_accents' => 'Golden Accents',
        'shade' => 'shade',
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {}

}

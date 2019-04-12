<?php

namespace Drupal\cp_appearance\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use PHPUnit\Framework\Assert;

/**
 * Flavor selection form.
 */
class FlavorForm implements FormInterface {

  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * The theme for which the form will be created.
   *
   * @var string
   */
  protected $theme;

  /**
   * Available flavors of the theme.
   *
   * An associative array of flavor machine name and human readable name.
   *
   * @var array
   */
  protected $flavors;

  /**
   * Creates a new FlavorForm object.
   *
   * @param string $theme
   *   The theme for which the form will be created.
   * @param array $flavors
   *   Available flavors of the theme.
   */
  public function __construct($theme, array $flavors) {
    Assert::assertNotEquals('', $theme);
    Assert::assertNotEmpty($flavors);

    $this->theme = $theme;
    $this->flavors = $flavors;
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
      '#options' => ['_none' => $this->t('None')] + $this->flavors,
      '#ajax' => [
        'callback' => '::feedbackMessage',
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

  /**
   * Flavor option change handler.
   *
   * @ingroup forms
   */
  public function feedbackMessage(array &$form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();
    $test['#markup'] = $form_state->getValue('options');

    $response->addCommand(new OpenModalDialogCommand('Flavor selected', $test));

    return $response;
  }

}

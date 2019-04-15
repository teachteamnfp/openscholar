<?php

namespace Drupal\cp_appearance\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Ds\Map;
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
   * Flavor name and its information mapping.
   *
   * @var \Ds\Map
   */
  protected $flavors;

  /**
   * Creates a new FlavorForm object.
   *
   * @param string $theme
   *   The theme for which the form will be created.
   * @param \Ds\Map $flavors
   *   Available flavors of the theme.
   */
  public function __construct($theme, Map $flavors) {
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
    $options = [
      '_none' => $this->t('None'),
    ];

    /** @var \Drupal\Core\Extension\Extension $flavor */
    foreach ($this->flavors->values() as $flavor) {
      $options[$flavor->getName()] = $flavor->info['name'];
    }

    $form['options'] = [
      '#type' => 'select',
      '#title' => $this->t('Flavors'),
      '#options' => $options,
      '#ajax' => [
        'callback' => '::updatePreview',
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

  /**
   * Flavor option change handler.
   *
   * Updates preview based on the selection.
   *
   * @ingroup forms
   */
  public function updatePreview(array &$form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();
    /** @var string $selection */
    $selection = $form_state->getValue('options');

    if ($selection !== '_none') {
      /** @var \Drupal\Core\Extension\Extension $flavor */
      $flavor = $this->flavors->get($selection);
      /** @var array $info */
      $info = $flavor->info;

      if (isset($info['screenshot'])) {
        $response->addCommand(new ReplaceCommand('#theme-selector-vibrant .theme-screenshot img', [
          '#theme' => 'image',
          '#uri' => $info['screenshot'],
          '#alt' => $this->t('Screenshot for @theme theme', ['@theme' => $info['name']]),
          '#title' => $this->t('Screenshot for @theme theme', ['@theme' => $info['name']]),
          '#attributes' => ['class' => ['screenshot']],
        ]));
      }
    }

    return $response;
  }

}

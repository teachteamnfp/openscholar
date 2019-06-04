<?php

namespace Drupal\cp_menu\Form\Multistep\Step;

/**
 * Class StepOne.
 *
 * @package Drupal\ms_ajax_form_example\Step
 */
class StepOne extends BaseStep {

  const STEP_ONE = 1;

  /**
   * {@inheritdoc}
   */
  protected function setStep() {
    return self::STEP_ONE;
  }

  /**
   * {@inheritdoc}
   */
  public function buildStepFormElements() {

    $form['link_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Item Type'),
      '#options' => [
        'post' => $this->t('Post'),
        'url' => $this->t('URL'),
        'home' => $this->t('Home'),
        'menu_heading' => $this->t('Menu Heading'),
      ],
      '#default_value' => $this->getValues()['link_type'] ?? '',
      '#description' => $this->t('Select heading or type of content to link to.'),
      '#required' => TRUE,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Continue'),
      '#ajax' => [
        'callback' => [$this, 'loadStep'],
        'event' => 'click',
      ],
    ];

    return $form;
  }

}

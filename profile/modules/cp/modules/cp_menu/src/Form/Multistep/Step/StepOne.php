<?php

namespace Drupal\cp_menu\Form\Multistep\Step;

/**
 * Class StepOne.
 *
 * @package Drupal\cp_menu\Form\Multistep\Step
 */
class StepOne extends BaseStep {

  /**
   * Step number.
   */
  const STEP_ONE = 1;

  /**
   * {@inheritdoc}
   */
  protected function setStep() : int {
    return self::STEP_ONE;
  }

  /**
   * {@inheritdoc}
   */
  public function buildStepFormElements() : array {

    $form['link_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Item Type'),
      '#options' => [
        'url' => $this->t('URL'),
        'home' => $this->t('Home'),
        'menu_heading' => $this->t('Menu Heading'),
      ],
      '#default_value' => $this->store->get('link_type') ?? 'url',
      '#description' => $this->t('Select heading or type of content to link to.'),
      '#required' => TRUE,
    ];
    return $form;
  }

}

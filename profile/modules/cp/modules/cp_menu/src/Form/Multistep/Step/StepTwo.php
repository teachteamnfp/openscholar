<?php

namespace Drupal\cp_menu\Form\Multistep\Step;

/**
 * Class StepTwo.
 *
 * @package Drupal\cp_menu\Form\Multistep\Step
 */
class StepTwo extends BaseStep {

  /**
   * Step number.
   */
  const STEP_TWO = 2;

  /**
   * {@inheritdoc}
   */
  protected function setStep() : int {
    return self::STEP_TWO;
  }

  /**
   * {@inheritdoc}
   */
  public function buildStepFormElements() : array {

    // Can this be prepopulated if we're coming from an existing node?
    $form['title'] = [
      '#title' => t('Title'),
      '#type' => 'textfield',
      '#description' => t('Name your link or heading.'),
      '#required' => TRUE,
    ];
    // Adds type dependent fields.
    switch ($this->store->get('link_type')) {
      case 'url':
        $form['new_node_type'] = [
          '#type' => 'hidden',
          '#value' => 0,
        ];
        $form['url'] = [
          '#type' => 'textfield',
          '#title' => t('URL'),
          '#required' => TRUE,
          '#description' => t('The address of the link.'),
        ];

        // $form['#validate'][] = 'cp_menu_url_validate';.
        break;

      case 'home':
        $form['new_node_type'] = [
          '#type' => 'hidden',
          '#value' => 0,
        ];
        $form['url'] = [
          '#type' => 'hidden',
          '#value' => '<front>',
        ];
        break;
    }
    return $form;
  }

}

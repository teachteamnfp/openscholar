<?php

namespace Drupal\cp_menu\Form\Multistep\Step;

/**
 * Interface StepInterface.
 *
 * @package Drupal\ms_ajax_form_example\Step
 */
interface StepInterface {

  /**
   * Gets the step.
   *
   * @returns step;
   */
  public function getStep();

  /**
   * Returns a renderable form array that defines a step.
   */
  public function buildStepFormElements();

  /**
   * Indicates if step is last step.
   */
  public function isLastStep();

  /**
   * All field validators.
   *
   * @returns array of fields with their validation requirements.
   */
  public function getFieldsValidators();

  /**
   * Sets filled out values of step.
   */
  public function setValues($values);

  /**
   * Gets filled out values of step.
   */
  public function getValues();

}

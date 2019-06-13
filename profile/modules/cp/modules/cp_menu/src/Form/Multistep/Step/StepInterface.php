<?php

namespace Drupal\cp_menu\Form\Multistep\Step;

/**
 * Interface StepInterface.
 *
 * @package Drupal\cp_menu\Form\Multistep\Step
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

}

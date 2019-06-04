<?php

namespace Drupal\cp_menu\Form\Multistep\Manager;

use Drupal\cp_menu\Form\Multistep\Step\StepInterface;

/**
 * Interface for StepManager.
 */
interface StepManagerInterface {

  /**
   * Add a step to the steps property.
   *
   * @param \Drupal\cp_menu\Form\Multistep\Step\StepInterface $step
   *   Step of the form.
   */
  public function addStep(StepInterface $step) : void;

  /**
   * Fetches step from steps property, If it doesn't exist, create step object.
   *
   * @param int $step_id
   *   Step ID.
   *
   * @return \Drupal\cp_menu\Form\Multistep\Step\StepInterface
   *   Return step object.
   */
  public function getStep($step_id) : StepInterface;

}

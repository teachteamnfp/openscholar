<?php

namespace Drupal\cp_menu\Form\Multistep\Manager;

use Drupal\cp_menu\Form\Multistep\Step\StepInterface;

/**
 * Class StepManager.
 *
 * @package Drupal\ms_ajax_form_example\Manager
 */
class StepManager {

  /**
   * Multi steps of the form.
   *
   * @var \Drupal\cp_menu\Form\Multistep\Step\StepInterface
   */
  protected $steps;

  /**
   * StepManager constructor.
   */
  public function __construct() {
  }

  /**
   * Add a step to the steps property.
   *
   * @param \Drupal\cp_menu\Form\Multistep\Step\StepInterface $step
   *   Step of the form.
   */
  public function addStep(StepInterface $step) {
    $this->steps[$step->getStep()] = $step;
  }

  /**
   * Fetches step from steps property, If it doesn't exist, create step object.
   *
   * @param int $step_id
   *   Step ID.
   *
   * @return \Drupal\cp_menu\Form\Multistep\Step\StepInterface
   *   Return step object.
   */
  public function getStep($step_id) {
    if (isset($this->steps[$step_id])) {
      // If step was already initialized, use that step.
      // Chance is there are values stored on that step.
      $step = $this->steps[$step_id];
    }
    else {
      // Get class.
      if ($step_id === 1) {
        $class = 'Drupal\cp_menu\Form\Multistep\Step\StepOne';
      }
      elseif ($step_id === 2) {
        $class = 'Drupal\cp_menu\Form\Multistep\Step\StepTwo';
      }
      // Init step.
      $step = new $class($this);
    }
    return $step;
  }

}

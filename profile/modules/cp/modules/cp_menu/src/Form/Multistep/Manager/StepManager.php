<?php

namespace Drupal\cp_menu\Form\Multistep\Manager;

use Drupal\cp_menu\Form\Multistep\Step\StepInterface;

/**
 * Class StepManager.
 *
 * @package Drupal\ms_ajax_form_example\Manager
 */
class StepManager implements StepManagerInterface {

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
   * {@inheritdoc}
   */
  public function addStep(StepInterface $step) : void {
    $this->steps[$step->getStep()] = $step;
  }

  /**
   * {@inheritdoc}
   */
  public function getStep($step_id) : StepInterface {
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

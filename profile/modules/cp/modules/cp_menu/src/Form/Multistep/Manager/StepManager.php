<?php

namespace Drupal\cp_menu\Form\Multistep\Manager;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\cp_menu\Form\Multistep\Step\StepInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class StepManager.
 *
 * Helps in managing the steps of the form like saving a step.
 *
 * And getting the current step when required.
 *
 * @package Drupal\cp_menu\Form\Multistep\Manager
 */
class StepManager implements StepManagerInterface {
  use DependencySerializationTrait;

  /**
   * Multi steps of the form.
   *
   * @var \Drupal\cp_menu\Form\Multistep\Step\StepInterface
   */
  protected $steps;

  /**
   * StepManager constructor.
   */
  public function __construct(PrivateTempStoreFactory $private_temp_store) {
    $this->privateTempStore = $private_temp_store;

  }

  /**
   * Inject all services we need.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Service container.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private')
    );
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
      $step = new $class($this->privateTempStore, $this);
    }
    return $step;
  }

}

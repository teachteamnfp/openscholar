<?php

namespace Drupal\cp_menu\Form\Multistep\Step;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BaseStep.
 *
 * @package Drupal\cp_menu\Form\Multistep\Step
 */
abstract class BaseStep implements StepInterface {
  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * Multi steps of the form.
   *
   * @var StepInterface
   */
  protected $step;

  /**
   * Private Temp store service.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $privateTempStore;

  /**
   * BaseStep constructor.
   */
  public function __construct(PrivateTempStoreFactory $private_temp_store) {
    $this->privateTempStore = $private_temp_store;
    $this->store = $this->privateTempStore->get('link_data');
    $this->step = $this->setStep();
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
  public function getStep() {
    return $this->step;
  }

  /**
   * {@inheritdoc}
   */
  abstract protected function setStep();

}

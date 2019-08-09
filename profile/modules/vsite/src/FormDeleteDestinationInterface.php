<?php

namespace Drupal\vsite;

use Drupal\Core\Form\FormStateInterface;

/**
 * FormDeleteDestination service.
 */
interface FormDeleteDestinationInterface {

  /**
   * Get redirect/destination mapping by entity type.
   *
   * @return array
   */
  public function getRedirectMapping() : array;

  /**
   * Set form delete button destination.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function setDeleteButtonDestination(array &$form, FormStateInterface $form_state) : void;

}

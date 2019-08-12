<?php

namespace Drupal\vsite;

use Drupal\Core\Form\FormStateInterface;

/**
 * FormDeleteDestination service.
 */
interface FormDeleteDestinationInterface {

  /**
   * Set form delete button destination.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form State Interface.
   */
  public function setDeleteButtonDestination(array &$form, FormStateInterface $form_state) : void;

}

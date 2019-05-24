<?php

namespace Drupal\cp\Plugin\Action\Derivative;

use Drupal\Core\Action\Plugin\Action\Derivative\EntityDeleteActionDeriver;

/**
 * Provides an action deriver that finds entity types with delete form.
 *
 * @see \Drupal\Core\Action\Plugin\Action\DeleteAction
 */
class CpEntityDeleteActionDeriver extends EntityDeleteActionDeriver {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    parent::getDerivativeDefinitions($base_plugin_definition);

    if (!empty($this->derivatives['node'])) {
      $this->derivatives['node']['confirm_form_route_name'] = 'cp.node.multiple_delete_confirm';
    }

    return $this->derivatives;
  }

}

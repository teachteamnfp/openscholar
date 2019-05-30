<?php

namespace Drupal\cp\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\DeleteAction;

/**
 * Redirects to a control panel entity deletion form.
 *
 * @Action(
 *   id = "cp_entity:delete_action",
 *   action_label = @Translation("Delete"),
 *   deriver = "Drupal\cp\Plugin\Action\Derivative\CpEntityDeleteActionDeriver",
 * )
 */
class CpDeleteAction extends DeleteAction {}

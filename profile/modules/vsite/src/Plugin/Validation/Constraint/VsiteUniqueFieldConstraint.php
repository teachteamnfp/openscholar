<?php

namespace Drupal\vsite\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * A constraint plugin to register the vsite unique validator.
 *
 * @Constraint(
 *   id = "VsiteUniqueField",
 *   label = @Translation("Unique to a given vsite")
 * )
 */
class VsiteUniqueFieldConstraint extends Constraint {

  public $message = 'A @entity_type with @field_name %value already exists.';

  /**
   * {@inheritdoc}
   */
  public function validatedBy() {
    return '\Drupal\vsite\Plugin\Validation\Constraint\VsiteUniqueFieldValueValidator';
  }

}

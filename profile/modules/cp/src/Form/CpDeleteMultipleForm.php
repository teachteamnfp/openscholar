<?php

namespace Drupal\cp\Form;

use Drupal\Core\Entity\Form\DeleteMultipleForm;

/**
 * Provides an entities deletion confirmation form.
 */
class CpDeleteMultipleForm extends DeleteMultipleForm {

  /**
   * {@inheritdoc}
   */
  public function getBaseFormId() {
    return 'cp_entity_delete_multiple_confirm_form';
  }

}

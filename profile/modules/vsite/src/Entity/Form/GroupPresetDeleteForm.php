<?php

namespace Drupal\vsite\Entity\Form;

use Drupal\Core\Entity\Entity;
use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Confirm Delete Form.
 */
class GroupPresetDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Delete preset');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return Url::fromRoute('entity.group_preset.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var Entity $entity */
    $entity = $form_state->getFormObject()->getEntity();
    $entity->delete();
  }

}

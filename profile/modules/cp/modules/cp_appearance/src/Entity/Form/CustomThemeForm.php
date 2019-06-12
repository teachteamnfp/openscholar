<?php

namespace Drupal\cp_appearance\Entity\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form handler for CustomTheme add and edit.
 */
class CustomThemeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    /** @var \Drupal\cp_appearance\Entity\CustomThemeInterface $entity */
    $entity = $this->entity;

    $form['label'] = [
      '#title' => $this->t('Custom Theme Name'),
      '#type' => 'textfield',
      '#default_value' => $entity->label(),
      '#required' => TRUE,
      '#size' => 30,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $entity->id(),
      '#machine_name' => [
        'exists' => [$this, 'exist'],
        'source' => ['label'],
      ],
      '#disabled' => !$entity->isNew(),
    ];

    // TODO: Set default_value.
    $form['favicon'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Favicon'),
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
      ],
    ];

    // TODO: Use legit options.
    $form['base_theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Parent Theme'),
      '#default_value' => $entity->getBaseTheme(),
      '#options' => [
        'theme_1' => $this->t('Theme 1'),
        'theme_2' => $this->t('Theme 2'),
      ],
      '#required' => TRUE,
    ];

    // TODO: Set default_value.
    $form['images'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Images'),
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
      ],
    ];

    // TODO: Set default_value.
    $form['styles'] = [
      '#type' => 'textarea',
      '#title' => $this->t('CSS'),
      '#required' => TRUE,
    ];

    // TODO: Set default_value.
    $form['scripts'] = [
      '#type' => 'textarea',
      '#title' => $this->t('JavaScript'),
    ];

    return $form;
  }

}

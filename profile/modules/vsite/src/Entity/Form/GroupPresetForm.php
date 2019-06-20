<?php

namespace Drupal\vsite\Entity\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Class GroupPresetForm.
 *
 * @package Drupal\vsite\Entity\Form
 */
class GroupPresetForm extends EntityForm {

  /**
   * The entity being operated on.
   *
   * @var \Drupal\vsite\Entity\GroupPresetInterface
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $this->entity->label(),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('Id'),
      '#default_value' => $this->entity->id(),
      '#machine_name' => [
        'exists' => '\\Drupal\\vsite\\Entity\\GroupPreset::load',
      ],
      // '#disabled' => true.
    ];

    $form['description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#default_value' => $this->entity->get('description'),
    ];

    $applicableToOptions = [];
    /** @var \Drupal\group\Entity\GroupTypeInterface[] $group_types */
    $group_types = $this->entityTypeManager->getStorage('group_type')->loadMultiple();
    foreach ($group_types as $gt) {
      $applicableToOptions[$gt->id()] = $gt->label();
    }
    $form['applicableTo'] = [
      '#type' => 'select',
      '#title' => $this->t('Applies To'),
      '#multiple' => TRUE,
      '#options' => $applicableToOptions,
      '#default_value' => $this->entity->get('applicableTo'),
      '#description' => $this->t('Select what group types can use this preset.'),
      '#required' => TRUE,
    ];

    $form['creationTasks'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Creation Tasks'),
      '#rows' => 10,
      '#default_value' => $this->entity->get('creationTasks'),
      '#description' => $this->t('Enter a fully-qualified class and method name, one per line. Ex. @example', [
        '@example' => '\Drupal\example\Task::taskMethod'
      ])
    ];

    $this->getRedirectDestination()->set(Url::fromRoute('entity.group_preset.collection')->toString());
    return $form;
  }

}

<?php

namespace Drupal\vsite\Entity\Form;


use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\group\Entity\GroupTypeInterface;
use Drupal\vsite\Entity\GroupPresetInterface;

/**
 * Class GroupPresetForm.
 *
 * @package Drupal\vsite\Entity\Form
 */
class GroupPresetForm extends EntityForm {

  /** @var GroupPresetInterface */
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
      //'#disabled' => true
    ];

    $form['description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#default_value' => $this->entity->get('description'),
    ];

    $applicableToOptions = [];
    /** @var GroupTypeInterface[] $group_types */
    $group_types = $this->entityTypeManager->getStorage('group_type')->loadMultiple();
    foreach ($group_types as $gt) {
      $applicableToOptions[$gt->id()] = $gt->label();
    }
    $form['applicableTo'] = [
      '#type' => 'select',
      '#title' => $this->t('Applies To'),
      '#multiple' => true,
      '#options' => $applicableToOptions,
      '#default_value' => $this->entity->get('applicableTo'),
      '#description' => $this->t('Select what group types can use this preset.'),
      '#required' => TRUE,
    ];

    // TODO: Tasks to execute on group creation.


    $this->getRedirectDestination()->set(Url::fromRoute('entity.group_preset.collection')->toString());
    return $form;
  }

}

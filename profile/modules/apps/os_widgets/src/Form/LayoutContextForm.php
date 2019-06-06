<?php


namespace Drupal\os_widgets\Form;


use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;

class LayoutContextForm extends EntityForm {

  public function form(array $form, FormStateInterface $form_state) {
    $context = $this->entity;
    if ($context->isNew()) {
      $form['#title'] = $this->t('Add layout context');
    }
    else {
      $form['#title'] = $this->t('Edit layout context');
    }

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#default_value' => $context->label(),
      '#maxlength' => 255,
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $context->id(),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#machine_name' => [
        'exists' => [$this, 'exists'],
        'source' => ['name'],
      ],
    ];
    $form['description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#default_value' => $context->getDescription(),
    ];
    $form['weight'] = [
      '#type' => 'weight',
      '#title' => 'Weight',
      '#default_value' => $context->getWeight(),
      '#delta' => 10,
      '#description' => $this->t('Set the weight of this context. Higher weighted contexts will override lower weighted.')
    ];
    $form['activationRules'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Activation Rules'),
      '#default_value' => $context->getActivationRules(),
      '#description' => $this->t('A list of paths and routes that this layout context should be active for.')
    ];

    return $form;
  }

}
<?php

namespace Drupal\cp_appearance\Entity\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cp_appearance\AppearanceSettingsBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for CustomTheme add and edit.
 */
class CustomThemeForm extends EntityForm {

  /**
   * Appearance settings builder service.
   *
   * @var \Drupal\cp_appearance\AppearanceSettingsBuilderInterface
   */
  protected $appearanceSettingsBuilder;

  /**
   * Creates a new CustomThemeForm object.
   *
   * @param \Drupal\cp_appearance\AppearanceSettingsBuilderInterface $appearance_settings_builder
   *   Appearance settings builder service.
   */
  public function __construct(AppearanceSettingsBuilderInterface $appearance_settings_builder) {
    $this->appearanceSettingsBuilder = $appearance_settings_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('cp_appearance.appearance_settings_builder'));
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    /** @var \Drupal\cp_appearance\Entity\CustomThemeInterface $entity */
    $entity = $this->entity;
    $base_theme_options = [];

    foreach ($this->appearanceSettingsBuilder->getThemes() as $key => $data) {
      $base_theme_options[$key] = $data->info['name'];
    }

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

    $form['base_theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Parent Theme'),
      '#default_value' => $entity->getBaseTheme(),
      '#options' => $base_theme_options,
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

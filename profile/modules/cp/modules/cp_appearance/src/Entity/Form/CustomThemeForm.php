<?php

namespace Drupal\cp_appearance\Entity\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cp_appearance\AppearanceSettingsBuilderInterface;
use Drupal\cp_appearance\Entity\CustomTheme;
use Drupal\cp_appearance\Entity\CustomThemeException;
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
      '#maxlength' => EntityTypeInterface::ID_MAX_LENGTH,
      '#default_value' => $entity->id(),
      '#machine_name' => [
        'exists' => [$this, 'exists'],
        'source' => ['label'],
      ],
      '#disabled' => !$entity->isNew(),
    ];

    $form['favicon'] = [
      '#type' => 'file',
      '#title' => $this->t('Favicon'),
      '#default_value' => $entity->getFavicon(),
    ];

    $form['base_theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Parent Theme'),
      '#default_value' => $entity->getBaseTheme(),
      '#options' => $base_theme_options,
      '#required' => TRUE,
    ];

    $form['images'] = [
      '#type' => 'file',
      '#title' => $this->t('Images'),
      '#default_value' => $entity->getImages(),
      '#multiple' => TRUE,
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

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Handle file upload.
    // It is silly doing such things in validation handler, but Drupal also does
    // it.
    $file_fields = ['favicon', 'images'];
    $file_validators = [
      'file_validate_extensions' => ['png jpg jpeg'],
    ];
    foreach ($file_fields as $field) {
      $files = file_save_upload($field, $file_validators, 'temporary://');

      if ($files === FALSE) {
        $form_state->setError($form[$field], $this->t('Failed to upload @file_field. Please contact site administrator for support.', [
          '@file_field' => $field,
        ]));
      }
      elseif ($files !== NULL) {
        $form_state->setValue($field, $files);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\cp_appearance\Entity\CustomThemeInterface $entity */
    $entity = $this->getEntity();
    /** @var array $form_state_values */
    $form_state_values = $form_state->getValues();

    // Move uploaded favicon to a persistent location.
    /** @var \Drupal\file\FileInterface $file */
    foreach ($form_state_values['favicon'] as $file) {
      $moved_file = file_move($file, 'public://');

      if ($moved_file === FALSE) {
        throw new CustomThemeException($this->t('Failed to move file. Please contact the site administrator for support.'));
      }

      $entity->setFavicon($moved_file->id());
    }

    // Move uploaded images to a persistent location.
    $uploaded_image_ids = [];
    foreach ($form_state_values['images'] as $file) {
      $moved_file = file_move($file, 'public://');

      if ($moved_file === FALSE) {
        throw new CustomThemeException($this->t('Failed to move file. Please contact the site administrator for support.'));
      }

      $uploaded_image_ids[] = $moved_file->id();
    }

    $entity->setImages($uploaded_image_ids);

    parent::save($form, $form_state);

    $this->messenger()->addStatus($this->t('Custom theme successfully saved.'));
  }

  /**
   * Checks whether a custom theme ID exists already.
   *
   * @param string $id
   *   The ID to check.
   *
   * @return bool
   *   Whether the ID is taken.
   */
  public function exists($id): bool {
    return (bool) CustomTheme::load($id);
  }

}

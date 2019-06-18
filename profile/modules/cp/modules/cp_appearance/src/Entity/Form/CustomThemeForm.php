<?php

namespace Drupal\cp_appearance\Entity\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cp_appearance\AppearanceSettingsBuilderInterface;
use Drupal\cp_appearance\Entity\CustomTheme;
use Drupal\file\FileInterface;
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
      '#description' => $this->t('Enter name of the theme'),
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
      '#description' => $this->t('Upload the favicon for the theme. If no favicon uploaded, then the favicon of the parent theme will be used.'),
      '#default_value' => $entity->getFavicon(),
    ];

    $form['base_theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Parent Theme'),
      '#description' => $this->t('Select the base theme of this custom theme.'),
      '#default_value' => $entity->getBaseTheme(),
      '#options' => $base_theme_options,
      '#required' => TRUE,
    ];

    $form['images'] = [
      '#type' => 'file',
      '#title' => $this->t('Images'),
      '#description' => $this->t('Upload necessary files required for styling of your custom theme. These could be image sprites, arrow icons, etc. The images are going to be put inside %location directory. Therefore make sure you have mentioned the path while writing the style. For example, if you have uploaded %example_file_name, then the style should be <code>background-image: url("@location/@example_file_name")</code>', [
        '%location' => CustomTheme::CUSTOM_THEMES_IMAGES_LOCATION,
        '@location' => CustomTheme::CUSTOM_THEMES_IMAGES_LOCATION,
        '%example_file_name' => 'background-image.png',
        '@example_file_name' => 'background-image.png',
      ]),
      '#default_value' => $entity->getImages(),
      '#multiple' => TRUE,
    ];

    // TODO: Set default_value.
    $form['styles'] = [
      '#type' => 'textarea',
      '#title' => $this->t('CSS'),
      '#description' => $this->t('Enter the styles for your custom theme. These styles will be overriding the styles provided by the selected base theme. The styles are going to be put inside %style_file file.', [
        '%style_file' => CustomTheme::CUSTOM_THEMES_STYLE_LOCATION,
      ]),
      '#required' => TRUE,
    ];

    // TODO: Set default_value.
    $form['scripts'] = [
      '#type' => 'textarea',
      '#title' => $this->t('JavaScript'),
      '#description' => $this->t('Enter the scripts for your custom theme. Make sure that the script is valid, otherwise the site might break. The scripts are going to be put inside %script_file file.', [
        '%script_file' => CustomTheme::CUSTOM_THEMES_SCRIPT_LOCATION,
      ]),
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

      if ($files === NULL) {
        continue;
      }

      // file_save_upload() puts a FALSE inside `$files` in case of errors.
      // `$files` itself is not FALSE.
      // Therefore, validation is done in this way.
      $valid_files = array_filter($files);
      if (count($valid_files) !== count($files)) {
        $form_state->setError($form[$field]);
      }
      else {
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

    if ($form_state_values['favicon']) {
      /** @var \Drupal\file\FileInterface $favicon */
      $favicon = reset($form_state_values['favicon']);
      $entity->setFavicon($favicon->id());
    }

    if ($form_state_values['images']) {
      $uploaded_image_ids = array_map(function (FileInterface $file) {
        return $file->id();
      }, $form_state_values['images']);

      $entity->setImages($uploaded_image_ids);
    }

    $entity->setStyles($form_state_values['styles']);

    if (!empty($form_state_values['scripts'])) {
      $entity->setScripts($form_state_values['scripts']);
    }

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

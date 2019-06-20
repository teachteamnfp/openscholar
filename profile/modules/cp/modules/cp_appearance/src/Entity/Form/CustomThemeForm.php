<?php

namespace Drupal\cp_appearance\Entity\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Extension\ThemeInstallerInterface;
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
   * Theme installer service.
   *
   * @var \Drupal\Core\Extension\ThemeInstallerInterface
   */
  protected $themeInstaller;

  /**
   * Theme handler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Creates a new CustomThemeForm object.
   *
   * @param \Drupal\cp_appearance\AppearanceSettingsBuilderInterface $appearance_settings_builder
   *   Appearance settings builder service.
   * @param \Drupal\Core\Extension\ThemeInstallerInterface $theme_installer
   *   Theme installer service.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   Theme handler service.
   */
  public function __construct(AppearanceSettingsBuilderInterface $appearance_settings_builder, ThemeInstallerInterface $theme_installer, ThemeHandlerInterface $theme_handler) {
    $this->appearanceSettingsBuilder = $appearance_settings_builder;
    $this->themeInstaller = $theme_installer;
    $this->themeHandler = $theme_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('cp_appearance.appearance_settings_builder'), $container->get('theme_installer'), $container->get('theme_handler'));
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
      '#maxlength' => DRUPAL_EXTENSION_NAME_MAX_LENGTH - \strlen(CustomTheme::CUSTOM_THEME_ID_PREFIX),
      '#default_value' => $entity->id(),
      '#machine_name' => [
        'exists' => [$this, 'exists'],
        'source' => ['label'],
      ],
      '#disabled' => !$entity->isNew(),
      '#field_prefix' => CustomTheme::CUSTOM_THEME_ID_PREFIX,
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

    $form['styles'] = [
      '#type' => 'textarea',
      '#title' => $this->t('CSS'),
      '#description' => $this->t('Enter the styles for your custom theme. These styles will be overriding the styles provided by the selected base theme. The styles are going to be put inside %style_file file.', [
        '%style_file' => CustomTheme::CUSTOM_THEMES_STYLE_LOCATION,
      ]),
      '#required' => TRUE,
      '#default_value' => $entity->getStyles(),
    ];

    $form['scripts'] = [
      '#type' => 'textarea',
      '#title' => $this->t('JavaScript'),
      '#description' => $this->t('Enter the scripts for your custom theme. Make sure that the script is valid, otherwise the site might break. The scripts are going to be put inside %script_file file.', [
        '%script_file' => CustomTheme::CUSTOM_THEMES_SCRIPT_LOCATION,
      ]),
      '#default_value' => $entity->getScripts(),
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
    $file_fields = ['images'];
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

    $entity->set('id', CustomTheme::CUSTOM_THEME_ID_PREFIX . $entity->id());

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
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);

    $actions['submit']['#submit'][] = '::install';

    $actions['save_default'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save and set as default theme'),
      '#submit' => [
        '::submitForm',
        '::save',
        '::install',
        '::setDefault',
      ],
    ];

    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  protected function actionsElement(array $form, FormStateInterface $form_state) {
    $element = parent::actionsElement($form, $form_state);

    // Make sure that the "Save and set as default theme" operation is more
    // highlighted.
    $save_button_weight = $element['submit']['#weight'];
    $save_default_button_weight = $element['save_default']['#weight'];

    $element['submit']['#weight'] = $save_default_button_weight;
    unset($element['submit']['#button_type']);
    $element['save_default']['#weight'] = $save_button_weight;
    $element['save_default']['#button_type'] = 'primary';

    return $element;
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

  /**
   * Installs the custom theme.
   *
   * @ingroup forms
   *
   * @throws \Drupal\Core\Extension\ExtensionNameLengthException
   */
  public function install(array &$form, FormStateInterface $form_state): void {
    /** @var \Drupal\cp_appearance\Entity\CustomThemeInterface $custom_theme */
    $custom_theme = $this->getEntity();

    // Reset is necessary, otherwise the system fails to locate the new theme.
    $this->themeHandler->reset();
    $this->themeInstaller->install([$custom_theme->id()]);
  }

  /**
   * Sets the new custom theme as default theme.
   *
   * @ingroup forms
   */
  public function setDefault(array &$form, FormStateInterface $form_state): void {
    /** @var \Drupal\cp_appearance\Entity\CustomThemeInterface $custom_theme */
    $custom_theme = $this->getEntity();

    /** @var \Drupal\Core\Config\Config $theme_setting_mut */
    $theme_setting_mut = $this->configFactory()->getEditable('system.theme');
    $theme_setting_mut->set('default', $custom_theme->id())->save();

    $this->messenger()->addMessage($this->t('Custom theme %name successfully set as default.', [
      '%name' => $custom_theme->label(),
    ]));
  }

}

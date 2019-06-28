<?php

namespace Drupal\cp_appearance\Entity\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cp_appearance\AppearanceSettingsBuilderInterface;
use Drupal\cp_appearance\Entity\CustomTheme;
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
    $custom_theme_directory_path = CustomTheme::ABSOLUTE_CUSTOM_THEMES_LOCATION . '/' . $entity->id();

    foreach ($this->appearanceSettingsBuilder->getFeaturedThemes() as $key => $data) {
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
      '#field_prefix' => $entity->isNew() ? CustomTheme::CUSTOM_THEME_ID_PREFIX : '',
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
      '#type' => 'managed_file',
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

    $styles_path = $custom_theme_directory_path . '/' . CustomTheme::CUSTOM_THEMES_STYLE_LOCATION;
    $form['styles'] = [
      '#type' => 'textarea',
      '#title' => $this->t('CSS'),
      '#description' => $this->t('Enter the styles for your custom theme. These styles will be overriding the styles provided by the selected base theme. The styles are going to be put inside %style_file file.', [
        '%style_file' => CustomTheme::CUSTOM_THEMES_STYLE_LOCATION,
      ]),
      '#required' => TRUE,
      '#default_value' => !$entity->isNew() ? file_get_contents($styles_path) : NULL,
    ];

    $scripts_path = $custom_theme_directory_path . '/' . CustomTheme::CUSTOM_THEMES_SCRIPT_LOCATION;
    $form['scripts'] = [
      '#type' => 'textarea',
      '#title' => $this->t('JavaScript'),
      '#description' => $this->t('Enter the scripts for your custom theme. Make sure that the script is valid, otherwise the site might break. The scripts are going to be put inside %script_file file.', [
        '%script_file' => CustomTheme::CUSTOM_THEMES_SCRIPT_LOCATION,
      ]),
      '#default_value' => (!$entity->isNew() && file_exists($scripts_path)) ? file_get_contents($scripts_path) : NULL,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\cp_appearance\Entity\CustomThemeInterface $entity */
    $entity = $this->getEntity();
    /** @var array $form_state_values */
    $form_state_values = $form_state->getValues();

    if ($entity->isNew()) {
      $entity->set('id', CustomTheme::CUSTOM_THEME_ID_PREFIX . $entity->id());
    }

    if ($form_state_values['images']) {
      $entity->setImages($form_state_values['images']);
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

    $actions['submit']['#submit'][] = '::redirectOnSave';

    $actions['save_default'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save and set as default theme'),
      '#name' => 'save_default',
      '#submit' => [
        '::submitForm',
        '::save',
        '::redirectOnSave',
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
    return (bool) CustomTheme::load(CustomTheme::CUSTOM_THEME_ID_PREFIX . $id);
  }

  /**
   * Redirects to correct location on save.
   *
   * @ingroup forms
   */
  public function redirectOnSave(array &$form, FormStateInterface $form_state): void {
    /** @var array $element */
    $element = $form_state->getTriggeringElement();
    /** @var \Drupal\cp_appearance\Entity\CustomThemeInterface $entity */
    $entity = $this->getEntity();

    $route_parameters = [
      'custom_theme' => $entity->id(),
    ];

    if ($element['#name'] === 'save_default') {
      $route_parameters['make_default'] = TRUE;
    }

    $form_state->setRedirect('cp_custom_theme.install_form', $route_parameters);
  }

}

<?php

namespace Drupal\os_profiles\Plugin\CpSetting;

use Drupal\Component\Utility\Html;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\cp_settings\CpSettingBase;
use Drupal\file\FileUsage\FileUsageInterface;
use Drupal\image_widget_crop\ImageWidgetCropInterface;
use Drupal\media\Entity\Media;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * OS Profiles Setting.
 *
 * @CpSetting(
 *   id = "profiles_setting",
 *   title = @Translation("Profiles"),
 *   group = {
 *    "id" = "profiles",
 *    "title" = @Translation("Profiles"),
 *    "parent" = "cp.settings.app"
 *   }
 * )
 */
class ProfilesSetting extends CpSettingBase {

  /**
   * Entity Display Repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * Renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * File usage interface to configurate an file object.
   *
   * @var Drupal\file\FileUsage\FileUsageInterface
   */
  protected $fileUsage;

  /**
   * Instance of API ImageWidgetCropManager.
   *
   * @var \Drupal\image_widget_crop\ImageWidgetCropInterface
   */
  protected $imageWidgetCropManager;

  /**
   * ProfilesSetting constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager
   *   Vsite context manager.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   Entity Display Repository Interface.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Renderer.
   * @param \Drupal\file\FileUsage\FileUsageInterface $file_usage
   *   File usage service.
   * @param \Drupal\image_widget_crop\ImageWidgetCropInterface $iwc_manager
   *   The ImageWidgetCrop manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, VsiteContextManagerInterface $vsite_context_manager, EntityDisplayRepositoryInterface $entity_display_repository, RendererInterface $renderer, FileUsageInterface $file_usage, ImageWidgetCropInterface $iwc_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $vsite_context_manager);
    $this->entityDisplayRepository = $entity_display_repository;
    $this->renderer = $renderer;
    $this->fileUsage = $file_usage;
    $this->imageWidgetCropManager = $iwc_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('vsite.context_manager'),
      $container->get('entity_display.repository'),
      $container->get('renderer'),
      $container->get('file.usage'),
      $container->get('image_widget_crop.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames(): array {
    return ['os_profiles.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$form, ConfigFactoryInterface $configFactory) {
    $form['#attached']['library'][] = 'os_profiles/settings_hover';
    $config = $configFactory->get('os_profiles.settings');
    $default_mid = $config->get('default_image_mid');

    $view_modes = $this->entityDisplayRepository->getViewModeOptionsByBundle('node', 'person');

    $profile_styles = [];
    foreach ($view_modes as $name => $label) {
      if ($name == 'default') {
        // Display only display modes the user turned on.
        continue;
      }
      $profile_styles[$name] = $label;
    }

    $profile_styles_hover = [];
    $counter = 0;

    $hover_image = $this->getExampleImage($config->get('default_image_mid'));
    // Create markup for style examples when hovering over each style.
    foreach ($profile_styles as $name => $label) {
      $counter++;
      $build_hover = [
        '#theme' => 'os_profiles_example_hover_popup',
        '#counter' => $counter,
        '#label' => Html::escape($label),
        '#profile_example' => [
          '#theme' => 'os_profiles_example_' . $name,
          '#image' => $hover_image,
        ],
      ];
      $profile_styles_hover[$name] = $this->renderer->renderRoot($build_hover);
    }
    $display_types_order = [
      'teaser',
      'sidebar_teaser',
      'title',
      'slide_teaser',
      'no_image_teaser',
    ];

    $form['display_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Display types'),
      '#options' => $this->sortDisplayTypes($profile_styles_hover, $display_types_order),
      '#default_value' => $config->get('display_type'),
    ];

    $form['display_type_description'] = [
      '#type' => 'item',
      '#description' => $this->t('Choose the display type of a person in the "/people" page.'),
    ];

    // Form element for disabling the use of a default image.
    $form['default_image'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Default Image'),
    ];

    $form['default_image']['disable_default_image'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable default image for people profiles'),
      '#default_value' => $config->get('disable_default_image'),
      '#description' => $this->t('If checked no image will be used when viewing the "/people" page.'),
      '#weight' => -1,
    ];

    $suffix = '';
    if (empty($default_mid)) {
      $suffix .= $this->getDefaultImage() . '<br />';
    }
    else {
      $suffix .= $this->getExampleImage($default_mid, 'crop_photo_person_full') . '<br />';
    }
    $suffix .= $this->t('The default image will be used if a profile photo is not available. Instead, you can upload your own default image.<br/>Position the cropping tool over it if necessary. Allowed media types: <strong>image</strong>');
    $suffix .= '<br>';
    $suffix .= $this->t('<strong>IMPORTANT:</strong> You need to click Save configuration in order to display the uploaded profile image');

    $form['default_image']['default_image_mid'] = [
      '#type' => 'container',
      '#input' => TRUE,
      '#default_value' => [],
      '#suffix' => $suffix,
      'media-browser-field' => [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'media-browser-field' => '',
          'types' => 'image',
          'max-filesize' => '512 MB',
          'upload_text' => 'Upload',
          'droppable_text' => 'Drop here.',
          'cardinality' => 1,
          'files' => 'files',
        ],
        '#markup' => $this->t('Loading the Media Browser. Please wait a moment.'),
        '#attached' => [
          'library' => [
            'os_media/mediaBrowserField',
          ],
        ],
      ],
    ];
    if (!empty($default_mid)) {
      $field_layout = \Drupal::entityTypeManager()
        ->getStorage('entity_form_display')
        ->load('node.person.default');
      $content = $field_layout->get('content');
      $settings = $content['field_photo_person']['settings'];
      $form['default_image']['default_image_mid']['media-browser-field']['#attached']['drupalSettings'] = [
        'mediaBrowserField' => [
          'edit-default-image-mid' => [
            'selectedFiles' => [$default_mid],
          ],
        ],
      ];

      $media = Media::load($default_mid);
      $media_images = $media->get('field_media_image')->referencedEntities();
      $file = array_shift($media_images);
      // The key of element are hardcoded into buildCropToForm function,
      // ATM that is mendatory but can change easily.
      $form['default_image']['image_crop'] = [
        '#type' => 'image_crop',
        '#file' => $file,
        '#crop_type_list' => $settings['crop_list'],
        '#preview_image_style' => $settings['preview_image_style'],
        '#crop_preview_image_style' => $settings['crop_preview_image_style'],
        '#show_default_crop' => $settings['show_default_crop'],
        '#show_crop_area' => $settings['show_crop_area'],
        '#warn_mupltiple_usages' => $settings['warn_multiple_usages'],
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(FormStateInterface $form_state, ConfigFactoryInterface $configFactory) {
    $config = $configFactory->getEditable('os_profiles.settings');
    $display_type_changed = $config->get('display_type') != $form_state->getValue('display_type');
    $config->set('display_type', $form_state->getValue('display_type'));
    $config->set('disable_default_image', (bool) $form_state->getValue('disable_default_image'));
    $config->set('image_crop', $form_state->getValue('image_crop'));

    $form_media = $form_state->getValue('default_image_mid', 0);
    if (!empty($form_media[0]['target_id'])) {
      $media = Media::load($form_media[0]['target_id']);
      $media_images = $media->get('field_media_image')->referencedEntities();
      $file = array_shift($media_images);
      $form_state->getFormObject()->setEntity($file);
      $config->set('default_image_mid', $media->id());
    }
    else {
      $config->set('default_image_mid', NULL);
    }
    $config->save(TRUE);
    if (!empty($form_state->getValue('image_crop')) && !empty($file)) {
      // Call IWC manager to attach crop defined into image file.
      $this->imageWidgetCropManager->buildCropToForm($form_state);
    }
    if ($group = $this->vsiteContextManager->getActiveVsite()) {
      Cache::invalidateTags(['node-person-without-image:' . $group->id()]);
      if ($display_type_changed) {
        Cache::invalidateTags(['view:people:page:' . $group->id()]);
      }
    }
  }

  /**
   * Get image markup for example hover.
   */
  public function getExampleImage($default_image_mid = NULL, $image_style = 'crop_photo_person') {
    // Use custom default image if available.
    if (!empty($default_image_mid)) {
      $media = Media::load($default_image_mid);
      $media_images = $media->get('field_media_image')->referencedEntities();
      $image_file = array_shift($media_images);
      $path = $image_file->getFileUri();
      $alt = $media->get('field_media_image')->getValue()[0]['alt'];
      $build = [
        '#theme' => 'image_style',
        '#uri' => $path,
        '#style_name' => $image_style,
        '#alt' => $alt,
      ];
      return $this->renderer->renderRoot($build);
    }
    else {
      // Use default image.
      $build = [
        '#theme' => 'image',
        '#uri' => file_create_url(drupal_get_path('theme', 'os_base') . '/images/person-default-image.png'),
        '#alt' => t('default-image'),
      ];
      return $this->renderer->renderRoot($build);
    }
  }

  /**
   * Get default image.
   */
  public function getDefaultImage() {
    $build = [
      '#theme' => 'image',
      '#uri' => file_create_url(drupal_get_path('theme', 'os_base') . '/images/person-default-image-big.png'),
      '#alt' => t('default-image'),
    ];
    return $this->renderer->renderRoot($build);
  }

  /**
   * Short display types as order array.
   *
   * @param array $display_types
   *   Original array.
   * @param array $order
   *   Desired sorting with listed keys.
   *
   * @return array
   *   Ordered array.
   */
  protected function sortDisplayTypes(array $display_types, array $order) {
    $ordered_display_types = [];
    foreach ($order as $key) {
      if (isset($display_types[$key])) {
        $ordered_display_types[$key] = $display_types[$key];
        unset($display_types[$key]);
      }
    }
    // Merge the rest of array.
    $ordered_display_types = array_merge($ordered_display_types, $display_types);
    return $ordered_display_types;
  }

}

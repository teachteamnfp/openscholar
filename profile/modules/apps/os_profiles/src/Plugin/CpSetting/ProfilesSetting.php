<?php

namespace Drupal\os_profiles\Plugin\CpSetting;

use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\cp_settings\CpSettingBase;
use Drupal\file\Entity\File;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * OS Profiles Setting.
 *
 * @CpSetting(
 *   id = "profiles_setting",
 *   title = @Translation("Profiles Settings"),
 *   group = {
 *    "id" = "profiles",
 *    "title" = @Translation("Profiles Settings"),
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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, VsiteContextManagerInterface $vsite_context_manager, EntityDisplayRepositoryInterface $entity_display_repository, RendererInterface $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $vsite_context_manager);
    $this->entityDisplayRepository = $entity_display_repository;
    $this->renderer = $renderer;
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
      $container->get('renderer')
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

    $hover_image_url = $this->getExampleImage($config->get('default_image_file'));
    // Create markup for style examples when hovering over each style.
    foreach ($profile_styles as $name => $label) {
      $counter++;
      // Prepare the popup and the style name.
      $profile_example_link = '<a href="#" class="profile-pop" data-popbox="pop' . $counter . '">' . Html::escape($label) . '</a>';
      $profile_example_title = '<h2>' . $label . '</h2>';
      // Prepare the profile style example for the popup.
      $build = [
        '#theme' => 'os_profiles_example_' . $name,
        '#image' => $hover_image_url,
      ];
      $profile_example = $this->renderer->render($build);
      $profile_example_text = '<p>' . $profile_example . '</p>';
      $profile_example_output = $profile_example_link . '<div id="pop' . $counter . '" class="stylebox">' . $profile_example_title . $profile_example_text . '</div>';
      $profile_styles_hover[$name] = $profile_example_output;
    }

    $form['display_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Display types'),
      '#options' => $profile_styles_hover,
      '#default_value' => $config->get('display_type'),
      '#description' => t('Choose the display type of a person in the "/people" page.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(FormStateInterface $formState, ConfigFactoryInterface $configFactory) {
    $config = $configFactory->getEditable('os_profiles.settings');
    $config->set('display_type', $formState->getValue('display_type'));
    $config->save(TRUE);
  }

  /**
   * Get image markup for example hover.
   */
  public function getExampleImage($default_image_file) {
    // Use custom default image if available.
    if (!empty($default_image_file)) {
      $image_file = File::load($default_image_file);
      $path = $image_file->getFileUri();
      $build = [
        '#theme' => 'image_style',
        '#path' => $path,
        '#style_name' => 'profile_thumbnail',
      ];
      return $this->renderer->render($build);
    }
    else {
      // Use default image.
      $build = [
        '#theme' => 'image',
        '#uri' => file_create_url(drupal_get_path('theme', 'os_base') . '/images/person-default-image.png'),
      ];
      return $this->renderer->render($build);
    }
  }

}

<?php

namespace Drupal\cp_appearance;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\cp_appearance\Form\FlavorForm;
use Ds\Map;

/**
 * Helper methods for appearance settings.
 */
final class AppearanceSettingsBuilder implements AppearanceSettingsBuilderInterface {

  use StringTranslationTrait;

  /**
   * Theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Theme configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $themeConfig;

  /**
   * Form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * List of currently installed themes in the site.
   *
   * @var \Drupal\Core\Extension\Extension[]
   */
  protected $drupalInstalledThemes;

  /**
   * List of installed themes made from os_base.
   *
   * @var \Drupal\Core\Extension\Extension[]
   */
  protected $osInstalledThemes;

  /**
   * Theme selector builder service.
   *
   * @var \Drupal\cp_appearance\ThemeSelectorBuilderInterface
   */
  protected $themeSelectorBuilder;

  /**
   * AppearanceBuilder constructor.
   *
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   Theme handler.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   Form builder.
   * @param \Drupal\cp_appearance\ThemeSelectorBuilderInterface $theme_selector_builder
   *   Theme selector builder service.
   */
  public function __construct(ThemeHandlerInterface $theme_handler, ConfigFactoryInterface $config_factory, FormBuilderInterface $form_builder, ThemeSelectorBuilderInterface $theme_selector_builder) {
    $this->themeHandler = $theme_handler;
    $this->configFactory = $config_factory;
    $this->formBuilder = $form_builder;
    $this->themeSelectorBuilder = $theme_selector_builder;
    $this->themeConfig = $this->configFactory->get('system.theme');
    $this->drupalInstalledThemes = $this->themeHandler->listInfo();
    $this->osInstalledThemes = array_filter($this->drupalInstalledThemes, function (Extension $theme) {
      return (isset($theme->base_themes) && $theme->base_theme === 'os_base' && $theme->status);
    });
  }

  /**
   * {@inheritdoc}
   */
  public function getThemes(): array {
    // We do not want to make any unwanted changes to osInstalledThemes by
    // mistake.
    $themes = $this->osInstalledThemes;

    uasort($themes, 'system_sort_modules_by_info_name');

    // Attach additional information in the themes.
    foreach ($themes as $theme) {
      $theme->is_default = $this->themeIsDefault($theme);
      $theme->is_admin = FALSE;
      $theme->screenshot = $this->addScreenshotInfo($theme);
      $theme->operations = $this->addOperations($theme);
      $theme->more_operations = $this->addMoreOperations($theme);
      $theme->notes = $this->addNotes($theme);
    }

    return $themes;
  }

  /**
   * {@inheritdoc}
   */
  public function themeIsDefault(Extension $theme): bool {
    /** @var string $theme_default */
    $theme_default = $this->themeConfig->get('default');

    if ($theme_default === $theme->getName()) {
      return TRUE;
    }

    if (isset($theme->sub_themes[$theme_default])) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Adds a screenshot information to the theme.
   *
   * @param \Drupal\Core\Extension\Extension $theme
   *   The theme.
   *
   * @return array|null
   *   Renderable theme_image structure. NULL if no screenshot found.
   */
  protected function addScreenshotInfo(Extension $theme): ?array {
    /** @var string $theme_default */
    $theme_default = $this->themeConfig->get('default');
    $preview = $theme;

    // Make sure that if a flavor is set as default, then its preview is being
    // showed, not its base theme's.
    if (isset($theme->sub_themes[$theme_default])) {
      $preview = $this->drupalInstalledThemes[$theme_default];
    }

    /** @var string|null $screenshot_uri */
    $screenshot_uri = $this->themeSelectorBuilder->getScreenshotUri($preview);

    if ($screenshot_uri) {
      return [
        'uri' => $screenshot_uri,
        'alt' => $this->t('Screenshot for @theme theme', ['@theme' => $preview->info['name']]),
        'title' => $this->t('Screenshot for @theme theme', ['@theme' => $preview->info['name']]),
        'attributes' => ['class' => ['screenshot']],
      ];
    }

    return NULL;
  }

  /**
   * Adds allowed operations to a theme.
   *
   * @param \Drupal\Core\Extension\Extension $theme
   *   The theme.
   *
   * @return array
   *   Renderable theme_link structure.
   */
  protected function addOperations(Extension $theme): array {
    $operations = [];

    if (!$theme->is_default) {
      $operations[] = [
        'title' => $this->t('Set as default'),
        'url' => Url::fromRoute('cp_appearance.cp_select_theme', [
          'theme' => $theme->getName(),
        ]),
        'options' => [
          'attributes' => [
            'title' => $this->t('Set @theme as your theme', ['@theme' => $theme->info['name']]),
            'class' => [
              'btn',
              'btn-sm',
              'btn-default',
              'set-default',
            ],
          ],
        ],
      ];

      $operations[] = [
        'title' => $this->t('Preview'),
        'url' => Url::fromRoute('cp_appearance.preview', [
          'theme' => $theme->getName(),
        ]),
        'options' => [
          'attributes' => [
            'title' => $this->t('Preview @theme', ['@theme' => $theme->info['name']]),
            'class' => [
              'btn',
              'btn-sm',
              'btn-default',
              'preview',
            ],
          ],
        ],
      ];
    }

    return $operations;
  }

  /**
   * Adds additional notes to a theme.
   *
   * @param \Drupal\Core\Extension\Extension $theme
   *   The theme.
   *
   * @return array
   *   Renderable markup structure.
   */
  protected function addNotes(Extension $theme): array {
    $notes = [];

    if ($theme->is_default) {
      $notes[] = $this->t('current theme');
    }

    return $notes;
  }

  /**
   * Adds more allowed operations to a theme.
   *
   * These are the operations which cannot be rendered as links.
   *
   * @param \Drupal\Core\Extension\Extension $theme
   *   The theme.
   *
   * @return array
   *   Renderable form structure.
   *
   * @see \template_preprocess_cp_appearance_themes_page
   */
  protected function addMoreOperations(Extension $theme): array {
    $operations = [];

    if (\property_exists($theme, 'sub_themes')) {
      // Create a key-extension_info mapping.
      $sub_themes = new Map();
      foreach ($theme->sub_themes as $key => $name) {
        $sub_themes->put($key, $this->drupalInstalledThemes[$key]);
      }

      $operations[] = $this->formBuilder->getForm(new FlavorForm($theme, $sub_themes, $this->themeSelectorBuilder, $this->configFactory));
    }

    return $operations;
  }

}

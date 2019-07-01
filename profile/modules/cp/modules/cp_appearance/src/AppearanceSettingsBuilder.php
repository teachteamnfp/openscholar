<?php

namespace Drupal\cp_appearance;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\cp_appearance\Entity\CustomTheme;
use Drupal\cp_appearance\Entity\CustomThemeInterface;
use Drupal\cp_appearance\Form\FlavorForm;
use Ds\Map;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Helper methods for appearance settings.
 */
final class AppearanceSettingsBuilder implements AppearanceSettingsBuilderInterface, ContainerInjectionInterface {

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
   * Form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * List of installed themes made from os_base.
   *
   * @var \Drupal\Core\Extension\Extension[]|null
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
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('theme_handler'),
      $container->get('config.factory'),
      $container->get('form_builder'),
      $container->get('cp_appearance.theme_selector_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFeaturedThemes(): array {
    $themes = $this->osInstalledThemes();

    $this->prepareThemes($themes);

    return $themes;
  }

  /**
   * {@inheritdoc}
   */
  public function themeIsDefault(Extension $theme): bool {
    /** @var \Drupal\Core\Config\Config $theme_config */
    $theme_config = $this->configFactory->get('system.theme');
    /** @var string $theme_default */
    $theme_default = $theme_config->get('default');

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
    /** @var \Drupal\Core\Extension\Extension[] $drupal_installed_themes */
    $drupal_installed_themes = $this->themeHandler->listInfo();
    /** @var \Drupal\Core\Config\Config $theme_config */
    $theme_config = $this->configFactory->get('system.theme');
    /** @var string $theme_default */
    $theme_default = $theme_config->get('default');
    $preview = $theme;

    // Make sure that if a flavor is set as default, then its preview is being
    // showed, not its base theme's.
    if (isset($theme->sub_themes[$theme_default])) {
      $preview = $drupal_installed_themes[$theme_default];
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
        'attributes' => [
          'title' => $this->t('Set @theme as your theme', ['@theme' => $theme->info['name']]),
          'class' => [
            'btn',
            'btn-sm',
            'btn-default',
            'set-default',
          ],
        ],
      ];

      $operations[] = [
        'title' => $this->t('Preview'),
        'url' => Url::fromRoute('cp_appearance.preview', [
          'theme' => $theme->getName(),
        ]),
        'attributes' => [
          'title' => $this->t('Preview @theme', ['@theme' => $theme->info['name']]),
          'class' => [
            'btn',
            'btn-sm',
            'btn-default',
            'preview',
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
      /** @var \Drupal\Core\Extension\Extension[] $drupal_installed_themes */
      $drupal_installed_themes = $this->themeHandler->listInfo();

      $custom_theme_ids = array_map(function (CustomThemeInterface $custom_theme) {
        return $custom_theme->id();
      }, CustomTheme::loadMultiple());
      $flavors_excluding_custom_themes = array_diff(array_keys($theme->sub_themes), $custom_theme_ids);

      // Create a key-extension_info mapping.
      if ($flavors_excluding_custom_themes) {
        $sub_themes = new Map();

        foreach ($flavors_excluding_custom_themes as $sub_theme) {
          $sub_themes->put($sub_theme, $drupal_installed_themes[$sub_theme]);
        }

        $operations[] = $this->formBuilder->getForm(new FlavorForm($theme, $sub_themes, $this->themeSelectorBuilder, $this->configFactory));
      }
    }

    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function getCustomThemes(): array {
    $custom_themes = [];
    $custom_theme_entities = CustomTheme::loadMultiple();
    $themes = $this->themeHandler->listInfo();

    foreach ($themes as $theme) {
      if (isset($custom_theme_entities[$theme->getName()])) {
        $custom_themes[$theme->getName()] = $theme;
      }
    }

    $this->prepareThemes($custom_themes);

    return $custom_themes;
  }

  /**
   * Make the themes ready for settings form.
   *
   * @param \Drupal\Core\Extension\Extension[] $themes
   *   The themes.
   */
  protected function prepareThemes(array &$themes): void {
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
  }

  /**
   * List of installed themes made from os_base.
   *
   * @return \Drupal\Core\Extension\Extension[]
   *   The themes.
   */
  protected function osInstalledThemes(): array {
    if (!$this->osInstalledThemes) {
      $this->osInstalledThemes = array_filter($this->themeHandler->listInfo(), function (Extension $theme) {
        return (isset($theme->base_themes) && $theme->base_theme === 'os_base' && $theme->status);
      });
    }

    return $this->osInstalledThemes;
  }

}

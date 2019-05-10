<?php

namespace Drupal\cp_appearance;

use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ThemeHandlerInterface;

/**
 * Helper methods for building a theme selector.
 */
final class ThemeSelectorBuilder implements ThemeSelectorBuilderInterface {

  /**
   * Theme handler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Installed themes.
   *
   * @var \Drupal\Core\Extension\Extension[]
   */
  protected $installedThemes;

  /**
   * Creates a new ThemeSelectorBuilder object.
   *
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   Theme handler service.
   */
  public function __construct(ThemeHandlerInterface $theme_handler) {
    $this->themeHandler = $theme_handler;
    $this->installedThemes = $theme_handler->listInfo();
  }

  /**
   * {@inheritdoc}
   */
  public function getScreenshotUri(Extension $theme): ?string {
    $candidates = array_merge([$theme->getName()], array_keys($theme->base_themes));

    foreach ($candidates as $candidate) {
      /** @var string $screenshot_uri */
      $screenshot_uri = $this->installedThemes[$candidate]->info['screenshot'];
      if (file_exists($screenshot_uri)) {
        return $screenshot_uri;
      }
    }

    return NULL;
  }

}

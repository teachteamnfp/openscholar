<?php

namespace Drupal\cp_appearance;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Helper methods for building a theme selector.
 */
final class ThemeSelectorBuilder implements ThemeSelectorBuilderInterface, ContainerInjectionInterface {

  /**
   * Theme handler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Creates a new ThemeSelectorBuilder object.
   *
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   Theme handler service.
   */
  public function __construct(ThemeHandlerInterface $theme_handler) {
    $this->themeHandler = $theme_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('theme_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getScreenshotUri(Extension $theme): ?string {
    $candidates = array_merge([$theme->getName()], array_reverse(array_keys($theme->base_themes)));
    $installed_themes = $this->themeHandler->listInfo();

    foreach ($candidates as $candidate) {
      /** @var string $screenshot_uri */
      $screenshot_uri = $installed_themes[$candidate]->info['screenshot'];
      if (file_exists($screenshot_uri)) {
        return $screenshot_uri;
      }
    }

    return NULL;
  }

}

<?php

namespace Drupal\os_theme_preview\Theme;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Drupal\os_theme_preview\HandlerInterface;
use Drupal\os_theme_preview\PreviewManagerInterface;

/**
 * Sets the preview theme.
 */
class Negotiator implements ThemeNegotiatorInterface {

  /**
   * Theme preview handler.
   *
   * @var \Drupal\os_theme_preview\HandlerInterface
   */
  protected $handler;

  /**
   * Theme preview manager.
   *
   * @var \Drupal\os_theme_preview\PreviewManagerInterface
   */
  protected $themePreviewManager;

  /**
   * Negotiator constructor.
   *
   * @param \Drupal\os_theme_preview\HandlerInterface $handler
   *   Theme preview handler service.
   * @param \Drupal\os_theme_preview\PreviewManagerInterface $preview_manager
   *   Theme preview manager.
   */
  public function __construct(HandlerInterface $handler, PreviewManagerInterface $preview_manager) {
    $this->handler = $handler;
    $this->themePreviewManager = $preview_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match): bool {
    return $this->themePreviewManager->isPreviewModeEnabled();
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match): ?string {
    /** @var \Drupal\os_theme_preview\ThemePreviewInterface|null $theme_preview */
    $theme_preview = $this->handler->getPreviewedThemeData();

    if ($theme_preview) {
      return $theme_preview->getName();
    }

    return NULL;
  }

}

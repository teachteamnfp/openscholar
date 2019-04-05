<?php

namespace Drupal\os_theme_preview\Theme;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Drupal\os_theme_preview\HandlerInterface;
use Drupal\os_theme_preview\VsiteWrapperInterface;

/**
 * Sets the preview theme.
 */
class Negotiator implements ThemeNegotiatorInterface {

  /**
   * Preview theme name.
   *
   * @var \Drupal\os_theme_preview\ThemePreview|null
   */
  protected $previewedTheme;

  /**
   * Theme preview handler.
   *
   * @var \Drupal\os_theme_preview\HandlerInterface
   */
  protected $handler;

  /**
   * Theme preview vsite wrapper.
   *
   * @var \Drupal\os_theme_preview\VsiteWrapperInterface
   */
  protected $themePreviewVsiteWrapper;

  /**
   * Negotiator constructor.
   *
   * @param \Drupal\os_theme_preview\HandlerInterface $handler
   *   Theme preview handler service.
   * @param \Drupal\os_theme_preview\VsiteWrapperInterface $vsite_wrapper
   *   Theme preview vsite wrapper.
   */
  public function __construct(HandlerInterface $handler, VsiteWrapperInterface $vsite_wrapper) {
    $this->handler = $handler;
    $this->themePreviewVsiteWrapper = $vsite_wrapper;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match): bool {
    // Also consider current active vsite while applying the theme.
    $this->previewedTheme = $this->handler->getPreviewedThemeData();

    if (!$this->previewedTheme) {
      return FALSE;
    }

    return ($this->previewedTheme->getVsiteId() === $this->themePreviewVsiteWrapper->getActiveVsiteId());
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match): ?string {
    return $this->previewedTheme->getName();
  }

}

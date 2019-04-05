<?php

namespace Drupal\os_theme_preview\Theme;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Drupal\os_theme_preview\HandlerInterface;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;

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
   * Vsite context manager service.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * Negotiator constructor.
   *
   * @param \Drupal\os_theme_preview\HandlerInterface $handler
   *   Theme preview handler service.
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager
   *   Vsite context manager service.
   */
  public function __construct(HandlerInterface $handler, VsiteContextManagerInterface $vsite_context_manager) {
    $this->handler = $handler;
    $this->vsiteContextManager = $vsite_context_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match): bool {
    // Also consider current active vsite while applying the theme.
    $this->previewedTheme = $this->handler->getPreviewedThemeData();
    /** @var \Drupal\group\Entity\GroupInterface|null $group */
    $group = $this->vsiteContextManager->getActiveVsite();

    if (!$this->previewedTheme) {
      return FALSE;
    }

    if (!$group) {
      return ($this->previewedTheme->getVsiteId() === 0);
    }

    return ($this->previewedTheme->getVsiteId() === (int) $group->id());
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match): ?string {
    return $this->previewedTheme->getName();
  }

}

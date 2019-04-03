<?php

namespace Drupal\os_theme_preview\Theme;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Drupal\os_theme_preview\HelperInterface;

/**
 * Sets the preview theme.
 */
class Negotiator implements ThemeNegotiatorInterface {

  /**
   * Preview theme name.
   *
   * @var string|null
   */
  protected $previewedTheme;

  /**
   * Helper service.
   *
   * @var \Drupal\os_theme_preview\HelperInterface
   */
  protected $helper;

  /**
   * Negotiator constructor.
   *
   * @param \Drupal\os_theme_preview\HelperInterface $helper
   *   Helper service.
   */
  public function __construct(HelperInterface $helper) {
    $this->helper = $helper;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match): bool {
    $this->previewedTheme = $this->helper->getPreviewedTheme();

    return (bool) $this->previewedTheme;
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match): ?string {
    return $this->previewedTheme;
  }

}

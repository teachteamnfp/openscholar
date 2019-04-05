<?php

namespace Drupal\os_theme_preview;

use Drupal\vsite\Plugin\VsiteContextManagerInterface;

/**
 * Provides a high level abstraction for the theme preview mode.
 */
final class PreviewManager implements PreviewManagerInterface {

  /**
   * Vsite context manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * Theme preview handler.
   *
   * @var \Drupal\os_theme_preview\HandlerInterface
   */
  protected $handler;

  /**
   * VsiteWrapper constructor.
   *
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager
   *   Vsite context manager.
   * @param \Drupal\os_theme_preview\HandlerInterface $handler
   *   Theme preview handler.
   */
  public function __construct(VsiteContextManagerInterface $vsite_context_manager, HandlerInterface $handler) {
    $this->vsiteContextManager = $vsite_context_manager;
    $this->handler = $handler;
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveVsiteId(): int {
    /** @var \Drupal\group\Entity\Group|null $group */
    $group = $this->vsiteContextManager->getActiveVsite();

    if ($group) {
      return (int) $group->id();
    }

    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function isPreviewModeEnabled(): bool {
    /** @var \Drupal\os_theme_preview\ThemePreviewInterface|null $theme_preview */
    $theme_preview = $this->handler->getPreviewedThemeData();

    if (!$theme_preview) {
      return FALSE;
    }

    return ($theme_preview->getVsiteId() === $this->getActiveVsiteId());
  }

}

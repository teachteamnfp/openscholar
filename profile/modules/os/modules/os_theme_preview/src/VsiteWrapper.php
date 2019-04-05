<?php

namespace Drupal\os_theme_preview;

use Drupal\vsite\Plugin\VsiteContextManagerInterface;

/**
 * Helps to make better bonding between theme preview and vsite.
 */
class VsiteWrapper implements VsiteWrapperInterface {

  /**
   * Vsite context manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * VsiteWrapper constructor.
   *
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager
   *   Vsite context manager.
   */
  public function __construct(VsiteContextManagerInterface $vsite_context_manager) {
    $this->vsiteContextManager = $vsite_context_manager;
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

}

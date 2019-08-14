<?php

namespace Drupal\cp;

use Drupal\Core\Menu\MenuActiveTrailInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\system\SystemManager;

/**
 * CpManager service.
 *
 * Inspired from SystemManager.
 *
 * @see \Drupal\system\SystemManager
 */
class CpManager implements CpManagerInterface {

  use StringTranslationTrait;

  /**
   * System manager service.
   *
   * @var \Drupal\system\SystemManager
   */
  protected $systemManager;

  /**
   * The active menu trail service.
   *
   * @var \Drupal\Core\Menu\MenuActiveTrailInterface
   */
  protected $menuActiveTrail;

  /**
   * Creates a new CpManager object.
   *
   * @param \Drupal\system\SystemManager $system_manager
   *   System manager service.
   * @param \Drupal\Core\Menu\MenuActiveTrailInterface $menu_active_trail
   *   The active menu trail service.
   */
  public function __construct(SystemManager $system_manager, MenuActiveTrailInterface $menu_active_trail) {
    $this->systemManager = $system_manager;
    $this->menuActiveTrail = $menu_active_trail;
  }

  /**
   * {@inheritdoc}
   */
  public function getBlockContents(string $menu_name): array {
    $link = $this->menuActiveTrail->getActiveLink($menu_name);
    if ($link && $content = $this->systemManager->getAdminBlock($link)) {
      $output = [
        '#theme' => 'admin_block_content',
        '#content' => $content,
      ];
    }
    else {
      $output = [
        '#markup' => $this->t('You do not have any administrative items.'),
      ];
    }
    return $output;
  }

}

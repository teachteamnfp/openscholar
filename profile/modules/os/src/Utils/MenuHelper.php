<?php

namespace Drupal\os\Utils;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\os\MenuHelperInterface;

/**
 * Menu Helper functions.
 */
class MenuHelper implements MenuHelperInterface {

  /**
   * Constructs a new DefaultMailTemplate object.
   */
  public function __construct(ConfigFactory $configFactory, ModuleHandlerInterface $moduleHandler) {
    $this->configFactory = $configFactory;
    $this->moduleHandler = $moduleHandler;

  }

  /**
   * Returns the list of menu names used on all OpenScholar sites.
   */
  public function osGetMenus() : array {

    $menus = $this->configFactory->get('os.settings')->get('os_menus');

    $this->moduleHandler->alter('os_menus', $menus);

    return $menus;
  }

}

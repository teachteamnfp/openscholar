<?php

namespace Drupal\cp_menu\Plugin\Block;

use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\vsite\Plugin\VsiteContextManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Provides a block for displaying group menus.
 *
 * @Block(
 *   id = "secondarymenu",
 *   admin_label = @Translation("Secondary menu")
 * )
 */
class SecondaryMenuBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Secondary menu will always be footer by default.
   */
  const SECONDARY_MENU = 'footer';

  /**
   * Vsite Manager service.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManager
   */
  protected $vsiteManager;

  /**
   * Menu Tree service.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuTree;

  /**
   * Current vsite.
   *
   * @var \Drupal\group\Entity\GroupInterface|null
   */
  protected $vsite;

  /**
   * SecondaryMenuBlock constructor.
   *
   * @param array $configuration
   *   Block configuration.
   * @param string $plugin_id
   *   Block id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\vsite\Plugin\VsiteContextManager $vsite_manager
   *   VsiteContextManager instance.
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menu_tree
   *   MenuLinkTreeInterface instance.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, VsiteContextManager $vsite_manager, MenuLinkTreeInterface $menu_tree) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->vsiteManager = $vsite_manager;
    $this->vsite = $this->vsiteManager->getActiveVsite();
    $this->menuTree = $menu_tree;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('vsite.context_manager'),
      $container->get('menu.link_tree')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $menu_name = self::SECONDARY_MENU;
    // Get the associated group menu for the current page.
    if ($this->vsite) {
      $id = $this->vsite->id();
      $secondary_menu_id = 'menu-secondary-' . $id;
      $vsite_menu = $this->vsite->getContent('group_menu:menu', ['entity_id_str' => $secondary_menu_id]);
      $menu_name = $vsite_menu ? $secondary_menu_id : self::SECONDARY_MENU;
    }
    // Render the menus.
    $build = [];
    $parameters = new MenuTreeParameters();
    $parameters->onlyEnabledLinks();
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $this->menuTree->load($menu_name, $parameters);
    $tree = $this->menuTree->transform($tree, $manipulators);
    $build[] = $this->menuTree->build($tree);

    return $build;
  }

}

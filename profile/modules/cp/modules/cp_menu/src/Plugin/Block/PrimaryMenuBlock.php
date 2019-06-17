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
 *   id = "primarymenu",
 *   admin_label = @Translation("Primary menu")
 * )
 */
class PrimaryMenuBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Primary menu will always be main by default.
   */
  const PRIMARY_MENU = 'main';

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
   * PrimaryMenuBlock constructor.
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

    $menu_name = self::PRIMARY_MENU;
    // Get the associated group menu for the current page.
    if ($this->vsite) {
      $id = $this->vsite->id();
      $primary_menu_id = 'menu-primary-' . $id;
      $vsite_menu = $this->vsite->getContent('group_menu:menu', ['entity_id_str' => $primary_menu_id]);
      $menu_name = $vsite_menu ? $primary_menu_id : self::PRIMARY_MENU;
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

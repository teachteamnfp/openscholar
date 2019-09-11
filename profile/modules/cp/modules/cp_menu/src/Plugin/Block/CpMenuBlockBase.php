<?php

namespace Drupal\cp_menu\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\cp_menu\Services\MenuHelper;
use Drupal\vsite\Plugin\VsiteContextManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CpMenuBlockBase.
 *
 * @package Drupal\cp_menu\Plugin\Block
 */
abstract class CpMenuBlockBase extends BlockBase implements ContainerFactoryPluginInterface {

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
   * Current vsite id.
   *
   * @var int|string|null
   */
  protected $id;

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
   * Get the name of the menu in context.
   *
   * @param string $default
   *   The default shared menu.
   *
   * @return string
   *   The menu name.
   */
  public function getMenuName(string $default) {
    if ($this->vsite) {
      $this->id = $this->vsite->id();
      $menu_id = MenuHelper::DEFAULT_VSITE_MENU_MAPPING[$default] . $this->id;
      $vsite_menu = $this->vsite->getContent('group_menu:menu', ['entity_id_str' => $menu_id]);
      return $vsite_menu ? $menu_id : $default;
    }
    return $default;
  }

  /**
   * Load the tree.
   *
   * @param string $menu_name
   *   Menu name to load links off.
   *
   * @return array
   *   The built menu tree.
   */
  public function loadMenuTree(string $menu_name) {
    // Generate build array.
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

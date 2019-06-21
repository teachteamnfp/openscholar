<?php

namespace Drupal\cp_menu\Services;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Menu\MenuLinkTree;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\cp_menu\MenuHelperInterface;
use Drupal\group\Entity\GroupInterface;

/**
 * Class MenuHelper.
 *
 * @package Drupal\cp_menu\Service
 */
class MenuHelper implements MenuHelperInterface {
  use StringTranslationTrait;

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * MenuLinkTree service.
   *
   * @var \Drupal\Core\Menu\MenuLinkTree
   */
  protected $menuTree;

  /**
   * Entity Type Manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Menu Link content storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * MenuHelper constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory instance.
   * @param \Drupal\Core\Menu\MenuLinkTree $menu_tree
   *   Menu Link tree instance.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityTypeManager instance.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(ConfigFactoryInterface $config_factory, MenuLinkTree $menu_tree, EntityTypeManagerInterface $entity_type_manager) {
    $this->configFactory = $config_factory;
    $this->menuTree = $menu_tree;
    $this->entityTypeManager = $entity_type_manager;
    $this->storage = $this->entityTypeManager->getStorage('menu_link_content');
  }

  /**
   * {@inheritdoc}
   */
  public function createVsiteMenus(GroupInterface $vsite) : array {

    // Create vsite specific primary menu.
    $group_menu = $this->createMenu($vsite);
    // Map Links from main to vsite specific menu.
    $this->mapMenuLinks($group_menu);

    // Create vsite specific scondary menu.
    $group_menu_secondary = $this->createMenu($vsite, FALSE);
    // Map Links from secondary to vsite specific menu.
    $this->mapMenuLinks($group_menu_secondary, FALSE);

    // Return newly created tree.
    return $this->menuTree->load('menu-primary-' . $vsite->id(), new MenuTreeParameters());
  }

  /**
   * {@inheritdoc}
   */
  public function resetVsiteMenus($vsite, $secondary = FALSE) : void {
    // Create vsite specific menus.
    $group_menu = $this->createMenu($vsite);
    $group_menu_secondary = $this->createMenu($vsite, FALSE);

    if (!$secondary) {
      $this->storage->create([
        'title' => $this->t('Home'),
        'link' => ['uri' => 'internal:/'],
        'menu_name' => $group_menu->id(),
        'weight' => -1,
        'expanded' => TRUE,
      ])->save();
      // Map secondary menu links.
      $this->mapMenuLinks($group_menu_secondary, FALSE);
    }
    else {
      // Map primary menu links.
      $this->mapMenuLinks($group_menu);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function invalidateBlockCache() : void {
    $tags = [
      'config:block.block.primarymenu',
      'config:block.block.secondarymenu',
    ];
    Cache::invalidateTags($tags);
  }

  /**
   * Create a vsite specific menu.
   *
   * @param \Drupal\group\Entity\GroupInterface $vsite
   *   Group/vsite.
   * @param bool $primary
   *   If primary or secondary menu.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Menu entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createMenu(GroupInterface $vsite, $primary = TRUE) : EntityInterface {
    $id = $primary ? 'menu-primary-' . $vsite->id() : 'menu-secondary-' . $vsite->id();
    $label = $primary ? $this->t('Primary menu') : $this->t('Secondary menu');

    $group_menu = $this->entityTypeManager
      ->getStorage('menu')
      ->create([
        'id' => $id,
        'label' => $label,
        'description' => $this->t('Menu for %label', ['%label' => $vsite->label()]),
      ]);
    $group_menu->save();
    // Add menus to group content.
    $vsite->addContent($group_menu, 'group_menu:menu');
    return $group_menu;
  }

  /**
   * Map menus from shared to new vsite menu.
   *
   * @param \Drupal\Core\Entity\EntityInterface $group_menu
   *   Group menu.
   * @param bool $primary
   *   If primary or secondary menu.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function mapMenuLinks(EntityInterface $group_menu, $primary = TRUE) : void {
    // Load shared main menu tree.
    $menu = $primary ? 'main' : 'footer';
    $sharedMenuTree = $this->menuTree->load($menu, new MenuTreeParameters());

    foreach ($sharedMenuTree as $links) {
      $definition = $links->link->getPluginDefinition();
      $route_name = $definition['route_name'];
      $this->storage->create([
        'title' => $this->t('@title', ['@title' => $definition['title']]),
        'link' => ['uri' => "route:$route_name"],
        'menu_name' => $group_menu->id(),
        'weight' => $definition['weight'],
        'expanded' => TRUE,
      ])->save();
    }
  }

}

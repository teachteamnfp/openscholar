<?php

namespace Drupal\cp_menu\Services;

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
    $group_menu = $this->createPrimaryMenu($vsite);
    // Map Links from main to vsite specific menu.
    $this->mapPrimaryMenuLinks($group_menu);

    // Create vsite specific scondary menu.
    $group_menu_secondary = $this->createSecondaryMenu($vsite);
    // Map Links from secondary to vsite specific menu.
    $this->mapSecondaryMenuLinks($group_menu_secondary);

    // Return newly created tree.
    return $this->menuTree->load('menu-primary-' . $vsite->id(), new MenuTreeParameters());
  }

  /**
   * {@inheritdoc}
   */
  public function resetVsiteMenus($vsite, $secondary = FALSE) : void {
    // Create vsite specific primary menu.
    $group_menu = $this->createPrimaryMenu($vsite);
    $group_menu_secondary = $this->createSecondaryMenu($vsite);

    if (!$secondary) {
      $this->storage->create([
        'title' => $this->t('Home'),
        'link' => ['uri' => 'internal:/'],
        'menu_name' => $group_menu->id(),
        'weight' => -1,
        'expanded' => TRUE,
      ])->save();
      $this->mapSecondaryMenuLinks($group_menu_secondary);
    }
    else {
      $this->mapPrimaryMenuLinks($group_menu);
    }
  }

  /**
   * Create a vsite specific primary menu.
   *
   * @param \Drupal\group\Entity\GroupInterface $vsite
   *   Group/vsite.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Menu entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createPrimaryMenu(GroupInterface $vsite) : EntityInterface {
    $group_menu = $this->entityTypeManager
      ->getStorage('menu')
      ->create([
        'id' => 'menu-primary-' . $vsite->id(),
        'label' => $this->t('Primary menu'),
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
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function mapPrimaryMenuLinks(EntityInterface $group_menu) : void {
    // Load shared main menu tree.
    $sharedMainMenuTree = $this->menuTree->load('main', new MenuTreeParameters());

    foreach ($sharedMainMenuTree as $links) {
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

  /**
   * Create a vsite specific primary menu.
   *
   * @param \Drupal\group\Entity\GroupInterface $vsite
   *   Group/vsite.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Menu Entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createSecondaryMenu(GroupInterface $vsite) : EntityInterface {
    $group_menu_secondary = $this->entityTypeManager->getStorage('menu')
      ->create([
        'id' => 'menu-secondary-' . $vsite->id(),
        'label' => $this->t('Secondary menu'),
        'description' => $this->t('Menu for %label', ['%label' => $vsite->label()]),
      ]);
    $group_menu_secondary->save();
    $vsite->addContent($group_menu_secondary, 'group_menu:menu');
    return $group_menu_secondary;
  }

  /**
   * Map menus from shared to new vsite menu.
   *
   * @param \Drupal\Core\Entity\EntityInterface $group_menu_secondary
   *   Group menu secondary.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function mapSecondaryMenuLinks(EntityInterface $group_menu_secondary) : void {
    // Load shared secondary menu tree.
    $sharedSecondaryMenuTree = $this->menuTree->load('footer', new MenuTreeParameters());

    foreach ($sharedSecondaryMenuTree as $links) {
      $definition = $links->link->getPluginDefinition();
      $route_name = $definition['route_name'];
      $this->storage->create([
        'title' => $this->t('@title', ['@title' => $definition['title']]),
        'link' => ['uri' => "route:$route_name"],
        'menu_name' => $group_menu_secondary->id(),
        'weight' => $definition['weight'],
        'expanded' => TRUE,
      ])->save();
    }
  }

}

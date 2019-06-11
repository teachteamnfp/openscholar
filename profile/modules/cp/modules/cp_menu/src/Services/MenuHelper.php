<?php

namespace Drupal\cp_menu\Services;

use Drupal\Core\Config\ConfigFactoryInterface;
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
   * MenuHelper constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory instance.
   * @param \Drupal\Core\Menu\MenuLinkTree $menu_tree
   *   Menu Link tree instance.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityTypeManager instance.
   */
  public function __construct(ConfigFactoryInterface $config_factory, MenuLinkTree $menu_tree, EntityTypeManagerInterface $entity_type_manager) {
    $this->configFactory = $config_factory;
    $this->menuTree = $menu_tree;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function createVsiteMenus(GroupInterface $vsite) : array {

    // Create vsite specific primary menu.
    $group_menu = $this->entityTypeManager
      ->getStorage('menu')
      ->create([
        'id' => 'menu-primary-' . $vsite->id(),
        'label' => $this->t('Primary menu'),
        'description' => $this->t('Menu for %label', ['%label' => $vsite->label()]),
      ]);
    $group_menu->save();

    // Create vsite specific scondary menu.
    $group_menu_secondary = $this->entityTypeManager->getStorage('menu')
      ->create([
        'id' => 'menu-secondary-' . $vsite->id(),
        'label' => $this->t('Secondary menu'),
        'description' => $this->t('Menu for %label', ['%label' => $vsite->label()]),
      ]);
    $group_menu_secondary->save();

    // Load shared menu tree.
    $sharedMenuTree = $this->menuTree->load('main', new MenuTreeParameters());
    // Add menu link for group if enabled.
    $menu_content_storage = $this->entityTypeManager->getStorage('menu_link_content');

    foreach ($sharedMenuTree as $links) {
      $definition = $links->link->getPluginDefinition();
      $route_name = $definition['route_name'];
      $menu_content_storage->create([
        'title' => $this->t('@title', ['@title' => $definition['title']]),
        'link' => ['uri' => "route:$route_name"],
        'menu_name' => $group_menu->id(),
        'weight' => $definition['weight'],
        'expanded' => TRUE,
      ])->save();
    }

    $vsite->addContent($group_menu, 'group_menu:menu');
    $vsite->addContent($group_menu_secondary, 'group_menu:menu');
    // Return newly created tree.
    return $this->menuTree->load('menu-primary-' . $vsite->id(), new MenuTreeParameters());
  }

}

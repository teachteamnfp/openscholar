<?php

namespace Drupal\cp_menu\Services;

use Drupal\bibcite_entity\Entity\ReferenceInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Menu\MenuLinkTree;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\cp_menu\MenuHelperInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\vsite\Plugin\VsiteContextManager;

/**
 * Class MenuHelper.
 *
 * @package Drupal\cp_menu\Service
 */
class MenuHelper implements MenuHelperInterface {
  use StringTranslationTrait;

  public const DEFAULT_VSITE_MENU_MAPPING = [
    'main' => 'menu-primary-',
    'footer' => 'menu-secondary-',
  ];

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
   * Vsite Context Manager service.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManager
   */
  protected $vsiteManager;

  /**
   * Menu Link manager service.
   *
   * @var \Drupal\Core\Menu\MenuLinkManagerInterface
   */
  protected $menuLinkManager;

  /**
   * Entity Field Manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;


  /**
   * Entity Repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * MenuHelper constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory instance.
   * @param \Drupal\Core\Menu\MenuLinkTree $menu_tree
   *   Menu Link tree instance.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityTypeManager instance.
   * @param \Drupal\vsite\Plugin\VsiteContextManager $vsite_manager
   *   Vsite context manager instance.
   * @param \Drupal\Core\Menu\MenuLinkManagerInterface $menu_link_manager
   *   Menu Link manager interface.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   Entity field manager instance.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity Repository instance.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(ConfigFactoryInterface $config_factory, MenuLinkTree $menu_tree, EntityTypeManagerInterface $entity_type_manager, VsiteContextManager $vsite_manager, MenuLinkManagerInterface $menu_link_manager, EntityFieldManagerInterface $entity_field_manager, EntityRepositoryInterface $entity_repository) {
    $this->configFactory = $config_factory;
    $this->menuTree = $menu_tree;
    $this->entityTypeManager = $entity_type_manager;
    $this->storage = $this->entityTypeManager->getStorage('menu_link_content');
    $this->vsiteManager = $vsite_manager;
    $this->menuLinkManager = $menu_link_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->entityRepository = $entity_repository;
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
    $this->invalidateBlockCache($vsite, ['primarymenu', 'secondarymenu'], TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function invalidateBlockCache($vsite, $ids, $buildForm = FALSE) : void {
    $menu_label = $ids;
    // If not called from Main build form then ids will be a single string.
    if (!$buildForm) {
      $menus = $vsite->getContent('group_menu:menu');
      foreach ($menus as $menu) {
        $this->menus[$menu->entity_id_str->target_id] = $menu->label();
      }
      $menu_label = $this->menus[$ids];
      $menu_label = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $menu_label));
      $tags[] = "config:block.block.$menu_label";
    }
    // If called from main build form ids will be an array.
    else {
      foreach ($menu_label as $label) {
        $label = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $label));
        $tags[] = "config:block.block.$label";
      }
    }
    $tags = array_unique($tags);
    Cache::invalidateTags($tags);
  }

  /**
   * {@inheritdoc}
   */
  public function getMenuLinkDefaults(ReferenceInterface $reference, GroupInterface $vsite): array {
    // Get the default max_length of a menu link title from the base field
    // definition.
    $field_definitions = $this->entityFieldManager
      ->getBaseFieldDefinitions('menu_link_content');
    $max_length = $field_definitions['title']->getSetting('max_length');
    $description_max_length = $field_definitions['description']->getSetting('max_length');
    $defaults = [
      'entity_id' => 0,
      'id' => '',
      'title' => '',
      'title_max_length' => $max_length,
      'description' => '',
      'description_max_length' => $description_max_length,
      'menu_name' => 'main',
    ];

    if ($reference->id()) {
      // Check all allowed menus.
      $menus = $vsite->getContent('group_menu:menu');
      $menuIds = NULL;
      foreach ($menus as $menu) {
        $menuIds[] = $menu->entity_id_str->target_id;
      }

      if ($menuIds) {
        $query = $this->entityTypeManager->getStorage('menu_link_content')->getQuery()
          ->condition('link.uri', 'entity:bibcite_reference/' . $reference->id())
          ->condition('menu_name', $menuIds, 'IN')
          ->sort('id', 'ASC')
          ->range(0, 1);
        $result = $query->execute();
      }

      $id = (!empty($result)) ? reset($result) : FALSE;

      if ($id) {
        $menu_link = MenuLinkContent::load($id);
        $menu_link = $this->entityRepository->getTranslationFromContext($menu_link);
        $defaults = [
          'entity_id' => $menu_link->id(),
          'id' => $menu_link->getPluginId(),
          'title' => $menu_link->getTitle(),
          'title_max_length' => $menu_link->getFieldDefinitions()['title']->getSetting('max_length'),
          'description' => $menu_link->getDescription(),
          'description_max_length' => $menu_link->getFieldDefinitions()['description']->getSetting('max_length'),
          'menu_name' => $menu_link->getMenuName(),
        ];
      }
    }
    return $defaults;
  }

  /**
   * {@inheritdoc}
   */
  public function publicationInFormMenuAlterations(array $values, ReferenceInterface $reference, GroupInterface $vsite) :void {
    $menuId = $values['menu']['menu_parent'];
    $linkId = $values['menu']['id'];
    $enabled = $values['menu']['enabled'];

    // If existing link update plugin definition.
    if ($linkId && $enabled) {
      $link = $this->menuLinkManager->getDefinition($linkId);
      // If changes are there then only proceed.
      if ($link['title'] != $values['menu']['title'] || $link['description'] != $values['menu']['description'] || $link['menu_name'] != $values['menu']['menu_parent']) {
        $updatedValues['title'] = $this->t('@title', ['@title' => $values['menu']['title']]);
        $updatedValues['description'] = $this->t('@desc', ['@desc' => $values['menu']['description']]);
        $updatedValues['menu_name'] = $values['menu']['menu_parent'];
        // Update definitions.
        $this->menuLinkManager->updateDefinition($linkId, $updatedValues);
        // Call the block cache clear method as changes are made.
        $menuId = $this->menuLinkManager->getDefinition($linkId)['menu_name'];
        $this->invalidateBlockCache($vsite, $menuId);
      }
    }
    elseif ($linkId && !$enabled) {
      // Get the menu id before plugin is deleted to clear cache later.
      $menuId = $this->menuLinkManager->getDefinition($linkId)['menu_name'];
      // Delete the link.
      $this->menuLinkManager->removeDefinition($linkId);
      // Call the block cache clear method as changes are made.
      $this->invalidateBlockCache($vsite, $menuId);
    }
    // If new link create a new menu link content.
    elseif ($enabled) {
      $menus = $vsite->getContent('group_menu:menu');
      // If first time then create a new menu by replicating shared menus.
      if (!$menus) {
        // Create new menus.
        $this->createVsiteMenus($vsite);

        $vsiteMenuId = self::DEFAULT_VSITE_MENU_MAPPING[$menuId] . $vsite->id();

        // Create a new menu_link_content entity.
        MenuLinkContent::create([
          'link' => ['uri' => 'entity:bibcite_reference/' . $reference->id()],
          'langcode' => $reference->language()->getId(),
          'enabled' => TRUE,
          'title' => trim($values['menu']['title']),
          'description' => trim($values['menu']['description']),
          'menu_name' => $vsiteMenuId,
        ])->save();
        // Call the block cache clear method as changes are made.
        $this->invalidateBlockCache($vsite, $vsiteMenuId);
      }
    }
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

      $link_uri = "route:$route_name";
      // Make sure all necessary info is present for node routes.
      // This method wouldn't be invoked if there are publication menus,
      // because, that would be mean vsite menus are already present.
      // See \os_publications_bibcite_reference_form_submit.
      if ($route_name === 'entity.node.canonical') {
        $link_uri = "entity:node/{$definition['route_parameters']['node']}";
      }

      $this->storage->create([
        'title' => $this->t('@title', ['@title' => $definition['title']]),
        'link' => ['uri' => $link_uri],
        'menu_name' => $group_menu->id(),
        'weight' => $definition['weight'],
        'expanded' => TRUE,
      ])->save();
    }
  }

}

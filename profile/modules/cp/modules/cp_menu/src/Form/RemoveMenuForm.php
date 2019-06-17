<?php

namespace Drupal\cp_menu\Form;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Url;
use Drupal\cp_menu\MenuHelperInterface;
use Drupal\vsite\Plugin\VsiteContextManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Remove the menu or reset it.
 */
class RemoveMenuForm extends ConfirmFormBase {

  /**
   * The ID of the item to delete.
   *
   * @var string
   */
  protected $id;

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Entity Manager Service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityManager;

  /**
   * Vsite Manager Service.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManager
   */
  protected $vsiteManager;

  /**
   * Vsite Id.
   *
   * @var int|string|null
   */
  protected $vsiteId;

  /**
   * Menu Link manager service.
   *
   * @var \Drupal\Core\Menu\MenuLinkManagerInterface
   */
  protected $menuLinkManager;

  /**
   * Menu helper service.
   *
   * @var \Drupal\cp_menu\MenuHelperInterface
   */
  protected $menuHelper;

  /**
   * Constructor to initialize instances.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   Config factory instance.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   EntityType Manager instance.
   * @param \Drupal\vsite\Plugin\VsiteContextManager $vsite_manager
   *   Vsite manager instance.
   * @param \Drupal\Core\Menu\MenuLinkManagerInterface $menu_link_manager
   *   MenuLink manager instance.
   * @param \Drupal\cp_menu\MenuHelperInterface $menu_helper
   *   Menu helper instance.
   */
  public function __construct(ConfigFactory $config_factory, EntityTypeManager $entity_type_manager, VsiteContextManager $vsite_manager, MenuLinkManagerInterface $menu_link_manager, MenuHelperInterface $menu_helper) {
    $this->configFactory = $config_factory;
    $this->entityManager = $entity_type_manager;
    $this->vsiteManager = $vsite_manager;
    $this->vsiteId = $this->vsiteManager->getActiveVsite()->id();
    $this->menuLinkManager = $menu_link_manager;
    $this->revert = [
      'menu-primary-' . $this->vsiteId,
      'menu-secondary-' . $this->vsiteId,
    ];
    $this->menuHelper = $menu_helper;
  }

  /**
   * Inject all services we need.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Service container.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('vsite.context_manager'),
      $container->get('plugin.manager.menu.link'),
      $container->get('cp_menu.menu_helper')
    );
  }

  /**
   * Form id.
   */
  public function getFormId() {
    return 'cp_remove_menu';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to %text the menu: %label?', [
      '%text' => in_array($this->id, $this->revert) ? 'reset' : 'remove',
      '%label' => $this->label,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('cp.build.menu');
  }

  /**
   * Builds the form.
   *
   * @param array $form
   *   The form itself.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param string|null $menu_id
   *   The menu id.
   * @param string|null $label
   *   The menu label.
   *
   * @return array
   *   The built form.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $menu_id = NULL, $label = NULL) {
    $this->id = $menu_id;
    $this->label = $label;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Delete all links and add Home if Primary Menu.
    $vsite = $this->vsiteManager->getActiveVsite();
    $menus = $vsite->getContent('group_menu:menu');
    // If first time then create a new menu by replicating shared menu.
    if (!$menus) {
      if ($this->id === 'main') {
        // Create new primary menu with home link & map secondary menu changes.
        $this->menuHelper->resetVsiteMenus($vsite);
      }
      elseif ($this->id === 'footer') {
        $this->menuHelper->resetVsiteMenus($vsite, TRUE);
      }
      $form_state->setRedirect('cp.build.menu');
      return;
    }

    if ($this->id === 'menu-primary-' . $this->vsiteId) {
      $this->menuLinkManager->deleteLinksInMenu($this->id);
      // Add Home menu link for group if enabled.
      $menu_content_storage = $this->entityManager->getStorage('menu_link_content');
      $weight = -1;
      $menu_content_storage->create([
        'title' => t('Home'),
        'link' => ['uri' => 'internal:/'],
        'menu_name' => $this->id,
        'weight' => $weight,
        'expanded' => TRUE,
      ])->save();
    }
    // Delete all links if Secondary Menu.
    elseif ($this->id === 'menu-secondary-' . $this->vsiteId) {
      $this->menuLinkManager->deleteLinksInMenu($this->id);
    }
    // Delete all links and the menu itself.
    else {
      $groupMenu = $this->entityManager->getStorage('menu')->load($this->id);
      $groupMenu->delete();
      $this->menuLinkManager->deleteLinksInMenu($this->id);
    }
    $form_state->setRedirect('cp.build.menu');
  }

}

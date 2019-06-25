<?php

namespace Drupal\cp_menu\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Url;
use Drupal\cp_menu\MenuHelperInterface;
use Drupal\vsite\Plugin\VsiteContextManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DeleteMenuLinkForm.
 *
 * @package Drupal\cp_menu\Form
 */
class DeleteMenuLinkForm extends ConfirmFormBase {

  /**
   * The ID of the item to delete.
   *
   * @var string
   */
  protected $id;

  /**
   * The label of the menu link..
   *
   * @var string
   */
  protected $label;

  /**
   * Menu Link manager service.
   *
   * @var \Drupal\Core\Menu\MenuLinkManagerInterface
   */
  protected $menuLinkManager;

  /**
   * Vsite Manager service.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManager
   */
  protected $vsiteManager;

  /**
   * Menu helper service.
   *
   * @var \Drupal\cp_menu\MenuHelperInterface
   */
  protected $menuHelper;

  /**
   * Constructor to initialize instances.
   *
   * @param \Drupal\Core\Menu\MenuLinkManagerInterface $menu_link_manager
   *   MenuLink manager instance.
   * @param \Drupal\vsite\Plugin\VsiteContextManager $vsite_manager
   *   Vsite Manager instance.
   * @param \Drupal\cp_menu\MenuHelperInterface $menu_helper
   *   Menu helper instance.
   */
  public function __construct(MenuLinkManagerInterface $menu_link_manager, VsiteContextManager $vsite_manager, MenuHelperInterface $menu_helper) {
    $this->menuLinkManager = $menu_link_manager;
    $this->vsiteManager = $vsite_manager;
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
      $container->get('plugin.manager.menu.link'),
      $container->get('vsite.context_manager'),
      $container->get('cp_menu.menu_helper')
    );
  }

  /**
   * Form id.
   */
  public function getFormId() : string {
    return 'cp_delete_menu_link';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() : string {
    return $this->t('Are you sure you want to delete the "%link" link?', [
      '%link' => $this->label,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() : URL {
    return new Url('cp.build.menu');
  }

  /**
   * Builds the form.
   *
   * @param array $form
   *   The form itself.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param string|null $link_id
   *   The link plugin id.
   * @param string|null $link_title
   *   The label.
   *
   * @return array
   *   The built form.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $link_id = NULL, $link_title = NULL) : array {
    $this->id = $link_id;
    $this->label = $link_title;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) : void {

    $vsite = $this->vsiteManager->getActiveVsite();
    $menus = $vsite->getContent('group_menu:menu');
    // If first time then create a new menu by replicating shared menu.
    if (!$menus) {
      // Create new menus and get the tree for deleting it's menu.
      $tree = $this->menuHelper->createVsiteMenus($vsite);
      foreach ($tree as $element) {
        if ($this->label == $element->link->getTitle()) {
          $pluginId = $element->link->getPluginId();
        }
      }
    }
    $pluginId = $pluginId ?? $this->id;
    // Get the menu id before plugin is deleted to clear cache later.
    $menu_id = $this->menuLinkManager->getDefinition($pluginId)['menu_name'];
    // Delete the link.
    $this->menuLinkManager->removeDefinition($pluginId);

    // Call the block cache clear method as changes are made.
    $this->menuHelper->invalidateBlockCache($menu_id);

    $form_state->setRedirect('cp.build.menu');
  }

}

<?php

namespace Drupal\cp_menu\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\cp_menu\MenuHelperInterface;
use Drupal\vsite\Plugin\VsiteContextManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EditMenuLinkForm.
 *
 * @package Drupal\cp_menu\Form
 */
class EditMenuLinkForm extends FormBase {

  /**
   * Menu Link Manager service.
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
   * EditMenuLinkForm constructor.
   *
   * @param \Drupal\Core\Menu\MenuLinkManagerInterface $menu_link_manager
   *   MenuLinkManager instance.
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
   * {@inheritdoc}
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
   *
   * @return string
   *   The form id.
   */
  public function getFormId() : string {
    return 'cp_menu_link_edit';
  }

  /**
   * Builds the form.
   *
   * @param array $form
   *   The form to build.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param string|null $link_id
   *   The link id.
   *
   * @return array
   *   The form.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function buildForm(array $form, FormStateInterface $form_state, $link_id = NULL) : array {

    $link = $this->menuLinkManager->getDefinition($link_id);
    $form['#link'] = $link;

    $form['title'] = [
      '#title' => $this->t('Title'),
      '#type' => 'textfield',
      '#description' => $this->t('Text for your link or heading.'),
      '#default_value' => $link['title'],
      '#required' => TRUE,
    ];

    $form['tooltip'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tooltip'),
      '#description' => $this->t('Text displayed when mouse hovers over link'),
      '#default_value' => $link['description'] ? $link['description'] : '',
    ];

    $form['actions'] = [
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
      ],
      'cancel' => [
        '#type' => 'submit',
        '#value' => $this->t('Cancel'),
      ],
    ];
    return $form;
  }

  /**
   * Submits the form.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) : void {
    $link = $form['#link'];
    if ($form_state->getValue('op')->__toString() === 'Save') {
      // If changes are there then only proceed.
      if ($link['title'] != $form_state->getValue('title') || $link['description'] != $form_state->getValue('tooltip')) {
        $updated_values['title'] = $this->t('@title', ['@title' => $form_state->getValue('title')]);
        $updated_values['description'] = $this->t('@tooltip', ['@tooltip' => $form_state->getValue('tooltip')]);

        $vsite = $this->vsiteManager->getActiveVsite();
        $menus = $vsite->getContent('group_menu:menu');
        // If first time then create a new menu by replicating shared menu.
        if (!$menus) {
          // Create new menus and get the tree for editing it's menu.
          $tree = $this->menuHelper->createVsiteMenus($vsite);
          foreach ($tree as $element) {
            if ($link['title'] == $element->link->getTitle()) {
              $pluginId = $element->link->getPluginId();
            }
          }
        }
        $pluginId = $pluginId ?? $link['id'];
        // Update definitions.
        $this->menuLinkManager->updateDefinition($pluginId, $updated_values);

        // Call the block cache clear method as changes are made.
        $menu_id = $this->menuLinkManager->getDefinition($pluginId)['menu_name'];
        $this->menuHelper->invalidateBlockCache($vsite, $menu_id);
      }
    }
    $form_state->setRedirect('cp.build.menu');
  }

}

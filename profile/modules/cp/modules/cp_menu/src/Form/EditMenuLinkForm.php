<?php

namespace Drupal\cp_menu\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
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
   * EditMenuLinkForm constructor.
   *
   * @param \Drupal\Core\Menu\MenuLinkManagerInterface $menu_link_manager
   *   MenuLinkManager instance.
   */
  public function __construct(MenuLinkManagerInterface $menu_link_manager) {
    $this->menuLinkManager = $menu_link_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.menu.link')
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
    $form['#link_id'] = $link_id;

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
      '#type' => 'actions',
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
    $link_id = $form['#link_id'];
    if ($form_state->getValue('op')->__toString() === 'Save') {
      $updated_values['title'] = $form_state->getValue('title');
      if ($form_state->getValue('tooltip')) {
        $updated_values['description'] = $form_state->getValue('tooltip');
      }
      $this->menuLinkManager->updateDefinition($link_id, $updated_values);
    }
    $form_state->setRedirect('cp.build.menu');
  }

}

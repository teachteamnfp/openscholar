<?php

namespace Drupal\cp_menu\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Url;
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
   * Constructor to initialize instances.
   *
   * @param \Drupal\Core\Menu\MenuLinkManagerInterface $menu_link_manager
   *   MenuLink manager instance.
   */
  public function __construct(MenuLinkManagerInterface $menu_link_manager) {
    $this->menuLinkManager = $menu_link_manager;
  }

  /**
   * Inject all services we need.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Service container.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.menu.link')
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
    // Delete all links and add Home if Primary Menu.
    $this->menuLinkManager->removeDefinition($this->id);
    $form_state->setRedirect('cp.build.menu');
  }

}

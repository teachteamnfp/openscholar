<?php

namespace Drupal\cp_users\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Allows site owners to specify a new site owner.
 *
 * @package Drupal\cp_users\Form
 */
class CpUsersOwnershipForm extends FormBase {

  /**
   * Vsite Context Manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('vsite.context_manager')
    );
  }

  /**
   * CpUsersOwnershipForm constructor.
   */
  public function __construct(VsiteContextManagerInterface $vsiteContextManager) {
    $this->vsiteContextManager = $vsiteContextManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cp-users-change-ownership-form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var VsiteContextManagerInterface $vsiteContextManager */
    if ($group = $this->vsiteContextManager->getActiveVsite()) {
      $role = 'personal-member';
      $users = [];
      /** @var \Drupal\user\UserInterface[] $memberships */
      $memberships = $group->getContentEntities('group_membership');
      foreach ($memberships as $u) {
        $users[$u->id()] = $u->getDisplayName();
      }

      $form['wrapper'] = [
        '#type' => 'container',
        'title' => [
          '#type' => 'markup',
          '#markup' => '<h2>' . $this->t('Choose a new site owner for the @site site', ['@site' => $group->label()]) . '</h2>',
        ],
        'new_owner' => [
          '#type' => 'select',
          '#title' => $this->t('Username'),
          '#required' => TRUE,
          '#options' => $users,
        ],
        'actions' => [
          '#type' => 'actions',
          'save' => [
            '#type' => 'submit',
            '#value' => $this->t('Save'),
            '#attributes' => [
              'class' => 'use-ajax',
            ],
            '#ajax' => [
              'callback' => [$this, 'submitForm'],
              'event' => 'click',
            ],
          ],
          'cancel' => [
            '#type' => 'button',
            '#value' => $this->t('Cancel'),
            '#attributes' => [
              'class' => 'use-ajax',
            ],
            '#ajax' => [
              'callback' => [$this, 'closeModal'],
              'event' => 'click',
            ],
          ],
        ],
      ];

      return $form;
    }
    else {
      throw new AccessDeniedHttpException();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    if ($group = $this->vsiteContextManager->getActiveVsite()) {
      $new_owner_id = $form_state->getValue('new_owner');
      $group->setOwnerId($new_owner_id);
      $group->save();

      $response->addCommand(new CloseModalDialogCommand());
      $response->addCommand(new RedirectCommand(Url::fromRoute('cp.users')->toString()));
    }
    else {
      throw new AccessDeniedHttpException();
    }

    return $response;
  }

  /**
   * Close the modal.
   */
  public function closeModal(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new CloseModalDialogCommand());
    return $response;
  }

}

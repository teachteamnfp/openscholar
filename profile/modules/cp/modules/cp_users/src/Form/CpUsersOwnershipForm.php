<?php

namespace Drupal\cp_users\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\user\UserInterface;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Allows site owners to specify a new site owner.
 *
 * @package Drupal\cp_users\Form
 */
class CpUsersOwnershipForm extends FormBase {

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
    $vsiteContextManager = \Drupal::service('vsite.context_manager');
    if ($group = $vsiteContextManager->getActiveVsite()) {
      $role = 'personal-member';
      $users = [];
      $memberships = $group->getMembers($role);
      foreach ($memberships as $m) {
        $u = $m->getUser();
        $users[$u->id()] = $u->getDisplayName();
      }

      $form['wrapper'] = [
        '#type' => 'container',
        'title' => [
          '#type' => 'markup',
          '#markup' => '<h2>'.$this->t('Choose a new site owner for the @site site', array('@site' => $group->label())).'</h2>'
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
        ]
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
    /** @var VsiteContextManagerInterface $vsite */
    $vsite = \Drupal::service('vsite.context_manager');

    if ($group = $vsite->getActiveVsite()) {
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

  public function closeModal(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new CloseModalDialogCommand());
    return $response;
  }

}

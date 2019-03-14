<?php

namespace Drupal\cp_users\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Controller for the add User to Site form.
 */
class CpUsersAddForm extends FormBase {

  /**
   * Vsite Context Manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('vsite.context_manager'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(VsiteContextManagerInterface $vsiteContextManager, EntityTypeManagerInterface $entityTypeManager) {
    $this->vsiteContextManager = $vsiteContextManager;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cp-users-add-form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $group = $this->vsiteContextManager->getActiveVsite();
    if (!$group) {
      throw new AccessDeniedHttpException();
    }

    $roleData = [];
    $descriptions = [];
    /*@var \Drupal\group\Entity\GroupTypeInterface $group_type */
    $group_type = $group->getGroupType();
    $roles = $group_type->getRoles(TRUE);
    /* @var \Drupal\group\Entity\GroupRoleInterface $role */
    foreach ($roles as $rid => $role) {
      if (!$role->isAnonymous() && !$role->isOutsider()) {
        $roleData[$rid] = $role->label();
      }
    }

    $form['existing-member'] = [
      '#type' => 'details',
      '#title' => $this->t('Add an Existing User'),
      'member-entity' => [
        '#type' => 'entity_autocomplete',
        '#target_type' => 'user',
        '#selection_settings' => [
          'include_anonymous' => FALSE,
        ],
        '#title' => $this->t('Member'),
      ],
      'role' => [
        '#type' => 'radios',
        '#title' => $this->t('Role'),
        '#options' => $roleData,
      ],
    ];

    $form['new-user'] = [
      '#type' => 'details',
      '#title' => $this->t('Add New User'),
      'todo' => [
        '#type' => 'markup',
        '#markup' => '//TODO: This',
      ],
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
        '#submit' => [
          '::submitForm',
        ],
        '#attributes' => [
          'class' => 'use-ajax-submit',
        ],
      ],
      'cancel' => [
        '#type' => 'button',
        '#value' => $this->t('Cancel'),
        '#submit' => [
          '::closeModal',
        ],
        '#attributes' => [
          'class' => 'use-ajax-submit',
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $group = $this->vsiteContextManager->getActiveVsite();
    if (!$group) {
      $response->setStatusCode(403, 'Forbidden');
    }
    else {
      $response->addCommand(new CloseModalDialogCommand());
      $response->addCommand(new RedirectCommand(Url::fromRoute('cp.users')->toString()));
      /** @var string $entity */
      $entity = $form_state->getValue('member-entity');
      /** @var \Drupal\user\UserInterface $account */
      $account = $this->entityTypeManager->getStorage('user')->load($entity);
      /** @var string $role */
      $role = $form_state->getValue('role');

      $values = [
        'group_roles' => [
          $role,
        ],
      ];
      $group->addMember($account, $values);
    }
    return $response;
  }

  /**
   * Closes the modal.
   */
  public function closeModal() {
    $response = new AjaxResponse();
    $response->addCommand(new CloseModalDialogCommand());
    return $response;
  }

}

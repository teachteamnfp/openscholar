<?php

namespace Drupal\cp_users\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
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

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
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
      '#attributes' => [
        'id' => 'existing-member-fieldset',
      ],
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
      '#attributes' => [
        'id' => 'new-user-fieldset',
      ],
      '#access' => !$this->config('cp_users.settings')->get('disable_user_creation'),
      'first_name' => [
        '#type' => 'textfield',
        '#title' => $this->t('First Name'),
        '#maxlength' => 255,
        '#size' => 60,
      ],
      'last_name' => [
        '#type' => 'textfield',
        '#title' => $this->t('Last Name'),
        '#maxlength' => 255,
        '#size' => 60,
      ],
      'username' => [
        '#type' => 'textfield',
        '#title' => $this->t('Username'),
        '#maxlength' => 255,
        '#size' => 60,
      ],
      'email' => [
        '#type' => 'textfield',
        '#title' => $this->t('E-mail Address'),
        '#maxlength' => 255,
        '#size' => 60,
      ],
      'role' => [
        '#type' => 'radios',
        '#title' => $this->t('Role'),
        '#options' => $roleData,
      ],
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
        '#attributes' => [
          'class' => [
            'use-ajax',
          ],
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
          'class' => [
            'use-ajax',
          ],
        ],
        '#ajax' => [
          'callback' => [$this, 'closeModal'],
          'event' => 'click',
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    static $response = NULL;
    // For some reason this function gets run twice? Not sure exactly why.
    // This is a workaround to return the response we've already created.
    if ($response) {
      return $response;
    }
    $response = new AjaxResponse();
    $group = $this->vsiteContextManager->getActiveVsite();
    if (!$group) {
      $response->setStatusCode(403, 'Forbidden');
    }
    else {
      $response->addCommand(new CloseModalDialogCommand());
      $response->addCommand(new RedirectCommand(Url::fromRoute('cp.users')->toString()));
      /** @var string $entity */
      if ($entity = $form_state->getValue('member-entity')) {
        /** @var \Drupal\user\UserInterface $account */
        $account = $this->entityTypeManager->getStorage('user')->load($entity);
        $email_key = CP_USERS_ADD_TO_GROUP;
      }
      else {
        $account = User::create([
          'field_first_name' => $form_state->getValue('first_name'),
          'field_last_name' => $form_state->getValue('last_name'),
          'name' => $form_state->getValue('username'),
          'mail' => $form_state->getValue('email'),
          'status' => TRUE,
        ]);
        $account->save();
        $email_key = CP_USERS_NEW_USER;
      }

      /** @var string $role */
      $role = $form_state->getValue('role');
      if (!$role) {
        $role = $group->getGroupType()->getMemberRoleId();
      }

      $values = [
        'group_roles' => [
          $role,
        ],
      ];
      $group->addMember($account, $values);

      $params = [
        'user' => $account,
        'role' => $role,
        'creator' => \Drupal::currentUser(),
        'group' => $group,
      ];
      /** @var \Drupal\Core\Mail\MailManagerInterface $mailManager */
      $mailManager = \Drupal::service('plugin.manager.mail');
      $mailManager->mail('cp_users', $email_key, $form_state->getValue('email'), LanguageInterface::LANGCODE_DEFAULT, $params);
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

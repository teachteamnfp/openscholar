<?php

namespace Drupal\cp_users\Controller;

use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\cp_users\Access\ChangeOwnershipAccessCheck;
use Drupal\cp_users\CpUsersHelperInterface;
use Drupal\user\UserInterface;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Controller for the cp_users page.
 *
 * Also invokes the modals.
 */
class CpUserMainController extends ControllerBase {

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
   * ChangeOwnershipAccessCheck service.
   *
   * @var \Drupal\cp_users\Access\ChangeOwnershipAccessCheck
   */
  protected $changeOwnershipAccessChecker;

  /**
   * CpUsers helper service.
   *
   * @var \Drupal\cp_users\CpUsersHelperInterface
   */
  protected $cpUsersHelper;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('vsite.context_manager'),
      $container->get('entity_type.manager'),
      $container->get('cp_users.change_ownership_access_check'),
      $container->get('cp_users.cp_users_helper')
    );
  }

  /**
   * CpUserMainController constructor.
   *
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsiteContextManager
   *   Vsite Context Manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager.
   * @param \Drupal\cp_users\Access\ChangeOwnershipAccessCheck $change_ownership_access_check
   *   ChangeOwnershipAccessCheck service.
   * @param \Drupal\cp_users\CpUsersHelperInterface $cp_users_helper
   *   CpUsers helper service.
   */
  public function __construct(VsiteContextManagerInterface $vsiteContextManager, EntityTypeManagerInterface $entityTypeManager, ChangeOwnershipAccessCheck $change_ownership_access_check, CpUsersHelperInterface $cp_users_helper) {
    $this->vsiteContextManager = $vsiteContextManager;
    $this->entityTypeManager = $entityTypeManager;
    $this->changeOwnershipAccessChecker = $change_ownership_access_check;
    $this->cpUsersHelper = $cp_users_helper;
  }

  /**
   * Entry point for cp/users.
   */
  public function main() {
    $group = $this->vsiteContextManager->getActiveVsite();
    if (!$group) {
      throw new AccessDeniedHttpException();
    }
    /** @var \Drupal\Core\Session\AccountInterface $current_user */
    $current_user = $this->currentUser();
    $can_change_ownership = ($this->changeOwnershipAccessChecker->access($current_user) instanceof AccessResultAllowed);

    $users = $group->getContentEntities('group_membership');

    $build = [];

    $userRows = [];
    /* @var \Drupal\user\UserInterface $u */
    foreach ($users as $u) {
      $is_vsite_owner = $this->cpUsersHelper->isVsiteOwner($group, $u);
      $roles = $group->getMember($u)->getRoles();
      $role_link = '';

      if ($can_change_ownership && $is_vsite_owner) {
        $role_link = Link::createFromRoute('Change Owner', 'cp.users.owner', ['user' => $u->id()], ['attributes' => ['class' => ['use-ajax']]])->toString();
      }
      elseif (!$is_vsite_owner &&
        ($this->currentUser()->hasPermission('change user roles') || $group->getMember($this->currentUser())->hasPermission('manage cp roles'))) {
        $role_link = Link::createFromRoute($this->t('Change Role'), 'cp_users.role.change', ['user' => $u->id()], [
          'attributes' => [
            'class' => ['use-ajax'],
            'data-dialog-type' => 'modal',
          ],
        ])->toString();
      }

      $remove_link = Link::createFromRoute($this->t('Remove'), 'cp.users.remove', ['user' => $u->id()], ['attributes' => ['class' => ['use-ajax']]])->toString();
      $row = [
        'data-user-id' => $u->id(),
        'data' => [
          $u->label(),
          $u->label(),
          $group->getOwnerId() == $u->id() ? $this->t('Site Owner') : current($roles)->label(),
          $role_link,
          $this->t('Active'),
          ($group->getOwnerId() == $u->id()) ? '' : $remove_link,
        ],
      ];
      $userRows[] = $row;
    }

    $build['cp_user'] = [
      '#cache' => [
        'max-age' => 0,
      ],
      '#type' => 'container',
      '#attributes' => [
        'id' => 'cp-user',
        'class' => ['cp-manage-users-wrapper'],
      ],
      'cp_user_actions' => [
        '#type' => 'container',
        'add-member' => [
          '#type' => 'link',
          '#title' => $this->t('Add a member'),
          '#url' => Url::fromRoute('cp.users.add'),
          '#attributes' => [
            'class' => [
              'os-green-button',
              'cp-user-float-right',
              'use-ajax',
              'button',
              'button--primary',
              'button-action',
              'action-links',
            ],
            'data-dialog-type' => 'modal',
          ],
          '#attached' => [
            'library' => [
              'core/drupal.dialog.ajax',
            ],
          ],
        ],
      ],
      'cp_user_table' => [
        '#type' => 'table',
        '#header' => [
          $this->t('Name'),
          $this->t('Username'),
          $this->t('Role'),
          $this->t('Change Role'),
          $this->t('Status'),
          $this->t('Remove'),
        ],
        '#rows' => $userRows,
        '#empty' => $this->t('There are no users in your site. This is very not right, please contact the support team immediately.'),
        '#attributes' => [
          'class' => ['cp-manager-user-content'],
        ],
      ],
    ];

    return $build;
  }

  /**
   * Opens a modal with the Add Member form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The response returned to the client.
   */
  public function addUserForm() {
    $group = $this->vsiteContextManager->getActiveVsite();
    $dialogOptions = [
      'dialogClass' => 'add-user-dialog',
      'width' => 800,
      'modal' => TRUE,
      'position' => [
        'my' => "center top",
        'at' => "center top",
      ],
    ];
    if (!$group) {
      throw new AccessDeniedHttpException();
    }

    $response = new AjaxResponse();

    $modal_form = $this->formBuilder()->getForm('Drupal\cp_users\Form\CpUsersAddForm');

    $response->addCommand(new OpenModalDialogCommand('Add Member', $modal_form, $dialogOptions));

    return $response;
  }

  /**
   * Open a modal with the Remove User.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user being removed from the site.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The response to open the modal
   */
  public function removeUserForm(UserInterface $user) {
    $group = $this->vsiteContextManager->getActiveVsite();
    if (!$group) {
      throw new AccessDeniedHttpException();
    }

    $response = new AjaxResponse();

    $modal_form = $this->formBuilder()->getForm('Drupal\cp_users\Form\CpUsersRemoveForm', $user);

    $response->addCommand(new OpenModalDialogCommand($this->removeUserFormTitle($user), $modal_form, ['width' => '800']));

    return $response;
  }

  /**
   * Customize the title to have the target user's name.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user being removed from the site.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The title of the modal.
   */
  public function removeUserFormTitle(UserInterface $user) {
    return $this->t('Remove Member @name', ['@name' => $user->label()]);
  }

  /**
   * Modal for changing the owner of a Vsite.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The response to open the modal.
   */
  public function changeOwnershipForm() {
    $group = $this->vsiteContextManager->getActiveVsite();
    if (!$group) {
      throw new AccessDeniedHttpException();
    }

    $response = new AjaxResponse();

    $modal_form = $this->formBuilder()->getForm('Drupal\cp_users\Form\CpUsersOwnershipForm');

    $response->addCommand(new OpenModalDialogCommand('Change Site Ownership', $modal_form, ['width' => '800']));

    return $response;
  }

}

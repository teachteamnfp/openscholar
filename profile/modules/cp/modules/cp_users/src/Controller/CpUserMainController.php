<?php

namespace Drupal\cp_users\Controller;


use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\TranslationManager;
use Drupal\user\UserInterface;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CpUserMainController extends ControllerBase {

  /**
   * @var VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * @var EntityTypeManagerInterface
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
   * CpUserMainController constructor.
   *
   * @param VsiteContextManagerInterface $vsiteContextManager
   * @param EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct(VsiteContextManagerInterface $vsiteContextManager, EntityTypeManagerInterface $entityTypeManager) {
    $this->vsiteContextManager = $vsiteContextManager;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Entry point for cp/users
   */
  public function main() {
    $group = $this->vsiteContextManager->getActiveVsite();
    if (!$group) {
      throw new AccessDeniedHttpException();
    }

    $users = $group->getContentEntities('group_membership');

    $build = [];

    $userRows = [];
    /** @var UserInterface $u */
    foreach ($users as $u) {
      $row = [
        $u->label(),
        $u->label(),
        $this->t('Site Owner'),
        $this->t('Active')
      ];
      $userRows[] = $row;
    }

    $build['cp_user'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'cp-user',
        'class' => ['cp-manage-users-wrapper']
      ],
      'cp_user_actions' => [
        '#theme' => 'links',
        '#links' => [
          [
            'title' => $this->t('+ Add a member'),
            'href' => 'cp/users/add',
            'attributes' => [
              'class' => ['os-green-button', 'cp-user-float-right']
            ]
          ]
        ]
      ],
      'cp_user_table' => [
        '#type' => 'table',
        '#header' => [
          $this->t('Name'),
          $this->t('Username'),
          $this->t('Role'),
          $this->t('Status')
        ],
        '#rows' => $userRows,
        '#empty' => $this->t('There are no users in your site. This is very not right, please contact the support team immediately.'),
        '#attributes' => [
          'class' => ['cp-manager-user-content']
        ]
      ]
    ];

    return $build;
  }

}

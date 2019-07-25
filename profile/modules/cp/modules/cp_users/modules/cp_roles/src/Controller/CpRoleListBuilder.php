<?php

namespace Drupal\cp_roles\Controller;

use Drupal\Core\Config\Entity\DraggableListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\cp_roles\CpRolesEditable;
use Drupal\cp_roles\CpRolesEditableInterface;
use Drupal\group\Entity\GroupTypeInterface;
use Drupal\group\GroupRoleSynchronizerInterface;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * GroupRole list builder.
 *
 * GroupRoleListBuilder has hardcoded the route `entity.group_role.collection`.
 * This makes it impossible to expose another route to list custom roles.
 *
 * @see \Drupal\group\Entity\GroupRole
 * @see \Drupal\group\Entity\Controller\GroupRoleListBuilder
 */
class CpRoleListBuilder extends DraggableListBuilder {

  /**
   * The group type to check for roles.
   *
   * @var \Drupal\group\Entity\GroupTypeInterface
   */
  protected $groupType;

  /**
   * Vsite context manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * Active group.
   *
   * @var \Drupal\group\Entity\GroupInterface|null
   */
  protected $activeVsite;

  /**
   * Group role synchronizer.
   *
   * @var \Drupal\group\GroupRoleSynchronizerInterface
   */
  protected $groupRoleSynchronizer;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * CpRoles editable service.
   *
   * @var \Drupal\cp_roles\CpRolesEditableInterface
   */
  protected $cpRolesEditable;

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Creates a new CpRoleListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route match service.
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager
   *   Vsite context manager.
   * @param \Drupal\group\GroupRoleSynchronizerInterface $group_role_synchronizer
   *   Group role synchronizer.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\cp_roles\CpRolesEditableInterface $cp_roles_editable
   *   CpRoles editable service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   Current user.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, RouteMatchInterface $route_match, VsiteContextManagerInterface $vsite_context_manager, GroupRoleSynchronizerInterface $group_role_synchronizer, EntityTypeManagerInterface $entity_type_manager, CpRolesEditableInterface $cp_roles_editable, AccountProxyInterface $current_user) {
    parent::__construct($entity_type, $storage);

    $this->vsiteContextManager = $vsite_context_manager;
    $this->activeVsite = $this->vsiteContextManager->getActiveVsite();
    $this->groupRoleSynchronizer = $group_role_synchronizer;
    $this->entityTypeManager = $entity_type_manager;
    $this->cpRolesEditable = $cp_roles_editable;
    $this->currentUser = $current_user;

    $parameters = $route_match->getParameters();
    $group_type = $parameters->get('group_type');

    if ($group_type instanceof GroupTypeInterface) {
      $this->groupType = $group_type;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('current_route_match'),
      $container->get('vsite.context_manager'),
      $container->get('group_role.synchronizer'),
      $container->get('entity_type.manager'),
      $container->get('cp_roles.editable'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityIds() {
    // Do not show synchronized roles in the list.
    $synchronized_roles = [];
    /** @var \Drupal\User\RoleInterface[] $user_roles */
    $user_roles = $this->entityTypeManager->getStorage('user_role')->loadMultiple();

    foreach (array_keys($user_roles) as $role_id) {
      if ($role_id === 'anonymous' || $role_id === 'authenticated') {
        continue;
      }
      $synchronized_roles[] = $this->groupRoleSynchronizer->getGroupRoleId($this->groupType->id(), $role_id);
    }

    $roles_filter = $synchronized_roles;
    if ($this->activeVsite) {
      $roles_filter = array_merge($this->cpRolesEditable->getNonConfigurableGroupRoles($this->activeVsite), $synchronized_roles);
    }

    $query = $this->getStorage()->getQuery()
      ->condition('id', $roles_filter, 'NOT IN')
      ->condition('group_type', $this->groupType->id(), '=')
      ->sort($this->entityType->getKey('weight'));

    // Only add the pager if a limit is specified.
    if ($this->limit) {
      $query->pager($this->limit);
    }

    return array_values($query->execute());
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'group_admin_roles';
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

    // Prepare operations for default roles.
    if ($this->activeVsite &&
      $entity->hasLinkTemplate('permissions-form') &&
      ($this->currentUser->hasPermission('manage default group roles') &&
      \in_array($entity->id(), $this->cpRolesEditable->getDefaultGroupRoles($this->activeVsite), TRUE))) {
      $operations['permissions'] = [
        'title' => $this->t('Edit permissions'),
        'weight' => 5,
        'url' => $entity->toUrl('permissions-form'),
      ];
    }

    // Prepare operations for custom roles.
    if ($this->activeVsite &&
      !\in_array($entity->id(), $this->cpRolesEditable->getDefaultGroupRoles($this->activeVsite), TRUE)) {
      $operations['permissions'] = [
        'title' => $this->t('Edit permissions'),
        'weight' => 5,
        'url' => Url::fromRoute('cp_roles.role.role_permission_form', [
          'group_role' => $entity->id(),
          'group_type' => $this->groupType->id(),
        ]),
      ];

      $operations['edit'] = [
        'title' => $this->t('Edit'),
        'weight' => 10,
        'url' => $this->ensureDestination(Url::fromRoute('cp_roles.role.edit_form', [
          'group_role' => $entity->id(),
          'group_type' => $this->groupType->id(),
        ])),
      ];

      $operations['delete'] = [
        'title' => $this->t('Delete'),
        'weight' => 15,
        'url' => $this->ensureDestination(Url::fromRoute('cp_roles.role.delete_form', [
          'group_role' => $entity->id(),
          'group_type' => $this->groupType->id(),
        ])),
      ];
    }

    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['table']['#empty'] = $this->t('No group roles available. <a href="@link">Add group role</a>.', [
      '@link' => Url::fromRoute('entity.group_role.add_form', ['group_type' => $this->groupType->id()])->toString(),
    ]);
    return $build;
  }

}

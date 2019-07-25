<?php

namespace Drupal\cp_roles\Form;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cp_roles\CpRolesEditableInterface;
use Drupal\group\Access\GroupPermissionHandlerInterface;
use Drupal\group\Entity\GroupRoleInterface;
use Drupal\group\Form\GroupPermissionsForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the user permissions administration form for a specific group role.
 */
final class CpRolesPermissionsSpecificForm extends GroupPermissionsForm {

  /**
   * The specific group role for this form.
   *
   * @var \Drupal\group\Entity\GroupRoleInterface
   */
  protected $groupRole;

  /**
   * CpRoles editable service.
   *
   * @var \Drupal\cp_roles\CpRolesEditableInterface
   */
  protected $cpRolesEditable;

  /**
   * Creates a new CpRolesPermissionsSpecificForm object.
   *
   * @param \Drupal\group\Access\GroupPermissionHandlerInterface $permission_handler
   *   The group permission handler.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\cp_roles\CpRolesEditableInterface $cp_roles_editable
   *   CpRoles editable service.
   */
  public function __construct(GroupPermissionHandlerInterface $permission_handler, ModuleHandlerInterface $module_handler, CpRolesEditableInterface $cp_roles_editable) {
    parent::__construct($permission_handler, $module_handler);
    $this->cpRolesEditable = $cp_roles_editable;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('group.permissions'),
      $container->get('module_handler'),
      $container->get('cp_roles.editable')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getGroupType() {
    return $this->groupRole->getGroupType();
  }

  /**
   * {@inheritdoc}
   */
  protected function getGroupRoles() {
    return [$this->groupRole->id() => $this->groupRole];
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param \Drupal\group\Entity\GroupRoleInterface $group_role
   *   The group role used for this form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state, GroupRoleInterface $group_role = NULL) {
    if ($group_role->isInternal()) {
      return [
        '#title' => t('Error'),
        'description' => [
          '#prefix' => '<p>',
          '#suffix' => '</p>',
          '#markup' => t('Cannot edit an internal group role directly.'),
        ],
      ];
    }

    if ($this->cpRolesEditable->isDefaultGroupRole($group_role)) {
      return [
        '#title' => t('Error'),
        'description' => [
          '#prefix' => '<p>',
          '#suffix' => '</p>',
          '#markup' => t('Cannot edit an default group role directly.'),
        ],
      ];
    }

    $this->groupRole = $group_role;
    return parent::buildForm($form, $form_state);
  }

}

<?php

namespace Drupal\cp_users\Form;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cp_users\CpRolesHelperInterface;
use Drupal\group\Access\GroupPermissionHandlerInterface;
use Drupal\group\Form\GroupPermissionsForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base for CpUsers permissions form.
 */
abstract class CpUsersPermissionsForm extends GroupPermissionsForm {

  /**
   * CpRoles helper service.
   *
   * @var \Drupal\cp_users\CpRolesHelperInterface
   */
  protected $cpRolesHelper;

  /**
   * Creates a new CpUsersPermissionsForm object.
   *
   * @param \Drupal\group\Access\GroupPermissionHandlerInterface $permission_handler
   *   The group permission handler.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\cp_users\CpRolesHelperInterface $cp_roles_helper
   *   CpRoles editable service.
   */
  public function __construct(GroupPermissionHandlerInterface $permission_handler, ModuleHandlerInterface $module_handler, CpRolesHelperInterface $cp_roles_helper) {
    parent::__construct($permission_handler, $module_handler);
    $this->cpRolesHelper = $cp_roles_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('group.permissions'),
      $container->get('module_handler'),
      $container->get('cp_users.cp_roles_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $role_info = [];
    // Sort the group roles using the static sort() method.
    // See \Drupal\Core\Config\Entity\ConfigEntityBase::sort().
    $group_roles = $this->getGroupRoles();
    uasort($group_roles, '\Drupal\group\Entity\GroupRole::sort');

    foreach ($group_roles as $role_name => $group_role) {
      $role_info[$role_name] = [
        'label' => $group_role->label(),
        'permissions' => $group_role->getPermissions(),
        'is_anonymous' => $group_role->isAnonymous(),
        'is_outsider' => $group_role->isOutsider(),
        'is_member' => $group_role->isMember(),
      ];
    }

    // This overrides the default permissions form, and improves the UX.
    // Instead, of building the form elements from scratch, it re-uses the form
    // elements from parent.
    foreach ($this->getPermissions() as $provider => $sections) {
      $form["provider_$provider"] = [
        '#type' => 'fieldset',
        '#title' => $this->moduleHandler->getName($provider),
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
      ];

      $form["provider_$provider"]['permissions'] = [
        '#type' => 'table',
        '#header' => [$this->t('Permission')],
        '#id' => 'permissions',
        '#attributes' => ['class' => ['permissions', 'js-permissions']],
      ];

      $form["provider_$provider"]['permissions']['#header'] = $form['permissions']['#header'];

      foreach ($sections as $section => $permissions) {
        // Create a clean section ID.
        $section_id = $provider . '-' . preg_replace('/[^a-z0-9_]+/', '_', strtolower($section));

        // Start each section with a full width row containing the section name.
        $form["provider_$provider"]['permissions'][$section_id] = $form['permissions'][$section_id];

        // Then list all of the permissions for that provider and section.
        foreach ($permissions as $perm => $perm_item) {
          // Create a row for the permission, starting with the description
          // cell.
          $form["provider_$provider"]['permissions'][$perm]['description'] = $form['permissions'][$perm]['description'];
          $form['permissions'][$perm]['description']['#context']['description'] = $perm_item['description'];
          $form['permissions'][$perm]['description']['#context']['warning'] = $perm_item['warning'];

          // Finally build a checkbox cell for every group role.
          foreach ($role_info as $role_name => $info) {
            $form["provider_$provider"]['permissions'][$perm][$role_name] = $form['permissions'][$perm][$role_name];
          }
        }
      }

      // Do not show relationship permissions in the UI.
      foreach ($this->cpRolesHelper->getRestrictedPermissions($this->getGroupType()) as $permission) {
        unset($form["provider_$provider"]['permissions'][$permission]);
      }
    }

    // The default permissions form element is no longer required.
    unset($form['permissions']);

    return $form;
  }

}

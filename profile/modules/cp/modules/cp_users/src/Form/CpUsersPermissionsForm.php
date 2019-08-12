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

      foreach ($role_info as $info) {
        $form["provider_$provider"]['permissions']['#header'][] = [
          'data' => $info['label'],
          'class' => ['checkbox'],
        ];
      }

      foreach ($sections as $section => $permissions) {
        // Create a clean section ID.
        $section_id = $provider . '-' . preg_replace('/[^a-z0-9_]+/', '_', strtolower($section));

        // Start each section with a full width row containing the section name.
        $form["provider_$provider"]['permissions'][$section_id] = [
          [
            '#wrapper_attributes' => [
              'colspan' => count($group_roles) + 1,
              'class' => ['section'],
              'id' => 'section-' . $section_id,
            ],
            '#markup' => $section,
          ],
        ];

        // Then list all of the permissions for that provider and section.
        foreach ($permissions as $perm => $perm_item) {
          // Create a row for the permission, starting with the description
          // cell.
          $form["provider_$provider"]['permissions'][$perm]['description'] = [
            '#type' => 'inline_template',
            '#template' => '<span class="title">{{ title }}</span>{% if description or warning %}<div class="description">{% if warning %}<em class="permission-warning">{{ warning }}</em><br />{% endif %}{{ description }}</div>{% endif %}',
            '#context' => [
              'title' => $perm_item['title'],
            ],
            '#wrapper_attributes' => [
              'class' => ['permission'],
            ],
          ];

          // Finally build a checkbox cell for every group role.
          foreach ($role_info as $role_name => $info) {
            // Determine whether the permission is available for this role.
            $na = $info['is_anonymous'] && !in_array('anonymous', $perm_item['allowed for']);
            $na = $na || ($info['is_outsider'] && !in_array('outsider', $perm_item['allowed for']));
            $na = $na || ($info['is_member'] && !in_array('member', $perm_item['allowed for']));

            // Show a red '-' if the permission is unavailable.
            if ($na) {
              $form["provider_$provider"]['permissions'][$perm][$role_name] = [
                '#title' => $info['label'] . ': ' . $perm_item['title'],
                '#title_display' => 'invisible',
                '#wrapper_attributes' => [
                  'class' => ['checkbox'],
                  'style' => 'color: #ff0000;',
                ],
                '#markup' => '-',
              ];
            }
            // Show a checkbox if the permissions is available.
            else {
              $form["provider_$provider"]['permissions'][$perm][$role_name] = [
                '#title' => $info['label'] . ': ' . $perm_item['title'],
                '#title_display' => 'invisible',
                '#wrapper_attributes' => [
                  'class' => ['checkbox'],
                ],
                '#type' => 'checkbox',
                '#default_value' => in_array($perm, $info['permissions']) ? 1 : 0,
                '#attributes' => [
                  'class' => [
                    'rid-' . $role_name,
                    'js-rid-' . $role_name,
                  ],
                ],
                '#parents' => [$role_name, $perm],
              ];
            }
          }
        }
      }
    }

    // Do not show relationship permissions in the UI.
    foreach ($this->cpRolesHelper->getRestrictedPermissions($this->getGroupType()) as $permission) {
      unset($form['permissions'][$permission]);
    }

    return $form;
  }

}

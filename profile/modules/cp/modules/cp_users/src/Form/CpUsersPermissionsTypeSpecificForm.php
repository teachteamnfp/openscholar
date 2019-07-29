<?php

namespace Drupal\cp_users\Form;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\cp_users\CpRolesHelperInterface;
use Drupal\group\Access\GroupPermissionHandlerInterface;
use Drupal\group\Entity\GroupTypeInterface;
use Drupal\group\Form\GroupPermissionsForm;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the cp_users permission administration form.
 *
 * It is different from \Drupal\group\Form\GroupPermissionsTypeSpecificForm
 * because it hides some special group roles from the settings.
 *
 * @see \Drupal\group\Form\GroupPermissionsTypeSpecificForm
 */
final class CpUsersPermissionsTypeSpecificForm extends GroupPermissionsForm {

  /**
   * The specific group role for this form.
   *
   * @var \Drupal\group\Entity\GroupTypeInterface
   */
  protected $groupType;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * CpRoles helper service.
   *
   * @var \Drupal\cp_users\CpRolesHelperInterface
   */
  protected $cpRolesHelper;

  /**
   * Vsite context manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * Activated vsite.
   *
   * @var \Drupal\group\Entity\GroupInterface|null
   */
  protected $activeVsite;

  /**
   * Creates a new CpUsersPermissionsTypeSpecificForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\group\Access\GroupPermissionHandlerInterface $permission_handler
   *   The group permission handler.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\cp_users\CpRolesHelperInterface $cp_roles_helper
   *   CpRoles helper service.
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager
   *   Vsite context manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, GroupPermissionHandlerInterface $permission_handler, ModuleHandlerInterface $module_handler, CpRolesHelperInterface $cp_roles_helper, VsiteContextManagerInterface $vsite_context_manager) {
    parent::__construct($permission_handler, $module_handler);
    $this->entityTypeManager = $entity_type_manager;
    $this->cpRolesHelper = $cp_roles_helper;
    $this->vsiteContextManager = $vsite_context_manager;
    $this->activeVsite = $vsite_context_manager->getActiveVsite();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('group.permissions'),
      $container->get('module_handler'),
      $container->get('cp_users.cp_roles_helper'),
      $container->get('vsite.context_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getGroupType() {
    return $this->groupType;
  }

  /**
   * {@inheritdoc}
   */
  protected function getInfo() {
    $list = [
      'role_info' => [
        '#theme' => 'item_list',
        '#items' => [
          ['#markup' => $this->t('<strong>Basic member:</strong> The default role for anyone in the group. Behaves like the "Authenticated user" role does globally.')],
        ],
      ],
    ];

    if ($this->currentUser()->hasPermission('manage default group roles')) {
      $message = $this->t('<strong>Use this <a href="@setting_link">setting</a> to edit permissions of default roles.</strong>', [
        '@setting_link' => Url::fromRoute('entity.group_type.permissions_form', [
          'group_type' => $this->groupType->id(),
        ])->toString(),
      ]);
      $list['edit_default_roles_info'] = [
        '#markup' => new FormattableMarkup("<p>$message</p>", []),
      ];
    }

    return array_merge($list, parent::getInfo());
  }

  /**
   * {@inheritdoc}
   */
  protected function getGroupRoles() {
    /** @var \Drupal\group\Entity\Storage\GroupRoleStorageInterface $group_role_storage */
    $group_role_storage = $this->entityTypeManager->getStorage('group_role');

    $query = $group_role_storage
      ->getQuery()
      ->condition('id', $this->cpRolesHelper->getNonConfigurableGroupRoles($this->activeVsite), 'NOT IN')
      ->condition('group_type', $this->groupType->id(), '=')
      ->condition('permissions_ui', 1, '=');

    return $group_role_storage->loadMultiple(array_values($query->execute()));
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param \Drupal\group\Entity\GroupTypeInterface $group_type
   *   The group type used for this form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state, GroupTypeInterface $group_type = NULL) {
    $this->groupType = $group_type;
    $form = parent::buildForm($form, $form_state);

    /** @var \Drupal\group\Plugin\GroupContentEnablerCollection $plugins */
    $plugins = $this->groupType->getInstalledContentPlugins();
    $disabled_permissions = [];

    foreach ($plugins as $plugin) {
      $plugin_id = $plugin->getPluginId();

      $disabled_permissions[] = "view $plugin_id content";
      $disabled_permissions[] = "create $plugin_id content";
      $disabled_permissions[] = "update own $plugin_id content";
      $disabled_permissions[] = "update any $plugin_id content";
      $disabled_permissions[] = "delete own $plugin_id content";
      $disabled_permissions[] = "delete any $plugin_id content";
    }

    // Prevent permission edit for default roles.
    /** @var string[] $default_roles */
    $default_roles = $this->cpRolesHelper->getDefaultGroupRoles($this->activeVsite);
    $permissions = array_keys($this->groupPermissionHandler->getPermissions(TRUE));
    foreach ($permissions as $permission) {
      foreach ($default_roles as $default_role) {
        $form['permissions'][$permission][$default_role]['#disabled'] = TRUE;
      }
    }

    // Do not show relationship permissions in the UI.
    foreach ($disabled_permissions as $permission) {
      unset($form['permissions'][$permission]);
    }

    return $form;
  }

}

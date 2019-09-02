<?php

namespace Drupal\cp_users\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\cp_users\CpRolesHelperInterface;
use Drupal\group\Entity\GroupRoleInterface;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Changes role for a member.
 */
final class ChangeRoleForm extends FormBase {

  /**
   * Vsite context manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * Active group.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $activeGroup;

  /**
   * Cp Roles Helper service.
   *
   * @var \Drupal\cp_users\CpRolesHelperInterface
   */
  protected $cpRolesHelper;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('vsite.context_manager'),
      $container->get('cp_users.cp_roles_helper')
    );
  }

  /**
   * Creates a new ChangeRoleForm object.
   *
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager
   *   Vsite context manager.
   * @param \Drupal\cp_users\CpRolesHelperInterface $cp_roles_helper
   *   Cp roles helper instacne.
   */
  public function __construct(VsiteContextManagerInterface $vsite_context_manager, CpRolesHelperInterface $cp_roles_helper) {
    $this->vsiteContextManager = $vsite_context_manager;
    $this->cpRolesHelper = $cp_roles_helper;
    $this->activeGroup = $vsite_context_manager->getActiveVsite();
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cp_users_change_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, AccountInterface $user = NULL) {
    /** @var \Drupal\group\Entity\GroupRoleInterface[] $roles */
    $roles = $this->activeGroup->getGroupType()->getRoles();
    /** @var \Drupal\group\GroupMembership $group_membership */
    $group_membership = $this->activeGroup->getMember($user);
    /** @var \Drupal\group\Entity\GroupRoleInterface[] $existing_roles */
    $existing_roles = $group_membership->getRoles();
    // It is a requirement that a member can have only one role, therefore we
    // can safely retrieve the first role.
    $existing_role = \reset($existing_roles);

    $form_state->addBuildInfo('account', $user);

    // Remove unwanted roles for vsites from the options.
    /** @var string[] $non_configurable_roles */
    $non_configurable_roles = $this->cpRolesHelper->getNonConfigurableGroupRoles($this->activeGroup);
    /** @var \Drupal\group\Entity\GroupRoleInterface[] $allowed_roles */
    $allowed_roles = array_filter($roles, static function (GroupRoleInterface $role) use ($non_configurable_roles) {
      return !\in_array($role->id(), $non_configurable_roles, TRUE) && !$role->isInternal();
    });
    foreach ($allowed_roles as $role) {
      $options[$role->id()] = $role->label();
    }

    $form['roles'] = [
      '#type' => 'radios',
      '#title' => $this->t('Roles'),
      '#options' => $options,
      '#default_value' => $existing_role->id(),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Session\AccountInterface $account */
    $account = $form_state->getBuildInfo()['account'];
    /** @var \Drupal\group\GroupMembership $group_membership */
    $group_membership = $this->activeGroup->getMember($account);
    /** @var \Drupal\group\Entity\GroupContentInterface $group_content */
    $group_content = $group_membership->getGroupContent();

    $group_content->set('group_roles', [
      'target_id' => $form_state->getValue('roles'),
    ])->save();

    $this->messenger()->addMessage($this->t('Role successfully updated.'));

    $form_state->setRedirect('cp.users');
  }

}

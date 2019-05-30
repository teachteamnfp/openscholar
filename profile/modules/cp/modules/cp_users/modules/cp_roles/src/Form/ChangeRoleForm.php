<?php

namespace Drupal\cp_roles\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('vsite.context_manager'));
  }

  /**
   * Creates a new ChangeRoleForm object.
   *
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager
   *   Vsite context manager.
   */
  public function __construct(VsiteContextManagerInterface $vsite_context_manager) {
    $this->vsiteContextManager = $vsite_context_manager;
    $this->activeGroup = $vsite_context_manager->getActiveVsite();
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cp_roles_change_form';
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

    $options = [];
    foreach ($roles as $role) {
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

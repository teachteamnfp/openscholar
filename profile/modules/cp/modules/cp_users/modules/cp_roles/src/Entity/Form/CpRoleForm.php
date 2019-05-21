<?php

namespace Drupal\cp_roles\Entity\Form;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\Form\GroupRoleForm;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Vsite role add form.
 */
class CpRoleForm extends GroupRoleForm {

  /**
   * Vsite context manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * Creates a new CpRoleForm object.
   *
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager
   *   Vsite context manager.
   */
  public function __construct(VsiteContextManagerInterface $vsite_context_manager) {
    $this->vsiteContextManager = $vsite_context_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('vsite.context_manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\group\Entity\GroupInterface|null $vsite */
    $vsite = $this->vsiteContextManager->getActiveVsite();

    // No need to make any alterations, if this is not being accessed from an
    // active vsite.
    if (!$vsite) {
      return $form;
    }

    /** @var \Drupal\group\Entity\GroupRoleInterface $group_role */
    $group_role = $this->getEntity();

    $group_role_id = '';

    // Since vsite role IDs are prefixed by the group type's ID, group ID,
    // followed by a period, we need to save some space for that.
    $subtract = \strlen($vsite->bundle()) + \strlen($vsite->id()) + 1;

    // Since machine names with periods in it are technically not allowed, we
    // strip the group type ID prefix when editing a group role.
    if ($group_role->id()) {
      [, $group_role_id] = explode('-', $group_role->id(), 2);
    }

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $group_role_id,
      '#maxlength' => EntityTypeInterface::ID_MAX_LENGTH - $subtract,
      '#machine_name' => [
        'exists' => [$this, 'exists'],
        'source' => ['label'],
      ],
      '#description' => t('A unique machine-readable name for this group role. It must only contain lowercase letters, numbers, and underscores.'),
      '#disabled' => !$group_role->isNew(),
      '#field_prefix' => "{$vsite->bundle()}-{$vsite->id()}-",
    ];

    return $form;
  }

}

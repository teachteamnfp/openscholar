<?php

namespace Drupal\os_rest\Normalizer;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\serialization\Normalizer\ContentEntityNormalizer;
use Drupal\user\Entity\Role;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;

/**
 * Replace standard normalizer with our own that returns the properties we want.
 */
class OsUserNormalizer extends ContentEntityNormalizer {

  /**
   * Vsite context manager.
   *
   * @var VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = '\Drupal\user\UserInterface';

  public function __construct(EntityManagerInterface $entity_manager, VsiteContextManagerInterface $vsiteContextManager) {
    parent::__construct($entity_manager);
    $this->vsiteContextManager = $vsiteContextManager;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = []) {
    $temp = parent::normalize($entity, $format, $context);
    /** @var \Drupal\user\UserInterface $user */
    $user = $entity;

    $output = [];
    $output['uid'] = $user->id();
    $output['name'] = $user->label();
    $output['mail'] = $user->getEmail();

    $output['first_name'] = !empty($temp['field_first_name']) ? $temp['field_first_name'][0]['value'] : '';
    $output['last_name'] = !empty($temp['field_last_name']) ? $temp['field_last_name'][0]['value'] : '';

    // TODO: Replace with real logic once we have some.
    $output['can_create_new_sites'] = TRUE;

    $output['roles'] = $user->getRoles();
    $output['permissions'] = [];
    /** @var \Drupal\user\Entity\Role $r */
    foreach ($user->getRoles() as $r) {
      $role = Role::load($r);
      $output['permissions'] = array_merge($output['permissions'], $role->getPermissions());
    }

    if ($group = $this->vsiteContextManager->getActiveVsite()) {
      $membership = $group->getMember($user);
      foreach ($membership->getRoles() as $r) {
        $output['permissions'] = array_merge($output['permissions'], $r->getPermissions());
      }
    }

    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function denormalize($data, $class, $format = NULL, array $context = []) {
    $entityData = [];
    $entityData['name'] = [['value' => $data['name']]];
    $entityData['mail'] = [['value' => $data['mail']]];
    $entityData['pass'] = [['value' => $data['pass']]];
    $entityData['field_first_name'] = [['value' => $data['field_first_name']]];
    $entityData['field_last_name'] = [['value' => $data['field_last_name']]];
    $entityData['status'] = [['value' => 1]];

    return parent::denormalize($entityData, $class, $format, $context);
  }

}

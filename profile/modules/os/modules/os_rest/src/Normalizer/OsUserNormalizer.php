<?php

namespace Drupal\os_rest\Normalizer;



use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\serialization\Normalizer\ContentEntityNormalizer;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;

class OsUserNormalizer extends ContentEntityNormalizer {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = '\Drupal\user\UserInterface';

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = []) {
    $temp = parent::normalize($entity, $format, $context);
    /** @var UserInterface $user */
    $user = $entity;

    $output = [];
    $output['uid'] = $user->id();
    $output['name'] = $user->label();
    $output['mail'] = $user->getEmail();

    $output['first_name'] = !empty($temp['field_first_name']) ? $temp['field_first_name'][0]['value'] : '';
    $output['last_name'] = !empty($temp['field_last_name']) ? $temp['field_last_name'][0]['value'] : '';

    $output['can_create_new_sites'] = true; // TODO: Replace with real logic once we have some.

    $output['roles'] = $user->getRoles();
    $output['permissions'] = [];
    /** @var Role $r */
    foreach ($user->getRoles() as $r) {
      $role = Role::load($r);
      $output['permissions'] = array_merge($output['permissions'], $role->getPermissions());
    }

    /** @var VsiteContextManagerInterface $vsiteContextManager */
    $vsiteContextManager = \Drupal::service('vsite.context_manager');
    if ($group = $vsiteContextManager->getActiveVsite()) {
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

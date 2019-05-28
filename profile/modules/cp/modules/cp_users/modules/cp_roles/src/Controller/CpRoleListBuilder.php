<?php

namespace Drupal\cp_roles\Controller;

use Drupal\Core\Config\Entity\DraggableListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\group\Entity\GroupTypeInterface;
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
   * {@inheritdoc}
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, RouteMatchInterface $route_match) {
    parent::__construct($entity_type, $storage);

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
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityIds() {
    $query = $this->getStorage()->getQuery()
      ->condition('internal', 0, '=')
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

    if ($entity->hasLinkTemplate('permissions-form')) {
      $operations['permissions'] = [
        'title' => $this->t('Edit permissions'),
        'weight' => 5,
        'url' => $entity->toUrl('permissions-form'),
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

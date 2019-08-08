<?php

namespace Drupal\vsite\Plugin\Validation\Constraint;

use Drupal\Core\Config\Entity\ConfigEntityType;
use Drupal\group\Entity\GroupContentType;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates that a field value is unique within a specific vsite.
 *
 * If the request is not in a vsite, it will check globally.
 */
class VsiteUniqueFieldValueValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    if (!$item = $items->first()) {
      return;
    }

    /** @var \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsiteContextManager */
    $vsiteContextManager = \Drupal::service('vsite.context_manager');

    $field_name = $items->getFieldDefinition()->getName();
    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $items->getEntity();
    $entity_type_id = $entity->getEntityTypeId();
    $id_key = $entity->getEntityType()->getKey('id');

    $query = \Drupal::entityQuery($entity_type_id);
    $group = $vsiteContextManager->getActiveVsite();

    // Handle vsite things.
    // How an entity is attached to a vsite differs based on whether its a config or content entity.
    if ($entity->getEntityType() instanceof ConfigEntityType) {
      if ($group) {
        $query->condition('collection', 'vsite:' . $group->id());
      }
      else {
        $query->condition('collection', '');
      }
    }
    else {
      // No join on EntityQuery, so instead we only include entities that are part of the active group.
      /** @var \Drupal\group\Entity\GroupContentType[] $plugins */
      $plugins = GroupContentType::loadByEntityTypeId($entity_type_id);
      $plugin = reset($plugins);
      if ($group) {
        $entities = $group->getContentEntities($plugin->getContentPluginId());
        $include = [];
        foreach ($entities as $et) {
          $include[] = $et->id();
        }
        if (count($include)) {
          $query->condition($entity->getEntityType()->getKey('id'), $include, 'IN');
        }
        else {
          // This vsite has no entities of the given type. It's value is valid by default.
          return;
        }
      }
    }

    $entity_id = $entity->id();
    // Using isset() instead of !empty() as 0 and '0' are valid ID values for
    // entity types using string IDs.
    if (isset($entity_id)) {
      $query->condition($id_key, $entity_id, '<>');
    }

    $value_taken = (bool) $query
      ->condition($field_name, $item->value)
      ->range(0, 1)
      ->count()
      ->execute();

    if ($value_taken) {
      $this->context->addViolation($constraint->message, [
        '%value' => $item->value,
        '@entity_type' => $entity->getEntityType()->getLowercaseLabel(),
        '@field_name' => mb_strtolower($items->getFieldDefinition()->getLabel()),
      ]);
    }
  }

}

<?php

namespace Drupal\os_publications;

use Drupal\bibcite_entity\Entity\ContributorInterface;
use Drupal\bibcite_entity\Entity\ReferenceInterface;

/**
 * RepecHelper.
 */
final class RepecHelper implements RepecHelperInterface {

  /**
   * The reference entity.
   *
   * @var \Drupal\bibcite_entity\Entity\ReferenceInterface
   */
  protected $reference;

  /**
   * RepecHelper constructor.
   *
   * @param \Drupal\bibcite_entity\Entity\ReferenceInterface $reference
   *   The reference entity.
   */
  public function __construct(ReferenceInterface $reference) {
    $this->reference = $reference;
  }

  /**
   * {@inheritdoc}
   */
  public function getContributor() : ?ContributorInterface {
    /** @var \Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem|null $item */
    $item = $this->reference->get('author')->first();
    if (!$item) {
      return NULL;
    }

    /** @var \Drupal\Core\Entity\Plugin\DataType\EntityReference $entity_reference */
    $entity_reference = $item->get('entity');
    /** @var \Drupal\Core\Entity\Plugin\DataType\EntityAdapter $entity_adapter */
    $entity_adapter = $entity_reference->getTarget();
    return $entity_adapter->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public function getKeywords() : ?array {
    // TODO: Implement getKeywords() method.
  }

}

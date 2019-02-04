<?php

namespace Drupal\os_publications\Plugin\views\field;

use Drupal\os_publications\LabelHelper;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler to display entity label excluding prepositions in beginning.
 *
 * @ViewsField("os_publications_first_letter_last_name_author")
 */
class AuthorLastNameFirstLetter extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Prevent query on this field.
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    /** @var \Drupal\bibcite_entity\Entity\ReferenceInterface $bibcite_reference */
    $bibcite_reference = $values->_entity;
    /** @var \Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem $entity_reference_item */
    $entity_reference_item = $bibcite_reference->get('author')->first();
    /** @var \Drupal\Core\Entity\Plugin\DataType\EntityAdapter $entity_adapter */
    $entity_adapter = $entity_reference_item->get('entity')->getTarget();
    /** @var \Drupal\bibcite_entity\Entity\ContributorInterface $bibcite_contributor */
    $bibcite_contributor = $entity_adapter->getValue();
    /** @var \Drupal\os_publications\LabelHelperInterface $label_helper */
    $label_helper = new LabelHelper();
    return $label_helper->convertToPublicationsListingAuthorName($this->sanitizeValue($bibcite_contributor->getLastName()));
  }

}

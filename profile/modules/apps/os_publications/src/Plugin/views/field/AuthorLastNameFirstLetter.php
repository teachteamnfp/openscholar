<?php

namespace Drupal\os_publications\Plugin\views\field;

use Drupal\os_publications\PublicationsListingHelper;
use Drupal\os_publications\PublicationsListingHelperInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field handler to display entity label excluding prepositions in beginning.
 *
 * @ViewsField("os_publications_first_letter_last_name_author")
 */
class AuthorLastNameFirstLetter extends FieldPluginBase {

  /**
   * Publications listing helper.
   *
   * @var \Drupal\os_publications\PublicationsListingHelperInterface
   */
  protected $publicationsListingHelper;

  /**
   * AuthorLastNameFirstLetter constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\os_publications\PublicationsListingHelperInterface $publications_listing_helper
   *   Publications listing helper.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PublicationsListingHelperInterface $publications_listing_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->publicationsListingHelper = $publications_listing_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('os_publications.listing_helper'));
  }

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
    return $this->publicationsListingHelper->convertAuthorName($this->sanitizeValue($bibcite_contributor->getLastName()));
  }

}

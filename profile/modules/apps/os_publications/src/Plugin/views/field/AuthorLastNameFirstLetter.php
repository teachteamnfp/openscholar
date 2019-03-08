<?php

namespace Drupal\os_publications\Plugin\views\field;

use Drupal\os_publications\PublicationsListingHelper;
use Drupal\os_publications\PublicationsListingHelperInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field handler to display first letter of contributor last name.
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
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function render(ResultRow $values) {
    /** @var \Drupal\bibcite_entity\Entity\ReferenceInterface $reference */
    $reference = $values->_entity;
    return $this->sanitizeValue($this->publicationsListingHelper->convertAuthorName($reference));
  }

}

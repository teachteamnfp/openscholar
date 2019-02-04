<?php

namespace Drupal\os_publications\Plugin\views\field;

use Drupal\os_publications\PublicationsListingHelper;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler to display entity label excluding prepositions in beginning.
 *
 * @ViewsField("os_publications_first_letter_title_excl_prep")
 */
class LabelFirstLetterExclPreposition extends FieldPluginBase {

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
    /** @var \Drupal\os_publications\PublicationsListingHelperInterface $publications_listing_helper */
    $publications_listing_helper = new PublicationsListingHelper();
    return $publications_listing_helper->convertLabel($this->sanitizeValue($values->_entity->label()));
  }

}

<?php

namespace Drupal\os_bibcite\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler to display entity label excluding prepositions in beginning.
 *
 * @ViewsField("os_bibcite_first_letter_title_excl_prep")
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
    $words_to_trim = [
      'the',
      'a',
      'an',
      'on',
      'at',
      'by',
      'for',
      'from',
      'in',
      'of',
      'off',
      'to',
      'up',
      'with',
    ];

    // TODO: Implement this.

    return $this->sanitizeValue($values->_entity->label());
  }

}

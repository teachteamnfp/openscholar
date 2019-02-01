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
      'about',
      'beside',
      'near',
      'to',
      'above',
      'between',
      'of',
      'towards',
      'across',
      'beyond',
      'off',
      'under',
      'after',
      'by',
      'on',
      'underneath',
      'against',
      'despite',
      'onto',
      'unlike',
      'along',
      'down',
      'opposite',
      'until',
      'among',
      'during',
      'out',
      'up',
      'around',
      'except',
      'outside',
      'upon',
      'as',
      'for',
      'over',
      'via',
      'at',
      'from',
      'past',
      'with',
      'before',
      'in',
      'round',
      'within',
      'behind',
      'inside',
      'since',
      'without',
      'below',
      'into',
      'than',
      'beneath',
      'like',
      'through',
    ];

    // TODO: Implement this.

    return $this->sanitizeValue($values->_entity->label());
  }

}

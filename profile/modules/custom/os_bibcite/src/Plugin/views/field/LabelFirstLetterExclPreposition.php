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
    return $this->prepareLabel($this->sanitizeValue($values->_entity->label()));
  }

  /**
   * Prepares the label.
   *
   * Converts a string like, "The Velvet Underground", to "V", i.e. it trims any
   * articles or prepositions from the beginning of the string, and returns the
   * upper case first letter of the trimmed string.
   *
   * @param string $label
   *   The label.
   *
   * @return string
   *   The altered label.
   */
  protected function prepareLabel(string $label) : string {
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

    $pattern = '/\b^(?:' . implode('|', $words_to_trim) . ')\b/i';

    return mb_strtoupper(substr(trim(preg_replace($pattern, '', $label)), 0, 1));
  }

}

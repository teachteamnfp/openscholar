<?php

namespace Drupal\os_publications;

/**
 * Class LabelHelper.
 */
final class LabelHelper implements LabelHelperInterface {

  /**
   * {@inheritdoc}
   */
  public function convertToPublicationsListingLabel(string $label) : string {
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

    return mb_strtoupper(substr(trim(preg_replace($pattern, '', mb_strtolower($label))), 0, 1));
  }

  /**
   * {@inheritdoc}
   */
  public function convertToPublicationsListingAuthorName(string $name): string {
    return mb_strtoupper(substr($name, 0, 1));
  }

}

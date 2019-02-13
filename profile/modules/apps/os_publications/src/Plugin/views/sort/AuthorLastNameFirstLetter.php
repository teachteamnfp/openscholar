<?php

namespace Drupal\os_publications\Plugin\views\sort;

use Drupal\views\Plugin\views\sort\SortPluginBase;

/**
 * Handler to sort references by first letter of contributor last name.
 *
 * @ViewsSort("os_publications_first_letter_last_name_author")
 */
class AuthorLastNameFirstLetter extends SortPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();

    $this->query->addOrderBy(NULL, "SUBSTR({$this->tableAlias}.{$this->realField}, 0, 1)", $this->options['order'], 'first_letter_last_name_author');
  }

}

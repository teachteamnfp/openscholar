<?php

namespace Drupal\os_publications;

/**
 * PublicationsListingHelperInterface.
 */
interface PublicationsListingHelperInterface {

  /**
   * Converts reference label into label used in publications listing.
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
  public function convertLabel(string $label) : string;

  /**
   * Converts contributor's last name to a name used in publications listing.
   *
   * Converts a string like, "Curtis", to "C".
   *
   * @param string $name
   *   The name to convert.
   *
   * @return string
   *   The converted name.
   */
  public function convertAuthorName(string $name) : string;

}

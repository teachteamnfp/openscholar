<?php

namespace Drupal\os_publications;

use Drupal\bibcite_entity\Entity\ReferenceInterface;
use Drupal\redirect\Entity\Redirect;

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
   * @param \Drupal\bibcite_entity\Entity\ReferenceInterface $reference
   *   The reference whose contributor will be used.
   *
   * @return string
   *   The converted name.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function convertAuthorName(ReferenceInterface $reference): string;

  /**
   * Sets a redirect according to the setting.
   *
   * The difference between this and Redirect::create() is that, this deletes
   * all existing redirects by source path before creating a new one.
   *
   * @param string $source
   *   The source path.
   * @param string $redirect
   *   The redirect setting.
   *
   * @return \Drupal\redirect\Entity\Redirect|null
   *   The redirect entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   *
   * @see \Drupal\redirect\Entity\Redirect::create()
   */
  public function setRedirect(string $source, string $redirect) : Redirect;

}

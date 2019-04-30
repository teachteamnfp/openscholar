<?php

namespace Drupal\vsite;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * App plugin interface.
 */
interface AppInterface extends PluginInspectionInterface {

  /**
   * Provide list of all content types this app controls.
   *
   * @return array
   *   List of Content Types
   */
  public function getGroupContentTypes();

  /**
   * Return the title of the app.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   Title of the app.
   */
  public function getTitle();

  /**
   * Generate the links to the creation forms.
   *
   * @return array
   *   Menu Links
   */
  public function getCreateLinks();

}

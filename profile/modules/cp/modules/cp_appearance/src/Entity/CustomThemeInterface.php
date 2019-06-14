<?php

namespace Drupal\cp_appearance\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for CustomTheme configuration entity.
 */
interface CustomThemeInterface extends ConfigEntityInterface {

  /**
   * Sets the base of the custom theme.
   *
   * @param string $theme
   *   The name of the theme.
   *
   * @return \Drupal\cp_appearance\Entity\CustomThemeInterface
   *   The custom theme this was called on.
   */
  public function setBaseTheme(string $theme): CustomThemeInterface;

  /**
   * Returns the base theme of the custom theme.
   *
   * @return string|null
   *   The theme name.
   */
  public function getBaseTheme(): ?string;

  /**
   * Returns the favicon of the custom theme.
   *
   * @return int|null
   *   The favicon file id.
   */
  public function getFavicon(): ?int;

  /**
   * Sets the favicon of the custom theme.
   *
   * @param int $favicon
   *   The favicon file id.
   *
   * @return \Drupal\cp_appearance\Entity\CustomThemeInterface
   *   The custom theme this was called on.
   */
  public function setFavicon(int $favicon): CustomThemeInterface;

  /**
   * Returns the images of the custom theme.
   *
   * @return int[]|null
   *   The image file ids.
   */
  public function getImages(): ?array;

  /**
   * Sets images for the custom theme.
   *
   * @param int[] $images
   *   The image file ids.
   *
   * @return \Drupal\cp_appearance\Entity\CustomThemeInterface
   *   The custom theme this was called on.
   */
  public function setImages(array $images): CustomThemeInterface;

}

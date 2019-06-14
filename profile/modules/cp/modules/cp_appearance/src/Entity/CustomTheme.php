<?php

namespace Drupal\cp_appearance\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the CustomTheme entity.
 *
 * @ConfigEntityType(
 *   id = "cp_custom_theme",
 *   label = @Translation("Cp Custom Theme"),
 *   handlers = {
 *     "form" = {
 *       "add" = "\Drupal\cp_appearance\Entity\Form\CustomThemeForm",
 *       "edit" = "\Drupal\cp_appearance\Entity\Form\CustomThemeForm",
 *     },
 *   },
 *   admin_permission = "manage cp appearance",
 *   config_prefix = "custom_theme",
 *   entity_keys = {
 *     "id" = "id",
 *     "base_theme" = "base_theme",
 *     "label" = "label",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "base_theme",
 *     "favicon",
 *     "images",
 *   },
 *   links = {
 *     "add-form" = "/cp/appearance/custom-themes/add",
 *     "edit-form" = "/cp/appearance/custom-themes/{cp_custom_theme}/edit"
 *   }
 * )
 */
class CustomTheme extends ConfigEntityBase implements CustomThemeInterface {

  /**
   * The machine name of the custom theme.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the custom theme.
   *
   * @var string
   */
  protected $label;

  /**
   * {@inheritdoc}
   */
  public function setBaseTheme(string $theme): CustomThemeInterface {
    $this->set('base_theme', $theme);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseTheme(): ?string {
    return $this->get('base_theme');
  }

  /**
   * {@inheritdoc}
   */
  public function getFavicon(): ?int {
    return $this->get('favicon');
  }

  /**
   * {@inheritdoc}
   */
  public function setFavicon(int $favicon): CustomThemeInterface {
    $this->set('favicon', $favicon);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getImages(): ?array {
    return $this->get('images');
  }

  /**
   * {@inheritdoc}
   */
  public function setImages(array $images): CustomThemeInterface {
    $this->set('images', $images);
    return $this;
  }

}

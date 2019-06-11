<?php

namespace Drupal\cp_appearance\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the CustomTheme entity.
 *
 * @ConfigEntityType(
 *   id = "cp_custom_theme",
 *   label = @Translation("Cp Custom Theme"),
 *   admin_permission = "manage cp appearance",
 *   config_prefix = "custom_theme",
 *   entity_keys = {
 *     "id" = "id",
 *     "base_theme" = "base_theme",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "base_theme"
 *   },
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
   * Parent theme of the custom theme.
   *
   * @var string
   */
  protected $baseTheme;

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function setBaseTheme(string $theme): CustomThemeInterface {
    $this->baseTheme = $theme;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseTheme(): string {
    return $this->baseTheme;
  }

}

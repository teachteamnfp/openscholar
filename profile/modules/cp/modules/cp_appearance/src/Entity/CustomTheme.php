<?php

namespace Drupal\cp_appearance\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\file\Entity\File;

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

  public const CUSTOM_THEMES_LOCATION = 'custom_themes';

  public const ABSOLUTE_CUSTOM_THEMES_LOCATION = DRUPAL_ROOT . '/../' . self::CUSTOM_THEMES_LOCATION;

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
   * Custom theme styles.
   *
   * @var string
   */
  protected $styles;

  /**
   * Custom theme scripts.
   *
   * @var string
   */
  protected $scripts;

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
  public function getImages(): array {
    $images = $this->get('images');

    if (empty($images)) {
      return [];
    }

    return $images;
  }

  /**
   * {@inheritdoc}
   */
  public function setImages(array $images): CustomThemeInterface {
    $this->set('images', $images);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    $custom_theme_directory_path = self::ABSOLUTE_CUSTOM_THEMES_LOCATION . '/' . $this->id();
    $custom_theme_images_path = $custom_theme_directory_path . '/images';
    $status = file_prepare_directory($custom_theme_images_path, FILE_CREATE_DIRECTORY);

    if (!$status) {
      throw new CustomThemeException(t('Unable to create directory for storing the theme. Please contact the site administrator for support.'));
    }

    // Move custom theme files.
    $favicon_id = $this->getFavicon();
    if ($favicon_id) {
      /** @var \Drupal\file\FileInterface $favicon */
      $favicon = File::load($favicon_id);
      $status = file_unmanaged_move($favicon->getFileUri(), "file://$custom_theme_directory_path/favicon.ico");

      if (!$status) {
        throw new CustomThemeException(t('Unable to place favicon in the theme. Please contact the site administrator for support.'));
      }
    }

    /** @var int[] $image_ids */
    $image_ids = $this->getImages();
    foreach ($image_ids as $id) {
      /** @var \Drupal\file\FileInterface $image */
      $image = File::load($id);
      $status = file_unmanaged_move($image->getFileUri(), "file://$custom_theme_images_path");

      if (!$status) {
        throw new CustomThemeException(t('Unable to place file %file_uri in the theme. Please contact the site administrator for support.', [
          '%file_url' => $image->getFileUri(),
        ]));
      }
    }

    // Place styles and scripts.
    $styles = $this->getStyles();
    if ($styles) {
      $status = file_unmanaged_save_data($styles, "file://$custom_theme_directory_path/style.css");

      if (!$status) {
        throw new CustomThemeException(t('Unable to place the styles. Please contact the site administrator for support.'));
      }
    }

    $scripts = $this->getScripts();
    if ($scripts) {
      $status = file_unmanaged_save_data($scripts, "file://$custom_theme_directory_path/script.js");

      if (!$status) {
        throw new CustomThemeException(t('Unable to place the scripts. Please contact the site administrator for support.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getStyles(): ?string {
    return $this->styles;
  }

  /**
   * {@inheritdoc}
   */
  public function setStyles(string $styles): CustomThemeInterface {
    $this->styles = $styles;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getScripts(): ?string {
    return $this->scripts;
  }

  /**
   * {@inheritdoc}
   */
  public function setScripts(string $scripts): CustomThemeInterface {
    $this->scripts = $scripts;
    return $this;
  }

}

<?php

namespace Drupal\cp_appearance\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\file\Entity\File;
use Symfony\Component\Yaml\Yaml;

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
 *     "images",
 *   },
 *   links = {
 *     "add-form" = "/cp/appearance/custom-themes/add",
 *     "edit-form" = "/cp/appearance/custom-themes/{cp_custom_theme}/edit"
 *   }
 * )
 */
class CustomTheme extends ConfigEntityBase implements CustomThemeInterface {

  public const CUSTOM_THEME_ID_PREFIX = 'os_ct_';

  public const CUSTOM_THEMES_LOCATION = 'custom_themes';

  public const CUSTOM_THEMES_IMAGES_LOCATION = 'images';

  public const ABSOLUTE_CUSTOM_THEMES_LOCATION = DRUPAL_ROOT . '/../' . self::CUSTOM_THEMES_LOCATION;

  public const CUSTOM_THEMES_STYLE_LOCATION = 'style.css';

  public const CUSTOM_THEMES_SCRIPT_LOCATION = 'script.js';

  public const CUSTOM_THEME_GLOBAL_STYLING_NAMESPACE = 'global-styling';

  public const CUSTOM_THEMES_DRUPAL_LOCATION = 'themes/custom_themes';

  public const CUSTOM_THEME_INFO_TEMPLATE = [
    'core' => '8.x',
    'type' => 'theme',
  ];

  public const CUSTOM_THEME_LIBRARIES_INFO_TEMPLATE = [
    self::CUSTOM_THEME_GLOBAL_STYLING_NAMESPACE => [
      'version' => 'VERSION',
      'css' => [
        'theme' => [
          self::CUSTOM_THEMES_STYLE_LOCATION => [],
        ],
      ],
    ],
  ];

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
    $custom_theme_images_path = $custom_theme_directory_path . '/' . self::CUSTOM_THEMES_IMAGES_LOCATION;
    $status = file_prepare_directory($custom_theme_images_path, FILE_CREATE_DIRECTORY);

    if (!$status) {
      throw new CustomThemeException(t('Unable to create directory for storing the theme. Please contact the site administrator for support.'));
    }

    // Move custom theme files.
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
      $status = file_unmanaged_save_data($styles, "file://$custom_theme_directory_path/" . self::CUSTOM_THEMES_STYLE_LOCATION);

      if (!$status) {
        throw new CustomThemeException(t('Unable to place the styles. Please contact the site administrator for support.'));
      }
    }

    $scripts = $this->getScripts();
    if ($scripts) {
      $status = file_unmanaged_save_data($scripts, "file://$custom_theme_directory_path/" . self::CUSTOM_THEMES_SCRIPT_LOCATION);

      if (!$status) {
        throw new CustomThemeException(t('Unable to place the scripts. Please contact the site administrator for support.'));
      }
    }

    // Place theme.libraries.yml file.
    $libraries_info = self::CUSTOM_THEME_LIBRARIES_INFO_TEMPLATE;

    if ($scripts) {
      $libraries_info[self::CUSTOM_THEME_GLOBAL_STYLING_NAMESPACE]['js'] = [
        self::CUSTOM_THEMES_SCRIPT_LOCATION => [],
      ];
    }

    $status = file_unmanaged_save_data(Yaml::dump($libraries_info), "file://$custom_theme_directory_path/{$this->id()}.libraries.yml");

    if (!$status) {
      throw new CustomThemeException(t('Unable to place theme libraries info file. Please contact the site administrator for support.'));
    }

    // Place theme.info.yml file.
    $base_info = [
      'name' => $this->label(),
      'base theme' => $this->getBaseTheme(),
      'libraries' => [
        $this->id() . '/' . self::CUSTOM_THEME_GLOBAL_STYLING_NAMESPACE,
      ],
    ];

    // system_region_list() returns region names as translatable markup.
    // It needs to be converted to plain text, otherwise it fails to be parsed
    // into file.
    /** @var array $base_theme_regions */
    $base_theme_regions = system_region_list($this->getBaseTheme());
    $formatted_regions = [];
    /** @var \Drupal\Core\StringTranslation\TranslatableMarkup $region_name */
    foreach ($base_theme_regions as $region => $region_name) {
      $formatted_regions[$region] = $region_name->__toString();
    }

    $info = array_merge($base_info, ['regions' => $formatted_regions], self::CUSTOM_THEME_INFO_TEMPLATE);

    $status = file_unmanaged_save_data(Yaml::dump($info), "file://$custom_theme_directory_path/{$this->id()}.info.yml");

    if (!$status) {
      throw new CustomThemeException(t('Unable to place theme info file. Please contact the site administrator for support.'));
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

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);

    // Cleanup custom theme files.
    foreach ($entities as $custom_theme) {
      $custom_theme_directory_path = 'file://' . self::ABSOLUTE_CUSTOM_THEMES_LOCATION . '/' . $custom_theme->id();
      file_unmanaged_delete_recursive($custom_theme_directory_path);
    }
  }

}

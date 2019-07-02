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
 *       "delete" = "\Drupal\cp_appearance\Entity\Form\CustomThemeDeleteForm",
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
      $status = file_unmanaged_copy($image->getFileUri(), "file://$custom_theme_images_path", FILE_EXISTS_REPLACE);

      if (!$status) {
        throw new CustomThemeException(t('Unable to place file %file_uri in the theme. Please contact the site administrator for support.', [
          '%file_uri' => $image->getFileUri(),
        ]));
      }
    }

    // Place styles and scripts.
    $styles = $this->getStyles();
    if ($styles) {
      $styles_location = "file://$custom_theme_directory_path/" . self::CUSTOM_THEMES_STYLE_LOCATION;
      $status = touch($styles_location);

      if (!$status) {
        throw new CustomThemeException(t('Unable to place the styles. Please contact the site administrator for support.'));
      }

      $status = file_unmanaged_save_data($styles, $styles_location, FILE_EXISTS_REPLACE);

      if (!$status) {
        throw new CustomThemeException(t('Unable to place the styles. Please contact the site administrator for support.'));
      }
    }

    $scripts = $this->getScripts();
    if ($scripts) {
      $scripts_location = "file://$custom_theme_directory_path/" . self::CUSTOM_THEMES_SCRIPT_LOCATION;
      $status = touch($scripts_location);

      if (!$status) {
        throw new CustomThemeException(t('Unable to place the scripts. Please contact the site administrator for support.'));
      }

      $status = file_unmanaged_save_data($scripts, $scripts_location, FILE_EXISTS_REPLACE);

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

    $libraries_info_location = "file://$custom_theme_directory_path/{$this->id()}.libraries.yml";
    $status = touch($libraries_info_location);

    if (!$status) {
      throw new CustomThemeException(t('Unable to place theme libraries info file. Please contact the site administrator for support.'));
    }

    $status = file_unmanaged_save_data(Yaml::dump($libraries_info), $libraries_info_location, FILE_EXISTS_REPLACE);

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

    $info_location = "file://$custom_theme_directory_path/{$this->id()}.info.yml";
    $status = touch($info_location);

    if (!$status) {
      throw new CustomThemeException(t('Unable to place theme info file. Please contact the site administrator for support.'));
    }

    $status = file_unmanaged_save_data(Yaml::dump($info), $info_location, FILE_EXISTS_REPLACE);

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
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    parent::preDelete($storage, $entities);

    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = \Drupal::configFactory();
    /** @var \Drupal\Core\Config\Config $theme_setting_mut */
    $theme_setting_mut = $config_factory->getEditable('system.theme');
    /** @var \Drupal\Core\Extension\ThemeInstallerInterface $theme_installer */
    $theme_installer = \Drupal::service('theme_installer');
    /** @var \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler */
    $theme_handler = \Drupal::service('theme_handler');
    $default_theme = $theme_setting_mut->get('default');

    // Only perform the cleanups on installed custom themes.
    // This will prevent system crash if an uninstalled theme is deleted.
    /** @var \Drupal\cp_appearance\Entity\CustomThemeInterface[] $installed_custom_themes */
    $installed_custom_themes = array_filter($entities, function (CustomThemeInterface $entity) use ($theme_handler) {
      return $theme_handler->themeExists($entity->id());
    });

    foreach ($installed_custom_themes as $custom_theme) {
      if ($custom_theme->id() === $default_theme) {
        $theme_setting_mut->set('default', $custom_theme->getBaseTheme())->save();
      }
    }

    $theme_installer->uninstall(array_keys($installed_custom_themes));
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

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();
    $this->addDependency('theme', $this->getBaseTheme());
  }

}

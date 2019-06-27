<?php

namespace Drupal\Tests\cp_appearance\Traits;

use Drupal\cp_appearance\Entity\CustomTheme;
use Drupal\cp_appearance\Entity\CustomThemeInterface;

/**
 * Provides helper methods for CpAppearance tests.
 */
trait CpAppearanceTestTrait {

  /**
   * Creates a custom theme.
   *
   * @param array $values
   *   Default values for the theme.
   *   Example:
   *   [
   *     'id' => 'my_custom_theme',
   *     'label' => 'My Custom Theme',
   *     'base_theme' => 'documental',
   *     'images' => [
   *       <file_id>,
   *     ],
   *   ]
   *   <file_id> must be a valid File entity ID.
   * @param string $styles
   *   Custom theme styles.
   * @param string $scripts
   *   Custom theme scripts.
   *
   * @return \Drupal\cp_appearance\Entity\CustomThemeInterface
   *   The new custom theme config entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createCustomTheme(array $values = [], $styles = '', $scripts = ''): CustomThemeInterface {
    /** @var \Drupal\file\FileInterface[] $image */
    $image = $this->createFile('image');

    $theme_data = $values + [
      'id' => $this->randomMachineName(),
      'label' => $this->randomMachineName(),
      'base_theme' => 'documental',
      'images' => [
        $image->id(),
      ],
    ];
    $custom_style = $styles . 'body { background-color: black; }';

    $theme = CustomTheme::create($theme_data);
    $theme->setStyles($custom_style);
    $theme->setScripts($scripts);
    $theme->save();

    /** @var \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler */
    $theme_handler = $this->container->get('theme_handler');
    $theme_handler->rebuildThemeData();

    $this->markConfigForCleanUp($theme);

    return $theme;
  }

}

<?php

namespace Drupal\Tests\cp_appearance\ExistingSite;

use Drupal\cp_appearance\Entity\CustomTheme;
use Symfony\Component\Yaml\Yaml;

/**
 * Tests custom theme entity.
 *
 * @group kernel
 * @group cp-appearance
 * @coversDefaultClass \Drupal\cp_appearance\Entity\CustomTheme
 */
class CustomThemeTest extends TestBase {

  /**
   * Tests custom theme save.
   *
   * @covers ::id
   * @covers ::label
   * @covers ::setBaseTheme
   * @covers ::getBaseTheme
   * @covers ::setFavicon
   * @covers ::getFavicon
   * @covers ::setImages
   * @covers ::getImages
   * @covers ::getStyles
   * @covers ::setStyles
   * @covers ::getScripts
   * @covers ::setScripts
   * @covers ::postSave
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testSave(): void {
    $image = $this->createFile('image');
    $favicon = $this->createFile('image', 1);

    $custom_theme = CustomTheme::create([
      'id' => 'test',
      'label' => 'Test',
    ]);
    $custom_theme->setBaseTheme('clean');
    $custom_theme->setFavicon($favicon->id());
    $custom_theme->setImages([$image->id()]);
    $custom_theme->setStyles('background-color: black;');
    $custom_theme->setScripts('alert("Hello World");');
    $custom_theme->save();

    // Assert presence of basic data.
    $this->assertEquals('test', $custom_theme->id());
    $this->assertEquals('Test', $custom_theme->label());
    $this->assertEquals('clean', $custom_theme->getBaseTheme());
    $this->assertEquals($favicon->id(), $custom_theme->getFavicon());
    $this->assertEquals([$image->id()], $custom_theme->getImages());

    // Assert presence of favicon.
    $this->assertFileExists('file://' . CustomTheme::ABSOLUTE_CUSTOM_THEMES_LOCATION . '/' . $custom_theme->id() . '/favicon.ico');

    // Assert presence of images.
    $this->assertFileExists('file://' . CustomTheme::ABSOLUTE_CUSTOM_THEMES_LOCATION . '/' . $custom_theme->id() . '/' . CustomTheme::CUSTOM_THEMES_IMAGES_LOCATION . '/' . $image->getFilename());

    // Assert presence of scripts.
    $style_file = 'file://' . CustomTheme::ABSOLUTE_CUSTOM_THEMES_LOCATION . '/' . $custom_theme->id() . '/' . CustomTheme::CUSTOM_THEMES_STYLE_LOCATION;
    $styles = file_get_contents($style_file);
    $this->assertFileExists('file://' . CustomTheme::ABSOLUTE_CUSTOM_THEMES_LOCATION . '/' . $custom_theme->id() . '/' . CustomTheme::CUSTOM_THEMES_STYLE_LOCATION);
    $this->assertEquals('background-color: black;', $styles);
    $this->assertEquals('background-color: black;', $custom_theme->getStyles());

    // Assert presence of scripts.
    $script_file = 'file://' . CustomTheme::ABSOLUTE_CUSTOM_THEMES_LOCATION . '/' . $custom_theme->id() . '/' . CustomTheme::CUSTOM_THEMES_SCRIPT_LOCATION;
    $scripts = file_get_contents($script_file);
    $this->assertFileExists($script_file);
    $this->assertEquals('alert("Hello World");', $scripts);
    $this->assertEquals('alert("Hello World");', $custom_theme->getScripts());

    // Assert theme.libraries.yml file.
    $theme_libraries_info_file_location = 'file://' . CustomTheme::ABSOLUTE_CUSTOM_THEMES_LOCATION . '/' . $custom_theme->id() . '/' . $custom_theme->id() . '.libraries.yml';
    $this->assertFileExists($theme_libraries_info_file_location);
    $theme_libraries_info_file_data = Yaml::parseFile($theme_libraries_info_file_location);
    $this->assertNotNull($theme_libraries_info_file_data[CustomTheme::CUSTOM_THEME_GLOBAL_STYLING_NAMESPACE]);
    $this->assertEquals('VERSION', $theme_libraries_info_file_data[CustomTheme::CUSTOM_THEME_GLOBAL_STYLING_NAMESPACE]['version']);
    $this->assertNotNull($theme_libraries_info_file_data[CustomTheme::CUSTOM_THEME_GLOBAL_STYLING_NAMESPACE]['css']['theme'][CustomTheme::CUSTOM_THEMES_STYLE_LOCATION]);
    $this->assertNotNull($theme_libraries_info_file_data[CustomTheme::CUSTOM_THEME_GLOBAL_STYLING_NAMESPACE]['js'][CustomTheme::CUSTOM_THEMES_SCRIPT_LOCATION]);

    // Assert theme.info.yml file.
    $theme_info_file_location = 'file://' . CustomTheme::ABSOLUTE_CUSTOM_THEMES_LOCATION . '/' . $custom_theme->id() . '/' . $custom_theme->id() . '.info.yml';
    $this->assertFileExists($theme_info_file_location);
    $theme_info_file_data = Yaml::parseFile($theme_info_file_location);
    $this->assertEquals($custom_theme->label(), $theme_info_file_data['name']);
    $this->assertEquals($custom_theme->getBaseTheme(), $theme_info_file_data['base theme']);
    $this->assertEquals([
      $custom_theme->id() . '/' . CustomTheme::CUSTOM_THEME_GLOBAL_STYLING_NAMESPACE,
    ], $theme_info_file_data['libraries']);
    $this->assertNotNull($theme_info_file_data['regions']);
    $this->assertEquals('8.x', $theme_info_file_data['core']);
    $this->assertEquals('theme', $theme_info_file_data['type']);

    $this->markConfigForCleanUp($custom_theme);
  }

}

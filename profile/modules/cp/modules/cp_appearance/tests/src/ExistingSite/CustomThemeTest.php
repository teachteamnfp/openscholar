<?php

namespace Drupal\Tests\cp_appearance\ExistingSite;

use Drupal\cp_appearance\Entity\CustomTheme;

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

    $this->assertEquals('test', $custom_theme->id());
    $this->assertEquals('Test', $custom_theme->label());
    $this->assertEquals('clean', $custom_theme->getBaseTheme());
    $this->assertEquals($favicon->id(), $custom_theme->getFavicon());
    $this->assertEquals([$image->id()], $custom_theme->getImages());
    $this->assertFileExists('file://' . CustomTheme::ABSOLUTE_CUSTOM_THEMES_LOCATION . '/' . $custom_theme->id() . '/favicon.ico');
    $this->assertFileExists('file://' . CustomTheme::ABSOLUTE_CUSTOM_THEMES_LOCATION . '/' . $custom_theme->id() . '/' . CustomTheme::CUSTOM_THEMES_IMAGES_LOCATION . '/' . $image->getFilename());
    $style_file = 'file://' . CustomTheme::ABSOLUTE_CUSTOM_THEMES_LOCATION . '/' . $custom_theme->id() . '/' . CustomTheme::CUSTOM_THEMES_STYLE_LOCATION;
    $styles = file_get_contents($style_file);
    $this->assertFileExists('file://' . CustomTheme::ABSOLUTE_CUSTOM_THEMES_LOCATION . '/' . $custom_theme->id() . '/' . CustomTheme::CUSTOM_THEMES_STYLE_LOCATION);
    $this->assertEquals('background-color: black;', $styles);
    $this->assertEquals('background-color: black;', $custom_theme->getStyles());
    $script_file = 'file://' . CustomTheme::ABSOLUTE_CUSTOM_THEMES_LOCATION . '/' . $custom_theme->id() . '/' . CustomTheme::CUSTOM_THEMES_SCRIPT_LOCATION;
    $scripts = file_get_contents($script_file);
    $this->assertFileExists($script_file);
    $this->assertEquals('alert("Hello World");', $scripts);
    $this->assertEquals('alert("Hello World");', $custom_theme->getScripts());

    $this->markConfigForCleanUp($custom_theme);
  }

}

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
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testSave(): void {
    $image = $this->createFile('image');
    $favicon = $this->createFile('image');

    $custom_theme = CustomTheme::create([
      'id' => 'test',
      'label' => 'Test',
    ]);
    $custom_theme->setBaseTheme('clean');
    $custom_theme->setFavicon($favicon->id());
    $custom_theme->setImages([$image->id()]);
    $custom_theme->save();

    $this->assertEquals('test', $custom_theme->id());
    $this->assertEquals('Test', $custom_theme->label());
    $this->assertEquals('clean', $custom_theme->getBaseTheme());
    $this->assertEquals($favicon->id(), $custom_theme->getFavicon());
    $this->assertEquals([$image->id()], $custom_theme->getImages());

    $this->markConfigForCleanUp($custom_theme);
  }

}

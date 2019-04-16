<?php

namespace Drupal\Tests\cp_appearance\ExistingSite;

/**
 * ThemeSelectorBuilderTest.
 *
 * @group kernel
 * @group other
 * @coversDefaultClass \Drupal\cp_appearance\ThemeSelectorBuilder
 */
class ThemeSelectorBuilderTest extends TestBase {

  /**
   * Theme selector builder service.
   *
   * @var \Drupal\cp_appearance\ThemeSelectorBuilderInterface
   */
  protected $themeSelectorBuilder;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->themeSelectorBuilder = $this->container->get('cp_appearance.theme_selector_builder');
  }

  /**
   * @covers ::getScreenshotUri
   */
  public function testGetScreenshotUri(): void {
    /** @var \Drupal\Core\Extension\Extension[] $installed_themes */
    $installed_themes = $this->themeHandler->listInfo();
    $vibrant_theme = $installed_themes['vibrant'];

    $screenshot_uri = $this->themeSelectorBuilder->getScreenshotUri($vibrant_theme);

    $this->assertNotNull($screenshot_uri);
    $this->assertSame('profiles/contrib/openscholar/themes/vibrant/screenshot.png', $screenshot_uri);
  }

}

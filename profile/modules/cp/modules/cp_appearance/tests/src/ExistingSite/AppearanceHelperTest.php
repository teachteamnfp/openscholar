<?php

namespace Drupal\Tests\cp_appearance\ExistingSite;

/**
 * AppearanceHelper service test.
 *
 * @group kernel
 * @group other
 * @coversDefaultClass \Drupal\cp_appearance\AppearanceHelper
 */
class AppearanceHelperTest extends TestBase {

  /**
   * Theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->themeHandler = $this->container->get('theme_handler');
  }

  /**
   * @covers ::getThemes
   */
  public function testGetThemes(): void {
    /** @var \Drupal\Core\Extension\Extension[] $themes */
    $themes = $this->appearanceHelper->getThemes();

    $this->assertFalse(isset($themes['stark']));
    $this->assertFalse(isset($themes['seven']));
    $this->assertFalse(isset($themes['os_base']));
    $this->assertFalse(isset($themes['bootstrap']));

    $this->assertTrue(isset($themes['hwpi_classic']));

    $theme = $themes['hwpi_classic'];

    $this->assertTrue(\property_exists($theme, 'is_default'));
    $this->assertTrue(\property_exists($theme, 'is_admin'));
    $this->assertTrue(\property_exists($theme, 'screenshot'));
    $this->assertTrue(\property_exists($theme, 'operations'));
    $this->assertTrue(\property_exists($theme, 'notes'));
  }

}

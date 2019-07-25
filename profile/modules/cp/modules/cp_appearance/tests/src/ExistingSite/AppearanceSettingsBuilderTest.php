<?php

namespace Drupal\Tests\cp_appearance\ExistingSite;

use Drupal\Core\Link;
use Drupal\cp_appearance\Entity\CustomTheme;

/**
 * AppearanceSettingsBuilder service test.
 *
 * @group kernel
 * @group cp-appearance
 * @coversDefaultClass \Drupal\cp_appearance\AppearanceSettingsBuilder
 */
class AppearanceSettingsBuilderTest extends TestBase {

  /**
   * Theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Default theme.
   *
   * @var string
   */
  protected $defaultTheme;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    /** @var \Drupal\Core\Config\ImmutableConfig $theme_config */
    $theme_config = $this->configFactory->get('system.theme');
    $this->defaultTheme = $theme_config->get('default');
    $this->themeHandler = $this->container->get('theme_handler');
  }

  /**
   * @covers ::getFeaturedThemes
   * @covers ::osInstalledThemes
   * @covers ::prepareThemes
   * @covers ::addScreenshotInfo
   * @covers ::addOperations
   * @covers ::addMoreOperations
   * @covers ::addNotes
   */
  public function testFeaturedThemes(): void {
    /** @var \Drupal\Core\Config\Config $theme_config_mut */
    $theme_config_mut = $this->configFactory->getEditable('system.theme');
    $theme_config_mut->set('default', 'hwpi_classic')->save();

    /** @var \Drupal\Core\Extension\Extension[] $themes */
    $themes = $this->appearanceSettingsBuilder->getFeaturedThemes();

    $this->assertFalse(isset($themes['stark']));
    $this->assertFalse(isset($themes['seven']));
    $this->assertFalse(isset($themes['os_base']));
    $this->assertFalse(isset($themes['bootstrap']));

    $this->assertTrue(isset($themes['hwpi_classic']));

    // Test presence of custom properties.
    $active_theme = $themes['hwpi_classic'];

    $this->assertTrue(property_exists($active_theme, 'is_default'));
    $this->assertTrue(property_exists($active_theme, 'is_admin'));
    $this->assertTrue(property_exists($active_theme, 'screenshot'));
    $this->assertTrue(property_exists($active_theme, 'operations'));
    $this->assertTrue(property_exists($active_theme, 'notes'));

    // Test screenshot info.
    $screenshot_info = $active_theme->screenshot;

    $this->assertNotNull($screenshot_info);
    $this->assertSame('profiles/contrib/openscholar/themes/hwpi_classic/screenshot.png', $screenshot_info['uri']);
    $this->assertSame('Screenshot for Conservative theme', $screenshot_info['alt']->__toString());
    $this->assertSame('Screenshot for Conservative theme', $screenshot_info['title']->__toString());
    $this->assertTrue(isset($screenshot_info['attributes']));

    // Test operations.
    $inactive_theme = $themes['hwpi_college'];

    $this->assertCount(2, $inactive_theme->operations);
    $operations = $inactive_theme->operations[0];
    $this->assertTrue(isset($operations['title']));
    $this->assertTrue(isset($operations['url']));
    $this->assertTrue(isset($operations['attributes']));

    $this->assertCount(0, $active_theme->operations);

    // Test more operations.
    $theme = $themes['vibrant'];
    $this->assertGreaterThan(0, \count($theme->more_operations));
    $more_operations = $theme->more_operations[0];
    $this->assertEquals($more_operations['#type'], 'form');
    // Custom theme should not appear as flavor.
    $this->assertCount(0, $themes['documental']->more_operations);

    // Test notes.
    $this->assertCount(0, $inactive_theme->notes);

    $this->assertCount(1, $active_theme->notes);
    $notes = $active_theme->notes[0];
    $this->assertEquals('current theme', $notes);
  }

  /**
   * Flavors should not appear in the list.
   */
  public function testNoFlavors(): void {
    /** @var \Drupal\Core\Extension\Extension[] $themes */
    $themes = $this->appearanceSettingsBuilder->getFeaturedThemes();
    /** @var array $sub_themes */
    $sub_themes = $themes['vibrant']->sub_themes;

    foreach ($sub_themes as $key => $value) {
      $this->assertFalse(isset($themes[$key]));
    }
  }

  /**
   * @covers ::themeIsDefault
   */
  public function testThemeIsDefault(): void {
    /** @var \Drupal\Core\Extension\Extension[] $installed_themes */
    $installed_themes = $this->themeHandler->listInfo();
    /** @var \Drupal\Core\Config\Config $theme_config_mut */
    $theme_config_mut = $this->configFactory->getEditable('system.theme');

    // When flavor is set as default.
    $theme_config_mut->set('default', 'adams')->save();
    $this->assertTrue($this->appearanceSettingsBuilder->themeIsDefault($installed_themes['hwpi_college']));

    // When theme is set as default.
    $theme_config_mut->set('default', 'vibrant')->save();
    $this->assertTrue($this->appearanceSettingsBuilder->themeIsDefault($installed_themes['vibrant']));
    $this->assertFalse($this->appearanceSettingsBuilder->themeIsDefault($installed_themes['hwpi_college']));
  }

  /**
   * Tests the screenshot uri when flavor is set as default.
   *
   * @covers ::addScreenshotInfo
   */
  public function testFlavorScreenshotInfo(): void {
    /** @var \Drupal\Core\Config\Config $theme_config_mut */
    $theme_config_mut = $this->configFactory->getEditable('system.theme');
    $theme_config_mut->set('default', 'loeb')->save();

    /** @var \Drupal\Core\Extension\Extension[] $themes */
    $themes = $this->appearanceSettingsBuilder->getFeaturedThemes();

    $active_theme = $themes['hwpi_sterling'];
    $screenshot_info = $active_theme->screenshot;

    $this->assertNotNull($screenshot_info);
    $this->assertSame('profiles/contrib/openscholar/themes/loeb/screenshot.png', $screenshot_info['uri']);
    $this->assertSame('Screenshot for Loeb theme', $screenshot_info['alt']->__toString());
    $this->assertSame('Screenshot for Loeb theme', $screenshot_info['title']->__toString());
    $this->assertTrue(isset($screenshot_info['attributes']));
  }

  /**
   * @covers ::getCustomThemes
   * @covers ::prepareThemes
   * @covers ::addScreenshotInfo
   * @covers ::addOperations
   * @covers ::addMoreOperations
   * @covers ::addNotes
   */
  public function testCustomThemes(): void {
    $custom_theme_entity_1 = CustomTheme::load(self::TEST_CUSTOM_THEME_1_NAME);
    $custom_theme_entity_2 = CustomTheme::load(self::TEST_CUSTOM_THEME_2_NAME);

    /** @var \Drupal\Core\Config\Config $theme_config_mut */
    $theme_config_mut = $this->configFactory->getEditable('system.theme');
    $theme_config_mut->set('default', $custom_theme_entity_1->id())->save();

    /** @var \Drupal\Core\Extension\Extension[] $custom_themes */
    $custom_themes = $this->appearanceSettingsBuilder->getCustomThemes();

    $this->assertFalse(isset($custom_themes['hwpi_classic']));
    $this->assertTrue(isset($custom_themes[$custom_theme_entity_1->id()]));

    // Test presence of custom properties.
    $active_theme = $custom_themes[$custom_theme_entity_1->id()];

    $this->assertTrue(property_exists($active_theme, 'is_default'));
    $this->assertTrue(property_exists($active_theme, 'is_admin'));
    $this->assertTrue(property_exists($active_theme, 'screenshot'));
    $this->assertTrue(property_exists($active_theme, 'operations'));
    $this->assertTrue(property_exists($active_theme, 'notes'));

    // Test screenshot info.
    $screenshot_info = $active_theme->screenshot;

    $this->assertNotNull($screenshot_info);
    $this->assertSame('profiles/contrib/openscholar/themes/documental/screenshot.png', $screenshot_info['uri']);
    $this->assertSame("Screenshot for {$custom_theme_entity_1->label()} theme", $screenshot_info['alt']->__toString());
    $this->assertSame("Screenshot for {$custom_theme_entity_1->label()} theme", $screenshot_info['title']->__toString());
    $this->assertTrue(isset($screenshot_info['attributes']));

    // Test operations.
    $inactive_theme = $custom_themes[$custom_theme_entity_2->id()];

    $this->assertCount(2, $inactive_theme->operations);
    $operations = $inactive_theme->operations[0];
    $this->assertTrue(isset($operations['title']));
    $this->assertTrue(isset($operations['url']));
    $this->assertTrue(isset($operations['attributes']));

    $this->assertCount(0, $active_theme->operations);

    // Test more operations.
    $theme = $custom_themes[$custom_theme_entity_1->id()];
    $this->assertGreaterThan(0, \count($theme->more_operations));

    /** @var \Drupal\Core\Link $edit_operation */
    $edit_operation = $theme->more_operations[0];
    $edit_url = $edit_operation->getUrl();
    $this->assertInstanceOf(Link::class, $edit_operation);
    $this->assertEquals('entity.cp_custom_theme.edit_form', $edit_url->getRouteName());

    /** @var \Drupal\Core\Link $delete_operation */
    $delete_operation = $theme->more_operations[1];
    $delete_url = $delete_operation->getUrl();
    $this->assertInstanceOf(Link::class, $delete_operation);
    $this->assertEquals('entity.cp_custom_theme.delete_form', $delete_url->getRouteName());

    // Test notes.
    $this->assertCount(0, $inactive_theme->notes);

    $this->assertCount(1, $active_theme->notes);
    $notes = $active_theme->notes[0];
    $this->assertEquals('current theme', $notes);
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    parent::tearDown();
    /** @var \Drupal\Core\Config\Config $theme_config_mut */
    $theme_config_mut = $this->configFactory->getEditable('system.theme');
    $theme_config_mut->set('default', $this->defaultTheme)->save();
  }

}

<?php

namespace Drupal\Tests\cp_appearance\ExistingSite;

use Drupal\cp_appearance\Entity\CustomTheme;

/**
 * Tests appearance settings for custom themes.
 *
 * @group functional
 * @group cp-appearance
 * @coversDefaultClass \Drupal\cp_appearance\Controller\CpAppearanceMainController
 */
class CustomThemeAppearanceSettingsTest extends TestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->addGroupAdmin($this->groupAdmin, $this->group);
    $this->drupalLogin($this->groupAdmin);
  }

  /**
   * Tests whether custom themes are in settings page, with all the options.
   *
   * @covers ::main
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function testVisibility(): void {
    $this->visitViaVsite('cp/appearance/themes', $this->group);

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseContains('#system-themes-list--custom_theme');
    $this->assertSession()->pageTextContains('Cp Appearance Test 1 theme');
    $this->assertSession()->linkByHrefExists("{$this->groupAlias}/cp/appearance/themes/set/os_ct_cp_appearance_test_1");
    $this->assertSession()->linkByHrefExists("{$this->groupAlias}/cp/appearance/preview/os_ct_cp_appearance_test_1");
    $this->assertSession()->pageTextContains('Cp Appearance Test 2 theme');
    $this->assertSession()->linkByHrefExists("{$this->groupAlias}/cp/appearance/themes/set/os_ct_cp_appearance_test_2");
    $this->assertSession()->linkByHrefExists("{$this->groupAlias}/cp/appearance/preview/os_ct_cp_appearance_test_2");
  }

  /**
   * Tests appearance change.
   *
   * @covers ::main
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testSave(): void {
    $this->visitViaVsite('cp/appearance/themes', $this->group);

    $this->getCurrentPage()->selectFieldOption('theme', self::TEST_CUSTOM_THEME_1_NAME);
    $this->getCurrentPage()->pressButton('Save Theme');

    $this->visitViaVsite('', $this->group);
    $this->assertSession()->responseContains('/themes/custom_themes/' . self::TEST_CUSTOM_THEME_1_NAME . '/style.css');

    $this->visit('/');
  }

  /**
   * @covers ::setTheme
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testSetDefault(): void {
    $this->visitViaVsite('cp/appearance/themes/set/' . self::TEST_CUSTOM_THEME_1_NAME, $this->group);

    $this->assertSession()->statusCodeEquals(200);

    $this->visitViaVsite('', $this->group);
    $this->assertSession()->responseContains('/themes/custom_themes/' . self::TEST_CUSTOM_THEME_1_NAME . '/style.css');

    $this->visit('/');
  }

  /**
   * @covers ::previewTheme
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function testStartPreview(): void {
    $custom_theme_entity_1 = CustomTheme::load(self::TEST_CUSTOM_THEME_1_NAME);
    $custom_theme_entity_2 = CustomTheme::load(self::TEST_CUSTOM_THEME_2_NAME);

    $this->visitViaVsite('cp/appearance/preview/' . self::TEST_CUSTOM_THEME_1_NAME, $this->group);

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains("Previewing: {$custom_theme_entity_1->label()}");

    $this->visitViaVsite('cp/appearance/preview/' . self::TEST_CUSTOM_THEME_2_NAME, $this->group);

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains("Previewing: {$custom_theme_entity_2->label()}");

    $this->visit('/');
  }

}

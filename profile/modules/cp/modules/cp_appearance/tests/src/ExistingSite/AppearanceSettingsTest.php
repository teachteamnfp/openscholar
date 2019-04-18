<?php

namespace Drupal\Tests\cp_appearance\ExistingSite;

/**
 * AppearanceSettingsTest.
 *
 * @group functional
 * @coversDefaultClass \Drupal\cp_appearance\Controller\CpAppearanceMainController
 */
class AppearanceSettingsTest extends TestBase {

  /**
   * Test group.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $group;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->group = $this->createGroup([
      'path' => [
        'alias' => '/cp-appearance',
      ],
    ]);
    $this->group->addMember($this->admin);

    $this->drupalLogin($this->admin);
  }

  /**
   * Tests appearance change.
   *
   * @covers ::main
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function testSave(): void {
    $this->visit('/cp-appearance/cp/appearance');

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Select Theme');

    $this->getCurrentPage()->selectFieldOption('theme', 'hwpi_lamont');
    $this->getCurrentPage()->pressButton('Save Theme');

    $this->visit('/cp-appearance');
    $this->assertSession()->responseContains('/profiles/contrib/openscholar/themes/hwpi_lamont/css/style.css');
  }

  /**
   * @covers ::setTheme
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testSetDefault(): void {
    $this->visit('/cp-appearance/cp/appearance/set/hwpi_college');

    $this->assertSession()->statusCodeEquals(200);

    $theme_setting = $this->configFactory->get('system.theme');
    $this->assertEquals('hwpi_college', $theme_setting->get('default'));
  }

  /**
   * @covers ::previewTheme
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function testStartPreview(): void {
    $this->visit('/cp-appearance/cp/appearance/preview/vibrant');

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Previewing: Vibrant');

    $this->visit('/cp-appearance/cp/appearance/preview/hwpi_sterling');

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Previewing: Sterling');
  }

}

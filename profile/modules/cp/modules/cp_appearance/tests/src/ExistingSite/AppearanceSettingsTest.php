<?php

namespace Drupal\Tests\cp_appearance\ExistingSite;

/**
 * AppearanceSettingsTest.
 *
 * @group functional
 * @coversDefaultClass \Drupal\cp_appearance\Form\ThemeForm
 * @coversDefaultClass \Drupal\cp_appearance\Controller\CpAppearanceMainController
 */
class AppearanceSettingsTest extends TestBase {

  /**
   * Administrator.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $admin;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->admin = $this->createUser([], NULL, TRUE);
  }

  /**
   * Tests appearance change.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testSave(): void {
    $group = $this->createGroup([
      'path' => [
        'alias' => '/appearance-test-save',
      ],
    ]);
    $this->drupalLogin($this->admin);

    $this->vsiteContextManager->activateVsite($group);
    $this->visit('/appearance-test-save/cp/appearance');

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Select Theme');

    $this->getCurrentPage()->selectFieldOption('theme', 'hwpi_lamont');
    $this->getCurrentPage()->pressButton('Save Theme');

    $theme_setting = $this->configFactory->get('system.theme');
    $this->assertEquals('hwpi_lamont', $theme_setting->get('default'));
  }

}

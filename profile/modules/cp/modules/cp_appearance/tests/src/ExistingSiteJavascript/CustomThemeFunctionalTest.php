<?php

namespace Drupal\Tests\cp_appearance\ExistingSiteJavascript;

use Drupal\cp_appearance\Entity\CustomTheme;
use Drupal\Tests\cp_appearance\Traits\CpAppearanceTestTrait;
use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * Tests custom theme creation via UI.
 *
 * @coversDefaultClass \Drupal\cp_appearance\Entity\Form\CustomThemeForm
 */
class CustomThemeFunctionalTest extends OsExistingSiteJavascriptTestBase {

  use CpAppearanceTestTrait;

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

    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $this->container->get('config.factory');
    /** @var \Drupal\Core\Config\ImmutableConfig $system_theme */
    $system_theme = $config_factory->get('system.theme');
    $this->defaultTheme = $system_theme->get('default');
  }

  /**
   * Tests custom theme save.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testSave(): void {
    // Setup.
    $group_admin = $this->createUser();
    $this->addGroupAdmin($group_admin, $this->group);
    $this->drupalLogin($group_admin);

    // Tests.
    $this->visitViaVsite('cp/appearance/custom-themes/add', $this->group);
    $this->getSession()->getPage()->fillField('Custom Theme Name', 'Cyberpunk');
    $this->assertSession()->waitForElementVisible('css', '.machine-name-value');
    $this->getSession()->getPage()->selectFieldOption('Parent Theme', 'clean');
    $this->getSession()->getPage()->findField('styles')->setValue('body { color: black; }');
    $this->getSession()->getPage()->findField('scripts')->setValue('alert("Hello World")');
    $this->getSession()->getPage()->pressButton('Save');

    // TODO: Test that styles and scripts have been created.
    /** @var \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager */
    $vsite_context_manager = $this->container->get('vsite.context_manager');
    $vsite_context_manager->activateVsite($this->group);
    /** @var \Drupal\cp_appearance\Entity\CustomThemeInterface $custom_theme */
    $custom_theme = CustomTheme::load('cyberpunk');
    $this->assertNotNull($custom_theme);
    $this->assertEquals('Cyberpunk', $custom_theme->label());
    $this->assertEquals('clean', $custom_theme->getBaseTheme());

    // Clean up.
    $custom_theme->delete();
  }

  /**
   * Tests whether the custom theme is installable.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testInstallable(): void {
    // Setup.
    $custom_theme = $this->createCustomTheme([], '', 'test');

    $admin = $this->createUser([
      'administer themes',
    ], NULL, TRUE);
    $this->drupalLogin($admin);
    $this->visit('/admin/appearance');

    $this->assertSession()->pageTextContains($custom_theme->label());

    /** @var \Behat\Mink\Element\NodeElement|null $set_default_theme_link */
    $set_default_theme_link = $this->getSession()->getPage()->find('css', "[href*=\"/admin/appearance/default?theme={$custom_theme->id()}\"]");
    $this->assertNotNull($set_default_theme_link);
    $set_default_theme_link->click();

    // Tests.
    $this->visit('/');
    $this->assertSession()->responseContains("/themes/custom_themes/{$custom_theme->id()}/style.css");
    $this->assertSession()->responseContains("/themes/custom_themes/{$custom_theme->id()}/script.js");
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $this->container->get('config.factory');
    /** @var \Drupal\Core\Config\Config $system_theme_mut */
    $system_theme_mut = $config_factory->getEditable('system.theme');
    $system_theme_mut->set('default', $this->defaultTheme);
    $system_theme_mut->save();

    parent::tearDown();
  }

}

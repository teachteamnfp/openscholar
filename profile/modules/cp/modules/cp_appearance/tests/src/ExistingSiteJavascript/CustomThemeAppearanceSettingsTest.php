<?php

namespace Drupal\Tests\cp_appearance\ExistingSiteJavascript;

use Drupal\cp_appearance\Entity\CustomTheme;

/**
 * Tests appearance settings for custom themes.
 *
 * @group functional-javascript
 * @group cp-appearance
 * @coversDefaultClass \Drupal\cp_appearance\Controller\CpAppearanceMainController
 */
class CustomThemeAppearanceSettingsTest extends CpAppearanceExistingSiteJavascriptTestBase {

  /**
   * Tests whether vsite custom theme appear after site cache clear.
   *
   * @covers ::main
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testCacheClearVisibility(): void {
    // Setup.
    $group_admin = $this->createUser();
    $this->addGroupAdmin($group_admin, $this->group);
    $this->drupalLogin($group_admin);
    $custom_theme_label = strtolower($this->randomMachineName());

    $this->visitViaVsite('cp/appearance/custom-themes/add', $this->group);
    $this->getSession()->getPage()->fillField('Custom Theme Name', $custom_theme_label);
    $this->assertSession()->waitForElementVisible('css', '.machine-name-value');
    $this->getSession()->getPage()->selectFieldOption('Parent Theme', 'clean');
    $this->getSession()->getPage()->findField('styles')->setValue('body { color: black; }');
    $this->getSession()->getPage()->findField('scripts')->setValue('alert("Hello World")');
    $this->getSession()->getPage()->pressButton('Save');
    $this->getSession()->getPage()->pressButton('Confirm');

    // Tests.
    $this->visit('/');
    /** @var \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler */
    $theme_handler = $this->container->get('theme_handler');
    $theme_handler->refreshInfo();

    $this->visitViaVsite('cp/appearance/themes', $this->group);

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseContains('#system-themes-list--custom_theme');
    $this->assertSession()->pageTextContains($custom_theme_label);

    // Cleanup.
    /** @var \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager */
    $vsite_context_manager = $this->container->get('vsite.context_manager');
    $vsite_context_manager->activateVsite($this->group);
    /** @var \Drupal\cp_appearance\Entity\CustomThemeInterface $custom_theme */
    $custom_theme = CustomTheme::load(CustomTheme::CUSTOM_THEME_ID_PREFIX . $custom_theme_label);
    $custom_theme->delete();
  }

}

<?php

namespace Drupal\Tests\cp_appearance\ExistingSiteJavascript;

use Drupal\cp_appearance\Entity\CustomTheme;
use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * Tests custom theme creation via UI.
 *
 * @coversDefaultClass \Drupal\cp_appearance\Entity\Form\CustomThemeForm
 */
class CustomThemeFunctionalTest extends OsExistingSiteJavascriptTestBase {

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
    $this->assertEquals('clean', $custom_theme->get('base_theme'));

    // Clean up.
    $custom_theme->delete();
  }

}

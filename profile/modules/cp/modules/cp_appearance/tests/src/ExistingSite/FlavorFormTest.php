<?php

namespace Drupal\Tests\cp_appearance\ExistingSite;

use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * FlavorFormTest.
 *
 * @group functional-javascript
 * @coversDefaultClass \Drupal\cp_appearance\Form\FlavorForm
 */
class FlavorFormTest extends OsExistingSiteJavascriptTestBase {

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

    /** @var \Drupal\Core\Config\Config $theme_config_mut */
    $theme_config_mut = $this->container->get('config.factory')->getEditable('system.theme');
    $theme_config_mut->set('default', 'vibrant');
    $theme_config_mut->save();

    $this->group = $this->createGroup([
      'path' => [
        'alias' => '/cp-appearance-flavor',
      ],
    ]);
    $admin = $this->createAdminUser();

    $this->group->addMember($admin);

    $this->drupalLogin($admin);
    $this->container->get('vsite.context_manager')->activateVsite($this->group);
  }

  /**
   * Tests whether flavor option appears for default theme.
   *
   * @covers ::buildForm
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testFlavorForDefaultTheme(): void {
    $this->visit('/cp-appearance-flavor/cp/appearance');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementExists('css', 'form.cp-appearance-vibrant-flavor-form select');
  }

  /**
   * Tests preview update on flavor selection.
   *
   * @covers ::updatePreview
   *
   * @throws \Behat\Mink\Exception\DriverException
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\UnsupportedDriverActionException
   */
  public function testUpdatePreview(): void {
    $this->visit('/cp-appearance-flavor/cp/appearance');

    $this->getCurrentPage()->fillField('options_vibrant', 'golden_accents');
    $this->waitForAjaxToFinish();

    $this->assertSession()->responseContains('/profiles/contrib/openscholar/themes/golden_accents/screenshot.png');
  }

}

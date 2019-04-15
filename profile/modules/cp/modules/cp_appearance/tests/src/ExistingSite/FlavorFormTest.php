<?php

namespace Drupal\Tests\cp_appearance\ExistingSite;

/**
 * FlavorFormTest.
 *
 * @group functional
 * @coversDefaultClass \Drupal\cp_appearance\Form\FlavorForm
 */
class FlavorFormTest extends TestBase {

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
    $theme_config_mut = $this->configFactory->getEditable('system.theme');
    $theme_config_mut->set('default', 'vibrant');
    $theme_config_mut->save();

    $this->group = $this->createGroup([
      'path' => [
        'alias' => '/cp-appearance-flavor',
      ],
    ]);
    $this->group->addMember($this->admin);

    $this->drupalLogin($this->admin);
    $this->vsiteContextManager->activateVsite($this->group);
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

}

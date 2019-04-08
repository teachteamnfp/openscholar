<?php

namespace Drupal\Tests\os_theme_preview\ExistingSite;

/**
 * Tests preview action form.
 *
 * @group functional
 * @coversDefaultClass \Drupal\os_theme_preview\Form\PreviewAction
 */
class PreviewActionTest extends TestBase {

  /**
   * Administrator.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $admin;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->admin = $this->createUser([], NULL, TRUE);
    $this->configFactory = $this->container->get('config.factory');
  }

  /**
   * Test form visibility.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function testVisibility() {
    $this->drupalLogin($this->admin);

    $this->visit('/admin/appearance');
    $this->getCurrentPage()->pressButton('Preview');

    $this->assertSession()->pageTextContains('Previewing: Bentley Conservative');
  }

  /**
   * Test save action.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  public function testSave() {
    $this->drupalLogin($this->admin);

    $this->visit('/admin/appearance');
    $this->getCurrentPage()->pressButton('Preview');
    $this->getCurrentPage()->pressButton('Save');

    /** @var \Drupal\Core\Config\ImmutableConfig $theme_config */
    $theme_config = $this->configFactory->get('system.theme');

    $this->assertSame('hwpi_themeone_bentley', $theme_config->get('default'));
  }

}

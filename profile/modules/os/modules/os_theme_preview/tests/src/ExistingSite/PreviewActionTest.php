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
   * Theme configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $themeConfig;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->admin = $this->createUser([], NULL, TRUE);
    $this->configFactory = $this->container->get('config.factory');
    $this->themeConfig = $this->configFactory->get('system.theme');
  }

  /**
   * Test form visibility.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function testVisibility(): void {
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
  public function testSave(): void {
    $this->drupalLogin($this->admin);

    $this->visit('/admin/appearance');
    $this->getCurrentPage()->pressButton('Preview');
    $this->getCurrentPage()->pressButton('Save');

    /** @var \Drupal\Core\Config\ImmutableConfig $theme_config */
    $theme_config = $this->configFactory->get('system.theme');

    $this->assertSame('hwpi_themeone_bentley', $theme_config->get('default'));
  }

  /**
   * Test cancel action.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  public function testCancel(): void {
    $this->drupalLogin($this->admin);

    $this->visit('/admin/appearance');
    $this->getCurrentPage()->pressButton('Preview');
    $this->getCurrentPage()->pressButton('Cancel');

    /** @var \Drupal\Core\Config\ImmutableConfig $actual_theme_config */
    $actual_theme_config = $this->configFactory->get('system.theme');

    $this->assertSame($this->themeConfig->get('default'), $actual_theme_config->get('default'));
  }

  /**
   * Tests back button.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  public function testBack(): void {
    $this->drupalLogin($this->admin);

    $this->visit('/admin/appearance');
    $this->getCurrentPage()->pressButton('Preview');
    $this->getCurrentPage()->pressButton('Back to themes');

    /** @var \Drupal\Core\Config\ImmutableConfig $actual_theme_config */
    $actual_theme_config = $this->configFactory->get('system.theme');

    $this->assertSame($this->themeConfig->get('default'), $actual_theme_config->get('default'));
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    /** @var \Drupal\Core\Config\Config $theme_config_mut */
    $theme_config_mut = $this->configFactory->getEditable('system.theme');
    $theme_config_mut->set('default', $this->themeConfig->get('default'));
    parent::tearDown();
  }

}

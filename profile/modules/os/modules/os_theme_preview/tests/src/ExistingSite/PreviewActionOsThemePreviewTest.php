<?php

namespace Drupal\Tests\os_theme_preview\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;
use Drupal\Tests\os_theme_preview\Traits\ThemePreviewTestTrait;

/**
 * Tests preview action form.
 *
 * @group functional
 * @group os-theme-preview
 * @coversDefaultClass \Drupal\os_theme_preview\Form\PreviewAction
 */
class PreviewActionOsThemePreviewTest extends OsExistingSiteTestBase {

  use ThemePreviewTestTrait;

  /**
   * Group administrator.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $groupAdmin;

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

    $this->groupAdmin = $this->createUser();
    $this->configFactory = $this->container->get('config.factory');
    $this->themeConfig = $this->configFactory->get('system.theme');
    $this->group = $this->createGroup([
      'path' => [
        'alias' => '/os-theme-preview',
      ],
    ]);
    $this->addGroupAdmin($this->groupAdmin, $this->group);

    $this->drupalLogin($this->groupAdmin);
  }

  /**
   * Test form visibility.
   *
   * @covers ::buildForm
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function testVisibility(): void {
    $this->visit('/os-theme-preview/cp/appearance/preview/documental');

    $this->assertSession()->pageTextContains('Previewing: Documental');
  }

  /**
   * Test save action.
   *
   * @covers ::buildForm
   * @covers ::submitForm
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testSave(): void {
    $this->visit('/os-theme-preview/cp/appearance/preview/documental');
    $this->getCurrentPage()->pressButton('Save');

    $this->visit('/os-theme-preview');
    $this->assertSession()->responseContains('/profiles/contrib/openscholar/themes/documental/css/style.css');

    $this->visit('/');
  }

  /**
   * Test cancel action.
   *
   * @covers ::buildForm
   * @covers ::cancelPreview
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  public function testCancel(): void {
    $this->visit('/os-theme-preview/cp/appearance/preview/documental');
    $this->getCurrentPage()->pressButton('Cancel');

    /** @var \Drupal\Core\Config\ImmutableConfig $actual_theme_config */
    $actual_theme_config = $this->configFactory->get('system.theme');

    $this->assertSame($this->themeConfig->get('default'), $actual_theme_config->get('default'));
  }

  /**
   * Tests back button.
   *
   * @covers ::buildForm
   * @covers ::cancelPreview
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  public function testBack(): void {
    $this->visit('/os-theme-preview/cp/appearance/preview/documental');
    $this->getCurrentPage()->pressButton('Back to themes');

    /** @var \Drupal\Core\Config\ImmutableConfig $actual_theme_config */
    $actual_theme_config = $this->configFactory->get('system.theme');

    $this->assertSame($this->themeConfig->get('default'), $actual_theme_config->get('default'));
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    parent::tearDown();
    /** @var \Drupal\Core\Config\Config $theme_config_mut */
    $theme_config_mut = $this->configFactory->getEditable('system.theme');
    $theme_config_mut->set('default', $this->themeConfig->get('default'))->save();
  }

}

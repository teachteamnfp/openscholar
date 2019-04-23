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

    $this->admin = $this->createUser([], NULL, TRUE);
    $this->configFactory = $this->container->get('config.factory');
    $this->themeConfig = $this->configFactory->get('system.theme');
    $this->group = $this->createGroup([
      'path' => [
        'alias' => '/os-theme-preview',
      ],
    ]);
    $this->group->addMember($this->admin);

    $this->vsiteContextManager->activateVsite($this->group);
    $this->drupalLogin($this->admin);
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
   */
  public function testSave(): void {
    $this->visit('/os-theme-preview/cp/appearance/preview/documental');
    $this->getCurrentPage()->pressButton('Save');

    $this->visit('/os-theme-preview');
    $this->assertSession()->responseContains('/profiles/contrib/openscholar/themes/documental/css/style.css');
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
    /** @var \Drupal\Core\Config\Config $theme_config_mut */
    $theme_config_mut = $this->configFactory->getEditable('system.theme');
    $theme_config_mut->set('default', $this->themeConfig->get('default'));
    parent::tearDown();
  }

}

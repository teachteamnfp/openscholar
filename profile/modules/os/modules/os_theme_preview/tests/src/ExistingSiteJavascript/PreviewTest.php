<?php

namespace Drupal\Tests\os_theme_preview\ExistingSiteJavascript;

/**
 * Theme preview test.
 *
 * @group functional-javascript
 * @group os-theme-preview
 * @coversDefaultClass \Drupal\os_theme_preview\Theme\Negotiator
 */
class PreviewTest extends TestBase {

  /**
   * Administrator user.
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
   * Asserts that preview enabled for one vsite does not appear in another.
   *
   * @covers ::applies
   * @covers ::determineActiveTheme
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\os_theme_preview\ThemePreviewException
   */
  public function testMultipleVsite(): void {
    $this->drupalLogin($this->admin);

    // Do setup.
    $group1 = $this->createGroup([
      'path' => [
        'alias' => '/test-multiple-vsite-one',
      ],
    ]);
    $group2 = $this->createGroup([
      'path' => [
        'alias' => '/test-multiple-vsite-two',
      ],
    ]);
    $this->setSession($this->requestStack->getCurrentRequest());

    $this->handler->startPreviewMode('hwpi_themeone_bentley', $group1->id());

    // Make sure the preview is enabled in the vsite where it was activated.
    $this->visitGroupPage($group1, '/');

    $this->assertSession()->statusCodeEquals(200);
    $this->assertEquals('hwpi_themeone_bentley', $this->themeManager->getActiveTheme()->getName());

    // Make sure the preview is not enabled in the vsite where it was not
    // enabled.
    $this->visitGroupPage($group2, '/');

    $this->assertSession()->statusCodeEquals(200);
    $this->assertNotEquals('hwpi_themeone_bentley', $this->themeManager->getActiveTheme()->getName());
  }

}

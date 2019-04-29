<?php

namespace Drupal\Tests\os_theme_preview\ExistingSiteJavascript;

use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;
use Drupal\Tests\os_theme_preview\Traits\ThemePreviewTestTrait;

/**
 * Theme preview test.
 *
 * @group functional-javascript
 * @coversDefaultClass \Drupal\os_theme_preview\Theme\Negotiator
 */
class PreviewTest extends OsExistingSiteJavascriptTestBase {

  use ThemePreviewTestTrait;

  /**
   * Group administrator.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $groupAdmin;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->groupAdmin = $this->createUser();
  }

  /**
   * Asserts that preview enabled for one vsite does not appear in another.
   *
   * @covers ::applies
   * @covers ::determineActiveTheme
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testMultipleVsite(): void {
    $request_stack = $this->container->get('request_stack');
    $handler = $this->container->get('os_theme_preview.handler');
    $theme_manager = $this->container->get('theme.manager');

    $this->drupalLogin($this->groupAdmin);

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
    $this->addGroupAdmin($this->groupAdmin, $group1);
    $this->addGroupAdmin($this->groupAdmin, $group2);

    $this->setSessionFunctionalJavascript($request_stack->getCurrentRequest());

    $handler->startPreviewMode('hwpi_themeone_bentley', $group1->id());

    // Make sure the preview is enabled in the vsite where it was activated.
    $this->visitGroupPage($group1, '/');

    $this->assertSession()->statusCodeEquals(200);
    $this->assertEquals('hwpi_themeone_bentley', $theme_manager->getActiveTheme()->getName());

    // Make sure the preview is not enabled in the vsite where it was not
    // enabled.
    $this->visitGroupPage($group2, '/');

    $this->assertSession()->statusCodeEquals(200);
    $this->assertNotEquals('hwpi_themeone_bentley', $theme_manager->getActiveTheme()->getName());
  }

}

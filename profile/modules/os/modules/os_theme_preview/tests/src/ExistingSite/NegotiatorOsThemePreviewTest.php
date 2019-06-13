<?php

namespace Drupal\Tests\os_theme_preview\ExistingSite;

use Drupal\Core\Routing\RouteMatch;
use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;
use Drupal\Tests\os_theme_preview\Traits\ThemePreviewTestTrait;

/**
 * Theme negotiator test.
 *
 * @group kernel
 * @group os-theme-preview
 * @coversDefaultClass \Drupal\os_theme_preview\Theme\Negotiator
 */
class NegotiatorOsThemePreviewTest extends OsExistingSiteTestBase {

  use ThemePreviewTestTrait;

  /**
   * Theme preview negotiator.
   *
   * @var \Drupal\Core\Theme\ThemeNegotiatorInterface
   */
  protected $themeNegotiator;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->themeNegotiator = $this->container->get('os_theme_preview.theme_negotiator');
  }

  /**
   * Tests - without vsites.
   *
   * @covers ::applies
   * @covers ::determineActiveTheme
   */
  public function testNoVsite(): void {
    $request_stack = $this->container->get('request_stack');
    $handler = $this->container->get('os_theme_preview.handler');

    // Negative test.
    $route_match = RouteMatch::createFromRequest($request_stack->getCurrentRequest());

    $this->assertFalse($this->themeNegotiator->applies($route_match));
    $this->assertNull($this->themeNegotiator->determineActiveTheme($route_match));

    // Positive tests.
    $current_request = $this->setSessionKernel($request_stack->getCurrentRequest());
    $handler->startPreviewMode('hwpi_themeone_bentley', 0);
    $route_match = RouteMatch::createFromRequest($current_request);

    $this->assertTrue($this->themeNegotiator->applies($route_match));
    $this->assertSame('hwpi_themeone_bentley', $this->themeNegotiator->determineActiveTheme($route_match));
  }

  /**
   * Asserts that preview respects the base path where it was activated.
   *
   * @covers ::applies
   * @covers ::determineActiveTheme
   */
  public function testVsite(): void {
    $request_stack = $this->container->get('request_stack');
    $handler = $this->container->get('os_theme_preview.handler');
    $vsite_context_manager = $this->container->get('vsite.context_manager');

    $group = $this->createGroup([
      'path' => [
        'alias' => '/test-vsite',
      ],
    ]);

    $current_request = $this->setSessionKernel($request_stack->getCurrentRequest());
    $route_match = RouteMatch::createFromRequest($current_request);
    $handler->startPreviewMode('hwpi_themeone_bentley', $group->id());

    $this->assertFalse($this->themeNegotiator->applies($route_match));

    $vsite_context_manager->activateVsite($group);

    $this->assertTrue($this->themeNegotiator->applies($route_match));
    $this->assertSame('hwpi_themeone_bentley', $this->themeNegotiator->determineActiveTheme($route_match));
  }

}

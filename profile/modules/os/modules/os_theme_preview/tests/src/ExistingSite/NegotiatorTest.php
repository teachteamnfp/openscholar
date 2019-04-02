<?php

namespace Drupal\Tests\os_theme_preview\ExistingSite;

use Drupal\Core\Routing\RouteMatch;
use Drupal\os_theme_preview\Helper;

/**
 * Theme negotiator test.
 *
 * @group kernel
 * @group os-theme-preview
 * @coversDefaultClass \Drupal\os_theme_preview\Theme\Negotiator
 */
class NegotiatorTest extends TestBase {

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
   * Tests applies().
   *
   * @covers ::applies
   * @covers ::determineActiveTheme
   */
  public function test(): void {
    // Negative test.
    $route_match = RouteMatch::createFromRequest($this->requestStack->getCurrentRequest());

    $this->assertFalse($this->themeNegotiator->applies($route_match));
    $this->assertNull($this->themeNegotiator->determineActiveTheme($route_match));

    // Positive test.
    $current_request = $this->setSession($this->requestStack->getCurrentRequest());
    $this->requestStack->getCurrentRequest()->getSession()->set(Helper::SESSION_KEY, 'hwpi_themeone_bentley');
    $route_match = RouteMatch::createFromRequest($current_request);

    $this->assertTrue($this->themeNegotiator->applies($route_match));
    $this->assertSame('hwpi_themeone_bentley', $this->themeNegotiator->determineActiveTheme($route_match));
  }

}

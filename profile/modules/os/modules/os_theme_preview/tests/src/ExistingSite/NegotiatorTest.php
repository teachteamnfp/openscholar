<?php

namespace Drupal\Tests\os_theme_preview\ExistingSite;

use Drupal\Core\Routing\RouteMatch;

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
   * Tests - without vsites.
   *
   * @covers ::applies
   * @covers ::determineActiveTheme
   *
   * @throws \Drupal\os_theme_preview\ThemePreviewException
   */
  public function testNoVsite(): void {
    // Negative test.
    $route_match = RouteMatch::createFromRequest($this->requestStack->getCurrentRequest());

    $this->assertFalse($this->themeNegotiator->applies($route_match));

    // Positive tests.
    $current_request = $this->setSession($this->requestStack->getCurrentRequest());
    $this->helper->startPreviewMode('hwpi_themeone_bentley', 0);
    $route_match = RouteMatch::createFromRequest($current_request);

    $this->assertTrue($this->themeNegotiator->applies($route_match));
    $this->assertSame('hwpi_themeone_bentley', $this->themeNegotiator->determineActiveTheme($route_match));
  }

  /**
   * Asserts that preview respects the base path where it was activated.
   *
   * @covers ::applies
   * @covers ::determineActiveTheme
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\os_theme_preview\ThemePreviewException
   */
  public function testVsite(): void {
    $group = $this->createGroup([
      'path' => [
        'alias' => '/test-vsite',
      ],
    ]);

    $current_request = $this->setSession($this->requestStack->getCurrentRequest());
    $route_match = RouteMatch::createFromRequest($current_request);
    $this->helper->startPreviewMode('hwpi_themeone_bentley', $group->id());

    $this->assertFalse($this->themeNegotiator->applies($route_match));

    $this->vsiteContextManager->activateVsite($group);

    $this->assertTrue($this->themeNegotiator->applies($route_match));
    $this->assertSame('hwpi_themeone_bentley', $this->themeNegotiator->determineActiveTheme($route_match));
  }

}

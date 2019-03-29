<?php

namespace Drupal\Tests\os_theme_preview\ExampleWebDriverTest;

use Drupal\os_theme_preview\Helper;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use weitzman\DrupalTestTraits\ExistingSiteWebDriverTestBase;

/**
 * HelperTest.
 *
 * @group functional-javascript
 *
 * @coversDefaultClass \Drupal\os_theme_preview\Helper
 */
class HelperTest extends ExistingSiteWebDriverTestBase {

  /**
   * Helper service.
   *
   * @var \Drupal\os_theme_preview\HelperInterface
   */
  protected $helper;

  /**
   * Admin user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $admin;

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->helper = $this->container->get('os_theme_preview.helper');
    $this->admin = $this->createUser([], NULL, TRUE);
    $this->requestStack = $this->container->get('request_stack');
  }

  /**
   * Positive test startPreview.
   *
   * @covers ::startPreviewMode
   *
   * @throws \Drupal\os_theme_preview\ThemePreviewException
   */
  public function testStartPreview() {
    $this->drupalLogin($this->admin);

    $session = new Session(new MockFileSessionStorage());
    $request = $this->requestStack->getCurrentRequest();
    $request->setSession($session);

    $this->visit('/');

    $this->helper->startPreviewMode('hwpi_themeone_bentley');

    $previewed_theme = $this->requestStack->getCurrentRequest()->getSession()->get(Helper::SESSION_KEY);

    $this->assertEquals('hwpi_themeone_bentley', $previewed_theme);
  }

}

<?php

namespace Drupal\Tests\os_theme_preview\ExistingSite;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * TestBase.
 */
abstract class TestBase extends ExistingSiteBase {

  /**
   * Helper Service.
   *
   * @var \Drupal\os_theme_preview\HelperInterface
   */
  protected $helper;

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
    $this->requestStack = $this->container->get('request_stack');
  }

  /**
   * Sets a mock session to the request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\Request
   *   The updated request with session data.
   */
  protected function setSession(Request $request): Request {
    $session = new Session(new MockArraySessionStorage());
    $request->setSession($session);
    return $request;
  }

}

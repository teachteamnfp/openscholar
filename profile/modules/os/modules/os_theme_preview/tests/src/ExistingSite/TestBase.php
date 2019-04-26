<?php

namespace Drupal\Tests\os_theme_preview\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * TestBase.
 */
abstract class TestBase extends OsExistingSiteTestBase {

  /**
   * Theme preview handler.
   *
   * @var \Drupal\os_theme_preview\HandlerInterface
   */
  protected $handler;

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Vsite context manager service.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * Theme preview manager.
   *
   * @var \Drupal\os_theme_preview\PreviewManagerInterface
   */
  protected $themePreviewManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->handler = $this->container->get('os_theme_preview.handler');
    $this->requestStack = $this->container->get('request_stack');
    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->vsiteContextManager = $this->container->get('vsite.context_manager');
    $this->themePreviewManager = $this->container->get('os_theme_preview.manager');
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

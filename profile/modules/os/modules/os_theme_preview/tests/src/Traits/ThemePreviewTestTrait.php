<?php

namespace Drupal\Tests\os_theme_preview\Traits;

use Drupal\group\Entity\GroupInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

/**
 * Common helpers for os_theme_preview tests.
 */
trait ThemePreviewTestTrait {

  /**
   * Sets a mock session to the request for kernel tests.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\Request
   *   The updated request with session data.
   */
  protected function setSessionKernel(Request $request): Request {
    $session = new Session(new MockArraySessionStorage());
    $request->setSession($session);
    return $request;
  }

  /**
   * Sets a mock session to the request for functional javascript tests.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\Request
   *   The updated request with session data.
   */
  protected function setSessionFunctionalJavascript(Request $request): Request {
    $session = new Session(new MockFileSessionStorage());
    $request->setSession($session);

    return $request;
  }

  /**
   * Visit a group page.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   The group.
   * @param string $url
   *   The url to visit inside group.
   */
  protected function visitGroupPage(GroupInterface $group, $url): void {
    /** @var \Drupal\Core\Path\AliasManagerInterface $alias_manager */
    $alias_manager = $this->container->get('path.alias_manager');
    /** @var \Drupal\Core\Theme\ThemeManagerInterface $theme_manager */
    $theme_manager = $this->container->get('theme.manager');
    /** @var \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager */
    $vsite_context_manager = $this->container->get('vsite.context_manager');

    /** @var string $group_alias */
    $group_alias = $alias_manager->getAliasByPath("/group/{$group->id()}");

    // Unlike in actual browser requests, requests made via test does not
    // activates the group and does not considers theme negotiators.
    $theme_manager->resetActiveTheme();
    $vsite_context_manager->activateVsite($group);

    $this->visit($group_alias . $url);
  }

}

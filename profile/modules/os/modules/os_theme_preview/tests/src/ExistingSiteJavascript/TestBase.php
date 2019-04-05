<?php

namespace Drupal\Tests\os_theme_preview\ExistingSiteJavascript;

use Drupal\group\Entity\GroupInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use weitzman\DrupalTestTraits\ExistingSiteWebDriverTestBase;

/**
 * TestBase for functional javascript tests.
 */
abstract class TestBase extends ExistingSiteWebDriverTestBase {

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
   * Theme manager service.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * Alias manager service.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->helper = $this->container->get('os_theme_preview.helper');
    $this->requestStack = $this->container->get('request_stack');
    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->vsiteContextManager = $this->container->get('vsite.context_manager');
    $this->themeManager = $this->container->get('theme.manager');
    $this->aliasManager = $this->container->get('path.alias_manager');
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
    $session = new Session(new MockFileSessionStorage());
    $request->setSession($session);

    return $request;
  }

  /**
   * Creates a group.
   *
   * @param array $values
   *   (optional) The values used to create the entity.
   *
   * @return \Drupal\group\Entity\GroupInterface
   *   The created group entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function createGroup(array $values = []): GroupInterface {
    $group = $this->entityTypeManager->getStorage('group')->create($values + [
      'type' => 'personal',
      'label' => $this->randomMachineName(),
    ]);
    $group->enforceIsNew();
    $group->save();

    $this->markEntityForCleanup($group);

    return $group;
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
    /** @var string $group_alias */
    $group_alias = $this->aliasManager->getAliasByPath("/group/{$group->id()}");

    // Unlike in actual browser requests, requests made via test does not
    // activates the group and does not considers theme negotiators.
    $this->themeManager->resetActiveTheme();
    $this->vsiteContextManager->activateVsite($group);

    $this->visit($group_alias . $url);
  }

}

<?php

namespace Drupal\Tests\os_theme_preview\ExistingSite;

use Drupal\group\Entity\GroupInterface;
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
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->helper = $this->container->get('os_theme_preview.helper');
    $this->requestStack = $this->container->get('request_stack');
    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->vsiteContextManager = $this->container->get('vsite.context_manager');
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

}

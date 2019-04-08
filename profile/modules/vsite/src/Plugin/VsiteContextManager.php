<?php

namespace Drupal\vsite\Plugin;

use Drupal\Core\Database\Connection;
use Drupal\group\Entity\GroupInterface;
use Drupal\vsite\Event\VsiteActivatedEvent;
use Drupal\vsite\VsiteEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Manages and stores active vsites.
 *
 * Other classes declare a vsite is active to this manager, and this
 * class responds and dispatches an event for other modules to listen to.
 */
class VsiteContextManager implements VsiteContextManagerInterface {

  /**
   * The active vsite.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $activeGroup = NULL;

  /**
   * Event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $dbConnection;

  /**
   * Constructor.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   */
  public function __construct(EventDispatcherInterface $dispatcher, Connection $connection) {
    $this->dispatcher = $dispatcher;
    $this->dbConnection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public function activateVsite(GroupInterface $group) {
    if (!$group->id()) {
      return;
    }

    if (is_null($this->activeGroup) || $this->activeGroup->id() !== $group->id()) {
      $this->activeGroup = $group;
      $this->activateRoles();

      $event = new VsiteActivatedEvent($group);
      $this->dispatcher->dispatch(VsiteEvents::VSITE_ACTIVATED, $event);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function activateRoles() {
    // TODO: Implement activateRoles() method.
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveVsite() : ?GroupInterface {
    return $this->activeGroup;
  }

  /**
   * {@inheritdoc}
   */
  public function getActivePurl() {
    /** @var \Drupal\group\Entity\GroupInterface|null $group */
    $group = $this->getActiveVsite();

    if (!$group) {
      return '';
    }

    /** @var \Drupal\Core\Entity\Query\QueryInterface $query */
    $query = $this->dbConnection->select('url_alias', 'ua')
      ->fields('ua', ['alias'])
      ->condition('ua.source', "/group/{$group->id()}")
      ->range(0, 1);
    /** @var \Drupal\Core\Database\StatementInterface|null $result */
    $result = $query->execute();

    if (!$result) {
      return '';
    }

    $item = $result->fetchAssoc();

    return trim($item['alias'], '/');
  }

  /**
   * {@inheritdoc}
   */
  public function getAbsoluteUrl(string $path = '', GroupInterface $group = NULL) {
    if (!$this->activeGroup) {
      return $path;
    }

    $purl = $this->activeGroup->toUrl('canonical', ['base_url' => ''])->toString();
    return $purl . '/' . ltrim($path, '/');
  }

  /**
   * {@inheritdoc}
   */
  public function getStorage(GroupInterface $group = NULL) {}

}

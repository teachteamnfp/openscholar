<?php

namespace Drupal\os_publications;

/**
 * The base for ghost entities.
 *
 * The main purpose of ghost entities is to provide necessary data when a
 * citation is going to be deleted/concealed in background.
 * At that moment, the actual entity will not exist, and the system needs to
 * rely on some data for concealing the citation.
 * That is when, ghost entities will come into picture, and they will be
 * responsible to provide the data for concealing the citation.
 */
abstract class GhostEntity implements GhostEntityInterface {

  /**
   * Entity id.
   *
   * @var int
   */
  protected $id;

  /**
   * Entity type.
   *
   * @var string
   */
  protected $type;

  /**
   * Entity bundle.
   *
   * @var string
   */
  protected $bundle;

  /**
   * GhostEntity constructor.
   *
   * @param int $id
   *   The entity id.
   * @param string $type
   *   The entity type.
   * @param string $bundle
   *   The entity bundle.
   */
  public function __construct($id, $type, $bundle) {
    $this->id = (int) $id;
    $this->type = $type;
    $this->bundle = $bundle;
  }

  /**
   * {@inheritdoc}
   */
  public function id(): int {
    return (int) $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function type(): string {
    return $this->type;
  }

  /**
   * {@inheritdoc}
   */
  public function bundle(): string {
    return $this->bundle;
  }

}

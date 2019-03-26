<?php

namespace Drupal\os_publications;

/**
 * Contract for ghost entities.
 */
interface GhostEntityInterface {

  /**
   * Returns the id of the entity.
   *
   * @return int
   *   The entity id.
   */
  public function id(): int;

  /**
   * Returns the type of the entity.
   *
   * @return string
   *   The entity type.
   */
  public function type(): string;

  /**
   * Returns the bundle of the entity.
   *
   * @return string
   *   The entity bundle.
   */
  public function bundle(): string;

}

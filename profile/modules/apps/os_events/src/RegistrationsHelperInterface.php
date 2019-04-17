<?php

namespace Drupal\os_events;

/**
 * Interface RegistrationsHelperInterface.
 *
 * @package Drupal\os_events
 */
interface RegistrationsHelperInterface {

  /**
   * Checks Registration status.
   *
   * @param array $build
   *   Array of node build.
   *
   * @return array
   *   The data namely message and timestamp.
   */
  public function checkRegistrationStatus(array $build) : array;

  /**
   * Creates Another date link switcher.
   *
   * @param array $build
   *   Array of node build.
   *
   * @return array|bool
   *   The link and date switcher select list.
   *
   * @throws \Drupal\rng\Exception\InvalidEventException
   */
  public function anotherDateLink(array $build);

}

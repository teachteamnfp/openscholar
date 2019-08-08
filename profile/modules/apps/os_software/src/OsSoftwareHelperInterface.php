<?php

namespace Drupal\os_software;

use Drupal\node\NodeInterface;

/**
 * Helper functions interface.
 */
interface OsSoftwareHelperInterface {

  /**
   * Prepare release node title.
   *
   * @param \Drupal\node\NodeInterface $release_node
   *   Release node object.
   *
   * @return string
   *   New release title.
   */
  public function prepareReleaseTitle(NodeInterface $release_node) : string;

}

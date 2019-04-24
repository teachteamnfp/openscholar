<?php

namespace Drupal\vsite\Plugin\Purl\Method;

use Symfony\Component\HttpFoundation\Request;
use Drupal\group_purl\Plugin\Purl\Method\GroupPrefixMethod;

/**
 * Method for handling path prefixed vsites.
 *
 * @PurlMethod(
 *   id="vsite_prefix",
 *   title = @Translation("Path prefixed vsite content."),
 *   stage = {
 *      Drupal\purl\Plugin\Purl\Method\MethodInterface::STAGE_PROCESS_OUTBOUND,
 *      Drupal\purl\Plugin\Purl\Method\MethodInterface::STAGE_PRE_GENERATE
 *   }
 * )
 */
class VsitePrefixMethod extends GroupPrefixMethod {

  /**
   * Override to allow for showing front page instead of entity view.
   *
   * {@inheritdoc}.
   */
  public function contains(Request $request, $modifier) {
    $uri = $request->getPathInfo();

    // Always modify the 'entity.group.canonical' route so that the path will
    // be replaced and end as ''. So that <front> path is matched.
    if ($uri === '/' . $modifier) {
      return TRUE;
    }
    return $this->checkPath($modifier, $uri);
  }

}

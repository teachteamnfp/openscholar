<?php
/**
 * Created by PhpStorm.
 * User: New User
 * Date: 9/18/2018
 * Time: 11:43 AM
 */

namespace Drupal\vsite\Pathprocessor;


use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Symfony\Component\HttpFoundation\Request;

class VsiteOutboundPathProcessor implements OutboundPathProcessorInterface {

  protected $non_vsite_paths = [
    'admin',
    'admin/*',
    'user',
    'user/*'
  ];

  /**
   * @inheritDoc
   *
   * Disables purl handling from a whitelist of paths
   */
  public function processOutbound ($path, &$options = [], Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL) {
    foreach ($this->non_vsite_paths as $p) {
      $pattern = '|'.str_replace('*', '.+?', $p).'|';
      if (preg_match($pattern, $path)) {
        $options['purl_context'] = false;
      }
    }

    return $path;
  }
}
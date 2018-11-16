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
use Drupal\group\Entity\GroupInterface;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;

class VsiteOutboundPathProcessor implements OutboundPathProcessorInterface {

  protected $non_vsite_paths = [
    'admin',
    'admin/*',
    'user',
    'user/*'
  ];

  /** @var VsiteContextManagerInterface */
  protected $vsiteContextManager;

  public function __construct (VsiteContextManagerInterface $vsiteContextManager) {
    $this->vsiteContextManager = $vsiteContextManager;
  }

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

    if ($purl = $this->vsiteContextManager->getActivePurl ()) {
      if (strpos($path, $purl) !== FALSE) {
        $options['purl_exit'] = true;
      }
    }

    /** @var GroupInterface $group */
    if ($request && ($group = $request->get ('group')) && (!isset($options['purl_context']) || $options['purl_context'] !== FALSE) && (!isset($options['purl_exit']) || !$options['purl_exit'])) {
      $path = $this->vsiteContextManager->getAbsoluteUrl ($path);
    }

    return $path;
  }


}
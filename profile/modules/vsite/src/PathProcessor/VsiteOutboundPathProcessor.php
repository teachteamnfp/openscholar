<?php

namespace Drupal\vsite\Pathprocessor;

use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Modifications to urls based on our own requirements.
 */
class VsiteOutboundPathProcessor implements OutboundPathProcessorInterface {

  protected $nonVsitePaths = [
    'admin',
    'admin/*',
    'user',
    'user/*',
  ];

  /**
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * Constructor.
   */
  public function __construct(VsiteContextManagerInterface $vsiteContextManager) {
    $this->vsiteContextManager = $vsiteContextManager;
  }

  /**
   * @inheritdoc
   *
   * Disables purl handling from a whitelist of paths
   */
  public function processOutbound($path, &$options = [], Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL) {
    foreach ($this->nonVsitePaths as $p) {
      $pattern = '|' . str_replace('*', '.+?', $p) . '|';
      if (preg_match($pattern, $path)) {
        $options['purl_context'] = FALSE;
      }
    }

    if ($purl = $this->vsiteContextManager->getActivePurl()) {
      if (strpos($path, $purl) !== FALSE) {
        $options['purl_exit'] = TRUE;
      }
    }

    /** @var \Drupal\group\Entity\GroupInterface $group */
    if ($request && ($group = $request->get('group')) && (!isset($options['purl_context']) || $options['purl_context'] !== FALSE) && (!isset($options['purl_exit']) || !$options['purl_exit'])) {
      $path = $this->vsiteContextManager->getAbsoluteUrl($path);
    }

    return $path;
  }

}

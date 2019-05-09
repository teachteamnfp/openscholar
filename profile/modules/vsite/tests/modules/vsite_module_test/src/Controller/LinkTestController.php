<?php

namespace Drupal\vsite_module_test\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * A test controller to render various strings to a page so we can assert them.
 */
class LinkTestController extends ControllerBase {

  /**
   * Print testing strings to a page so internal state can be asserted.
   *
   * @return array
   *   Render array
   */
  public function linkTest() {

    /** @var \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsiteContextManager */
    $vsiteContextManager = \Drupal::service('vsite.context_manager');

    $metadata = new BubbleableMetadata();
    /** @var \Drupal\Core\PathProcessor\PathProcessorManager $pathProcessor */
    $pathProcessor = \Drupal::service('path_processor_manager');
    $options = [];
    $testStr = $pathProcessor->processOutbound('/link-test', $options, NULL, $metadata);

    /** @var \Drupal\purl\MatchedModifiers $matchedModifiers */
    $matchedModifiers = \Drupal::service('purl.matched_modifiers');

    /** @var \Drupal\purl\ContextHelper $contextHelper */
    $contextHelper = \Drupal::service('purl.context_helper');
    $contexts = $matchedModifiers->createContexts();
    /** @var \Drupal\purl\Context $context */
    $context = reset($contexts);
    $details = [
      'method_class' => get_class($context->getMethod()),
      'stages' => $context->getMethod()->getStages(),
      'action' => $context->getAction(),
      'modifier' => $context->getModifier(),
    ];
    $opt2 = [];
    $testStr2 = $contextHelper->processOutbound($contexts, '/link-test', $opt2);
    return [
      'vsite_active' => [
        '#type' => 'markup',
        '#markup' => $vsiteContextManager->getActiveVsite() ? 'vsite active' : 'vsite not active',
      ],
      'url' => [
        '#type' => 'markup',
        '#markup' => $testStr,
      ],
      'options' => [
        '#type' => 'markup',
        '#markup' => 'Options: ' . (isset($options['purl_context']) ? 'purl_context is set' : 'purl_context is not set') . ' ' . gettype($options['purl_context']),
      ],
      'contexts' => [
        '#type' => 'markup',
        '#markup' => print_r($details, 1),
      ],
      'helper' => [
        '#type' => 'markup',
        '#markup' => 'Just the context helper: ' . $testStr2,
      ],
    ];
  }

}
